<?php

/**
 * KouLController - 接口控制器（口粮网）
 * @author luzhizhong
 * @version V1.0
 */

Yii::import('application.modules.partner.models.interface.*');

class KouLController extends Controller
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
	 * 口粮网-接口模块-入口
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
			case 'binding':		//绑卡操作，成功可直接购票
				$ret = KouLInterface::Binding($param);
				break;
			default:
				$ret = GeneralFunc::returnErr(ErrorParse::getErrorNo('param_error'), ErrorParse::getErrorDesc('param_error'), 'json');
				break;
		}

		//返回结果
		if($type=='binding' && $ret['nErrCode']==0)
		{
			//@todo 此处需要进行登录操作
			//GeneralFunc::gotoUrl('/cinema/movieselect.php');
		}
		
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
		return $key == md5(implode('@', array($type, Yii::app()->params['interfPW_Partner']['KouL'], GeneralFunc::getCurDate())));
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