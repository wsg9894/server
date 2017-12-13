<?php

/**
 * G1758Controller - 1758接入控制器
 * @author luzhizhong
 * @version V1.0
 */
Yii::import('application.modules.partner.models.process.*');
class G1758Controller extends Controller
{
	
	private $_key = '27760f162c21c88e7502bd01295011aa';								//内部通用口令

	private $_1758key = '17051e62886339cee9486c25e1d9a059';

	private $_loginUrl = '/usercenter/login.html?go=/project/index.php?r=partner/G1758&type=%s&key=%s&para1758=%s';

	//游戏地址
	private $Url = '/project/index.php?r=partner/G1758&type=LoginAuth&key=%s&para1758=%s';

	private $_authUrl = 'http://wx.1758.com/play/openpf/third1758_epiao/oauth?userToken=%s&para1758=%s&sign=%s';	//1758授权回调地址

	/**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array(
		);
	}

	/**
	 * 1758-接口模块-入口
	 */
	public function actionIndex()
	{
		$type = empty($_REQUEST['type']) ? '' : $_REQUEST['type'];		//接口类型
		$key = empty($_REQUEST['key']) ? '' : $_REQUEST['key'];			//接口密钥

		//密钥验证
		if(FALSE==$this->checkKey($type, $key))
		{
			echo json_encode(array('code'=>1001));
			exit(0);
		}
		
//		echo 'hello';
//		exit(0);
		
		switch($type)
		{
			case 'LoginAuth':		//授权登录

				$para1758 = empty($_REQUEST['para1758']) ? '' : $_REQUEST['para1758'];
				$this->loginAuth($para1758,$key,$type);
				break;
			case 'UserCheck':		//用户验证
				
				$userToken = empty($_REQUEST['userToken']) ? '' : $_REQUEST['userToken'];
				$sign = empty($_REQUEST['sign']) ? '' : $_REQUEST['sign'];
				$this->userCheck($userToken, $sign);
				break;
			default:
				echo 'err';
				break;
		}
	}

	/**
	 * 授权登录
	 *
	 * @param string $para1758 用于1758授权请求，1758授权请求带有此参数，E票网授权回调后必须传递此参数
	 * @return void
	 */
	private function loginAuth($para1758,$key,$type)
	{
		$iUserID = UserProcess::isLogin();
		if($iUserID==0){
			$loginUrl = sprintf($this->_loginUrl,$type, $key,$para1758);
//			echo $loginUrl;die;
			GeneralFunc::gotoUrl($loginUrl);exit(0);
		}
//		$sessObj = new Session();
//		$sessObj->add('para1758', $para1758);
		//第一步、生成用户临时Token
		$userToken = self::createOauth();
		$filter = sprintf("iUserID=%d",$iUserID);
		$OauthInfo = UseroauthProcess::getUserOauth(array(),$filter);
		$editInfo = array(
			'iUserID'=>$iUserID,
			'oauth'=>$userToken,
			'dCreateDate'=>date('Y-m-d H:i:s'),
			'dEndDate'=>date('Y-m-d H:i:s',strtotime("+10 minute")),
		);
		if(!empty($OauthInfo)){
			UseroauthProcess::updateUserOauth($editInfo);
		}else{
			UseroauthProcess::addUserOauth($editInfo,$iUserID);
		}
		//第二步、生成签名（sign）
		$sign = self::createSign($userToken,$para1758);
//		echo $sign;die;
		GeneralFunc::writeLog('loginAuth/token:'.$userToken, Yii::app()->getRuntimePath().'/test/');
		GeneralFunc::writeLog('loginAuth/sign'.$sign, Yii::app()->getRuntimePath().'/test/');
		GeneralFunc::writeLog('loginAuth/para1758'.$para1758, Yii::app()->getRuntimePath().'/test/');
		//第三步、跳转至1758授权回调地址
		$authUrl = sprintf($this->_authUrl,$userToken, $para1758, $sign);
		GeneralFunc::gotoUrl($authUrl);
	}
	
	/**
	 * 用户验证
	 *
	 * @param string $userToken 用户token信息
	 * @param string $sign 签名
	 * @return array[][]
	 */
	private function userCheck($userToken, $sign)
	{
		$code = 0;
		//第一步、验证sign
		//密钥验证
		GeneralFunc::writeLog('userCheck/token:'.$userToken, Yii::app()->getRuntimePath().'/test/');
		GeneralFunc::writeLog('userCheck/sign'.$sign, Yii::app()->getRuntimePath().'/test/');
		GeneralFunc::writeLog('userCheck/para1758'.$_REQUEST['para1758'], Yii::app()->getRuntimePath().'/test/');
		if(FALSE==$this->checkSign($userToken, $sign,$_REQUEST['para1758']))
		{
			echo json_encode(array('code'=>2001));
			exit(0);
		}
		//第二步、验证用户Token
		$nowDate = GeneralFunc::getCurTime();
		$filter = sprintf("oauth='%s'",$userToken);
		$UserOauth = UseroauthProcess::getUserOauth(array(),$filter);
		GeneralFunc::writeLog('userCheck/UserOauth1:'.print_r($UserOauth,true), Yii::app()->getRuntimePath().'/test/');
		//token错误
		if(empty($UserOauth)){
			echo json_encode(array('code'=>2002));
			exit(0);
		}
		GeneralFunc::writeLog('userCheck/UserOauth2:'.print_r($UserOauth,true), Yii::app()->getRuntimePath().'/test/');
		//token失效
		if($nowDate>$UserOauth[0]['dEndDate']){
			echo json_encode(array('code'=>2003));
			exit(0);
		}
		GeneralFunc::writeLog('userCheck/UserOauth3:'.print_r($UserOauth,true), Yii::app()->getRuntimePath().'/test/');
		//让token失效
		$editInfo = array(
			'iUserID'=>$UserOauth[0]['iUserID'],
			'oauth'=>$userToken,
			'dEndDate'=>date('Y-m-d H:i:s'),
		);
		UseroauthProcess::updateUserOauth($editInfo);
		GeneralFunc::writeLog('userCheck/UserOauth4:'.print_r($UserOauth,true), Yii::app()->getRuntimePath().'/test/');
		//第三步、获取用户信息
		$fields = array(('sPhone'));
		$userInfo = UserProcess::getUInfo($UserOauth[0]['iUserID'],$fields);
		GeneralFunc::writeLog('userCheck/UserOauth5:'.print_r($userInfo,true), Yii::app()->getRuntimePath().'/test/');
		echo json_encode(array('code'=>$code, 'userinfo'=>$userInfo));
	}

	/**
	 * 密钥验证
	 * @param string $type 接口标识
	 * @param string $key 传入的密钥
	 * @return bool true－密钥正确；false－密钥错误
	 */
	private function checkKey($type, $key)
	{
		return $key == md5(implode('@', array($type, $this->_key, GeneralFunc::getCurDate())));
	}

	/**
	 * 生成秘钥
	 * @param $type
	 * @return string
	 */
	private function createKey(){
		return md5(implode('@', array('LoginAuth', $this->_key, GeneralFunc::getCurDate())));
	}
	/**
	 * 生成oauth
	 * @return string
	 */
	private function createOauth(){
		return strtolower(md5(uniqid(mt_rand(), true)));
	}

	private function createSign($userToken,$para1758=""){
		$arParam['userToken'] = $userToken;
		$arParam['para1758'] = $para1758;
		ksort($arParam);
		$strParam = '';
		foreach($arParam as $key=>$val){
			$strParam .= $key."=".$val."&";
		}
		return md5(substr($strParam,0,-1).$this->_1758key);
	}

	private function checkSign($userToken, $sign,$para1758){
		$arParam['userToken'] = $userToken;
		$arParam['para1758'] = $para1758;
		ksort($arParam);
		$strParam = '';
		foreach($arParam as $key=>$val){
			$strParam .= $key."=".$val."&";
		}
		$newSign = md5(substr($strParam,0,-1).$this->_1758key);
		return $sign == $newSign;
	}

	public function actionG1758(){
		$key = $this->createKey();
		$para1758 = empty($_REQUEST['para1758']) ? '' : $_REQUEST['para1758'];
		$Url = sprintf($this->Url,$key, $para1758);
		GeneralFunc::gotoUrl($Url);
	}

	
}