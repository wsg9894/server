<?php

/**
 * KouLInterface - 接口操作类（口粮网）
 * @author luzhizhong
 * @version V1.0
 */

Yii::import('application.modules.partner.models.process.*');

class KouLInterface
{
	function __construct()
	{
	}
	function __destruct()
	{
	}

	/**
	 * 有效字段过滤（过滤有效的字段列表）
	 *
	 * @param array[] $param 传入参数
	 * @return array[] 操作结果
	 */
	public function Binding($param)
	{
		if(empty($param['couponNo']) or empty($param['couponPW']) or empty($param['uid']))
		{
			//参数错误
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('param_error'), ErrorParse::getErrorDesc('param_error'));
		}
		
		$ret = CouponProcess::partnerBinding_KouL($param['couponNo'], $param['couponPW'], $param['uid'], $param['phone']);
		
		$retArr = array();
		switch($ret)
		{
			case '-801':		//卡不存在
				$retArr = GeneralFunc::returnErr(ErrorParse::getErrorNo('coupon_not_exist'), ErrorParse::getErrorDesc('coupon_not_exist'));
				break;
			case '-802':		//卡密码错误
				$retArr = GeneralFunc::returnErr(ErrorParse::getErrorNo('coupon_pw_invalid'), ErrorParse::getErrorDesc('coupon_pw_invalid'));
				break;
			case '-803':		//卡已经绑定
				$retArr = GeneralFunc::returnErr(ErrorParse::getErrorNo('coupon_is_binding'), ErrorParse::getErrorDesc('coupon_is_binding'));
				break;
			default:			//绑定成功
				$retArr = array('nErrCode'=>0);
				break;
		}
		
		return $retArr;
	}
}