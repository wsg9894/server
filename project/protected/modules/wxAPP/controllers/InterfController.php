<?php

/**
 * InterfController - 接口控制器
 * @author luzhizhong
 * @version V1.0
 */

Yii::import('application.modules.wxAPP.models.process.*');

class InterfController extends Controller
{
	/**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array(
		);
	}

	/**
	 * 微信小程序-接口模块-入口
	 */
	public function actionIndex()
	{

		$type = empty($_REQUEST['type']) ? '' : $_REQUEST['type'];		//接口类型
		$key = empty($_REQUEST['key']) ? '' : $_REQUEST['key'];			//接口密钥
		//密钥验证
		if(FALSE==$this->checkKey($type, $key))
		{
			$ret = GeneralFunc::returnErr(ErrorParse::getErrorNo('verify_err'), ErrorParse::getErrorDesc('verify_err'), 'json');
			echo $this->createReturnJSON($ret);
			exit(0);
		}

		//接口业务逻辑处理
		$param = $_REQUEST['param'];	//传入的参数数组

		$param = json_decode($param, true);

		switch($type)
		{
			case 'get_splash':		//获取首页闪屏地址
				$ret = InterfProcess::getSplash($param);
				break;
			case 'get_movielist':		//获取影片列表
				$ret = InterfProcess::getMovieList($param);
				break;
			case 'get_movieinfo':		//获取影片详情
				$ret = InterfProcess::getMovieInfo($param);
				break;
			case 'get_cinemalist_by_movie':		//获取影片上映影院
				$ret = InterfProcess::getMovieCinemaList($param);
				break;
			case 'get_arrangelist':		//获取影片的排期列表
				$ret = InterfProcess::getMovieArrangeList($param);
				break;
			case 'get_seatlist':		//获取影厅座位图
				$ret = InterfProcess::getMovieSeatList($param);
				break;
			case 'sub_seatorder':		//提交选座订单
				$ret = InterfProcess::subSeatorder($param);
				break;
			case 'sub_noPayorder':	   //获取订单信息
				$ret = InterfProcess::getOrderInfo($param);
				break;
			case 'sub_noPayorderseat':	   //完成订单
				$ret = InterfProcess::subnoPayorder($param);
				break;
			case 'sub_success':	   //成功页面
				$ret = InterfProcess::subsuccessOrder($param);
				break;
			case 'check_login':	       //用户登录
				$ret = InterfProcess::getLoginInfo($param);
				break;
			case 'check_register':	       //用户注册
				$ret = InterfProcess::setRegister($param);
				break;
			case 'check_getVoucher':	       //新用户领取现金券
				$ret = InterfProcess::getVoucher($param);
				break;
			case 'get_citylist':
				$ret = InterfProcess::getCityInfo($param);
				break;
			case 'get_regionlist':
				$ret = InterfProcess::getRegionInfo($param);
				break;
			case 'get_cinemalist':
				$ret = InterfProcess::getCinemaInfo($param);
				break;
			case 'get_cinemaListBySearch':
				$ret = InterfProcess::getSearchCinemaInfo($param);
				break;
			case 'set_cinemaSearchLog':
				$ret = InterfProcess::setCinemaSearchLog($param);
				break;
			case 'get_cinemaSearchLog':
				$ret = InterfProcess::getCinemaSearchLog($param);
				break;
			case 'del_cinemaSearchLog':
				$ret = InterfProcess::delCinemaSearchLog($param);
				break;
			case 'sub_weixinsuccess':
				$ret = InterfProcess::weixinOk($param);
				break;
			case 'get_userInfo':
				$ret = InterfProcess::getUserInfo($param);
				break;
			case 'get_userOrderInfo':
				$ret = InterfProcess::getUserOrderInfo($param);
				break;
			case 'get_userCarInfo':
				$ret = InterfProcess::getUserCarInfo($param);
				break;
			case 'get_userCashInfo':
				$ret = InterfProcess::getUserCashInfo($param);
				break;
			case 'add_CarToUser':
				$ret = InterfProcess::addCarToUser($param);
				break;
			case 'add_CashToUser':
				$ret = InterfProcess::addCashToUser($param);
				break;
			case 'sub_weixinfail':
				$ret = InterfProcess::weixinFail($param);
				break;
			case 'mess_authentication':
				$ret = InterfProcess::sendRegisterMsg($param);
				break;
			case 'set_password':
				$ret = InterfProcess::setPassWord($param);
				break;
			case 'up_pwdbycode':
				$ret = InterfProcess::upPwdByCode($param);
				break;
			case 'set_recharge':
				$ret = InterfProcess::setRecharge($param);
				break;
			case 'sub_weixinRechargeOk':
				$ret = InterfProcess::weixinRechargeOk($param);
				break;
			case 'sub_weixinRechargeFail':
				$ret = InterfProcess::weixinRechargeFail($param);
				break;
			case 'get_rechargelog':
				$ret = InterfProcess::getRechargeLog($param);
				break;
			case 'get_huodong':
				$ret = InterfProcess::getHuodong($param);
				break;
			case 'sub_huodongById':
				$ret = InterfProcess::subHuodongById($param);
				break;
			case 'weixin_tuisong':
				$ret = InterfProcess::sendSeatOnlineMsg($param);
				break;
			default:
				$ret = GeneralFunc::returnErr(ErrorParse::getErrorNo('param_error'), ErrorParse::getErrorDesc('param_error'), 'json');
				break;
		}

		//返回结果
		echo $this->createReturnJSON($ret);
	}

	/**
	 * 密钥验证
	 * @param string $type 接口标识
	 * @param string $key 传入的密钥
	 * @return bool true－密钥正确；false－密钥错误
	 */
	private function checkKey($type, $key)
	{
		return $key == md5(implode('@', array($type, Yii::app()->params['interfPW_WXAPP'], GeneralFunc::getCurDate())));
	}

	/**
	 * 创建返回XML数据
	 * @param string $result 返回数据
	 * @return xml返回串
	 */
	private function createReturnXML($result)
	{
		$returnXML = '<?xml version="1.0" encoding="utf-8"?><Root>';
		$returnXML .= $result;
		$returnXML .= '</Root>';

		return $returnXML;
	}
	/**
	 * 创建返回JSON数据
	 * @param array[][] $result 返回数据数组
	 * @return json返回串
	 */
	private function createReturnJSON($result)
	{
		return json_encode($result);
	}

}