<?php

/**
 * SMSProcess - 短信操作类（大于短信）
 * @author lzz
 */

Yii::import('application.models.process.lib.dayu.TopSdk');
Yii::import('application.models.process.lib.dayu.top.*');
Yii::import('application.models.process.lib.dayu.top.request.*');

class SMSProcess 
{
	const APP_KEY = '23340555';
	const SECRET_KEY = '9d0ae4b6b93ac6af6935acfc1042dbdd';
	const SMS_EXTEND = '123456';
	const SMS_TYPE = 'normal';
	const SMS_SIGN = 'E票网';									//短信签名
		
	public function __construct()
	{
	}
	function __destruct()
	{
	}
	
	/**
	 * 下发大于短信
	 * @param string $phone 下发手机号
	 * @param array[][] $data 下发内容-参数配置
	 * @param string $template 大于模板ID
	 * @return int 1-成功；其他-错误码
	 */
	public static function sendDayuSMS($phone, $data, $template)
	{
		try
		{
			//短信下发
			$c = new TopClient;
			$c->appkey = self::APP_KEY;
			$c->secretKey = self::SECRET_KEY;
			
			$req = new AlibabaAliqinFcSmsNumSendRequest;
			$req->setExtend(self::SMS_EXTEND);
			$req->setSmsType(self::SMS_TYPE);
			$req->setSmsFreeSignName(self::SMS_SIGN);
			$msg = json_encode($data);
			$req->setSmsParam($msg);
			$req->setRecNum($phone);
			$req->setSmsTemplateCode($template);
	
			$retObj = $c->execute($req);
			$retCode = (isset($retObj->result->err_code) and 0==$retObj->result->err_code) ? 1 : $retObj->code;
			
			//下发日志记录
			$msgDBObj = new B_MsgDB();
				
			$msgDBObj->sPhone = $phone;
			$msgDBObj->sMsg = $template.$msg;
			$msgDBObj->iUserID = 0;
			$msgDBObj->sendresult = $retCode;
			$msgDBObj->save();
			
			return $retCode;
		}
		catch(Exception $e)
		{
			return ErrorParse::getErrorNo('unknown_err');
		}
	}
	
	/**
	 * 大于短信记录查询（只能查询最近30天内某一天的日志）
	 * @param string $phone 查询手机号
	 * @param string $date 查询日期（格式：YYYYMMDD）
	 * @param string $curPage 当前页（string格式）
	 * @param string $pageSize 每页数量 （string格式）
	 * @return array[][] 短信下发日志
	 * 
	 */
	public static function getDayuSMSLog($phone, $date, $curPage='1', $pageSize='10')
	{
		$c = new TopClient;
		$c->appkey = self::APP_KEY;
		$c->secretKey = self::SECRET_KEY;
		
		$req = new AlibabaAliqinFcSmsNumQueryRequest;
// 		$req->setBizId("1234^1234");
		$req->setRecNum($phone);
		$req->setQueryDate($date);
		$req->setCurrentPage($curPage);
		$req->setPageSize($pageSize);
		$resp = $c->execute($req);
		$resp = json_decode(json_encode($resp), TRUE);
		
		$ret = empty($resp['values']['fc_partner_sms_detail_dto']) ? array() : $resp['values']['fc_partner_sms_detail_dto'];
		return $ret;
	}

	/**
	 * 成功短信
	 * @param $sCousumeNo
	 * @return string
	 */
	public static function getSuccessMsg($sCousumeNo){
		$arOrderInfo = OrderProcess::getOrderSeatInfoByOuterOrderId($sCousumeNo);
		$sPhone=$arOrderInfo['sPhone']; //手机号
		$iInterfaceID=$arOrderInfo['iInterfaceID']; //接口ID

		$validCode=$arOrderInfo['sInterfaceValidCode'];// 接口订单验证码
		$msginfo_temp = "";
		If ($iInterfaceID==ConfigParse::getInterfaceKey("InterfaceType_Spider"))
		{
			$spiderCode = explode('*',$validCode);
			if (count($spiderCode) == 1)
			{
				$arrCode= explode('|',$validCode);
				if (count($arrCode) == 1)
				{
					$msginfo_temp="请在开场前凭验证码[".$validCode."]在影院内自助取票机或前台取票。客服电话：400-603-1331【E票网】";
				}
				else
				{
					$msginfo_temp="请在开场前取票序号[".$arrCode[0]."]和取票验证码[".$arrCode[1]."]在影院内自助取票机或前台取票。客服电话：400-603-1331【E票网】";
				}
			}
			else
			{
				$arrCode= explode('|',$spiderCode[0]);
				$errorCode=explode('|',$spiderCode[1]);
				if (count($arrCode) == 1)
				{
					if (count($errorCode)== 2)
					{
						$msginfo_temp="请在开场前凭验证码[".$arrCode[0]."]在影院内自助取票机取票， 如遇机器故障凭影院订单号[".$errorCode[0]."]和验证码[".$errorCode[1]."]至柜台取票。客服电话：400-603-1331【E票网】";
					}
					else
					{
						$msginfo_temp="请在开场前凭验证码[".$arrCode[0]."]在影院内自助取票机取票, 如遇机器故障凭影院验证码[".$errorCode[0]."]至柜台取票。客服电话：400-603-1331【E票网】";
					}
				}
				else
				{
					if (count($errorCode)== 1)
					{
						$msginfo_temp="请在开场前取票序号[".$arrCode[0]."]和取票验证码[".$arrCode[1]."]在影院内自助取票机取票, 如遇机器故障凭影院验证码[".$errorCode[0]."]至柜台取票。客服电话：400-603-1331【E票网】";
					}
					else
					{
						$msginfo_temp="请在开场前取票序号[".$arrCode[0]."]和取票验证码[".$arrCode[1]."]在影院内自助取票机取票, 如遇机器故障凭影院订单号[".$errorCode[0]."]和验证码[".$errorCode[1]."]至柜台取票。客服电话：400-603-1331【E票网】";
					}
				}
			}
		}

		If ($iInterfaceID==ConfigParse::getInterfaceKey("InterfaceType_Hipiao"))
		{
			$arrCode = explode('|',$validCode);
			if (count($arrCode) == 1)
			{
				$msginfo_temp="请在开场前凭验证码[".$arrCode[0]."]在影院内自助取票机或前台取票。客服电话：400-603-1331【E票网】";
			}
			else
			{
				$msginfo_temp="请在开场前取票序号[".$arrCode[0]."]和取票验证码[".$arrCode[1]."]在影院内自助取票机或前台取票。客服电话：400-603-1331【E票网】";
			}
		}

		If ($iInterfaceID==ConfigParse::getInterfaceKey("InterfaceType_Wangpiao"))
		{
			$arrCode = explode('|',$validCode);
			if (count($arrCode) == 1)
			{
				$msginfo_temp="请在开场前凭验证码[".$arrCode[0]."]在影院内自助取票机或前台取票。客服电话：400-603-1331【E票网】";
			}
			else
			{
				$msginfo_temp="请在开场前取票序号[".$arrCode[0]."]和取票验证码[".$arrCode[1]."]在影院内自助取票机或前台取票。客服电话：400-603-1331【E票网】";
			}
		}

		return $msginfo_temp;
	}
}