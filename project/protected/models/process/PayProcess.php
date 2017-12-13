<?php

/**
 * PayProcess - 支付操作类
 * @author anqing
 * @version V1.0
 */


class PayProcess
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
	 * @param array[] $inputFields	输入的字段列表
	 * @param array[] $defFields 数据库字段列表
	 * @return array[] 过滤后的字段列表
	 */
	private function filterInputFields($inputFields,$defFields)
	{
		return array_intersect_key($inputFields,$defFields);
	}

	private static function createepiaoBankNo()
	{ //创建外部订单号0
		$charid = strtoupper(substr(md5(uniqid(mt_rand(), true)),8,24));
		$hyphen = '';   //chr(45);// "-"
		$uuid = substr($charid, 0, 8).$hyphen.substr($charid, 8, 4).$hyphen.substr($charid,12, 4).$hyphen.substr($charid,16, 4).$hyphen.substr($charid,20,11);
		return "TT-".date("md")."-".$uuid;
	}

	//微信支付
	public static function weixinPay($outerOrderId,$openid,$totalPrice,$orderType){
		switch($orderType){
			case ConfigParse::getOrdersKey('onlineSeatOrder'):
				$orderType = 'E票电影在线选座订单';
				$ret = OrderProcess::getOrderSeatInfoByOuterOrderId($outerOrderId);
				break;
			case ConfigParse::getOrdersKey('accountRechargeOrder'):
				$orderType = 'E票电影余额充值订单';
				$ret = OrderProcess::getOrderInfo($outerOrderId);
				break;
		}

		if (!$ret || !$openid)
		{
			return 0;
		}

		$orderInfo['orderPayType'] = ConfigParse::getPayTypeKey('weixinPay');
		$orderInfo['outerOrderId'] = $outerOrderId;

		OrderProcess::updateUserOrderInfo($orderInfo);

		$PayAccount = OrderProcess::getPayAccountInfo($outerOrderId);
		if($PayAccount){
			$bankNo = $PayAccount['sBankNo'];
		}else{
			$bankNo=  self::createepiaoBankNo();
			$payAccountInfo = array(
				'sBankNo'=>$bankNo,
				'outerOrderId'=>$outerOrderId,
				'iAlipayConsumeTypeID'=>ConfigParse::getOrdersKey('onlineSeatOrder'),
				'iOrderStatusID'=>ConfigParse::getPayStatusKey('orderNoPay'),
				'mConsumePrice'=>$totalPrice,
				'sBankType'=>ConfigParse::getPayTypeKey('weixinPay'),
				'sRetCode'=>'',
				'sTradeNo'=>'',
			);

			OrderProcess::insertPayAccountInfo($payAccountInfo);
		}

		$totalPrice = $totalPrice*100;
		$signA = 'appid='.ConfigParse::getWeixinKey("appId").'&attach=支付测试&body='.$orderType.'&mch_id='.ConfigParse::getWeixinKey("mch_id").'&nonce_str='.$bankNo.'&notify_url=https://api.epiaowang.com/index.php?r=wxAPP/Test&type=get_movielist&openid='.$openid.'&out_trade_no='.$outerOrderId.'&total_fee='.$totalPrice.'&trade_type=JSAPI';
		$signB = $signA.'&key='.ConfigParse::getWeixinKey("key");
		$sign =strtoupper(MD5($signB));
		$xmlData = '
 <xml>
        <appid>'.ConfigParse::getWeixinKey("appId").'</appid>
        <attach>支付测试</attach>
        <body>'.$orderType.'</body>
        <mch_id>'.ConfigParse::getWeixinKey("mch_id").'</mch_id>
        <nonce_str>'.$bankNo.'</nonce_str>
        <notify_url>https://api.epiaowang.com/index.php?r=wxAPP/Test&type=get_movielist</notify_url>
        <openid>'.$openid.'</openid>
        <out_trade_no>'.$outerOrderId.'</out_trade_no>
        <total_fee>'.$totalPrice.'</total_fee>
        <trade_type>JSAPI</trade_type>
        <sign>'.$sign.'</sign>
    </xml>';
//第一种发送方式，也是推荐的方式：
		$url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';  //接收xml数据的文件
		$header[] = "Content-type: text/xml";        //定义content-type为xml,注意是数组
		$ch = curl_init ($url);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlData);
		$response = curl_exec($ch);
		if(curl_errno($ch)){
			print curl_error($ch);
		}
		curl_close($ch);

		$xmlResult = simplexml_load_string($response);
		//foreach循环遍历
		foreach($xmlResult->children() as $childItem) {
			//输出xml节点名称和值
			if($childItem->getName() == 'prepay_id'){
				$prepay_id = $childItem;
			}
		}
		$package = ConfigParse::getWeixinKey('package').$prepay_id;
		$signA = "appId=".ConfigParse::getWeixinKey('appId')."&nonceStr=".$bankNo."&package=".$package."&signType=MD5&timeStamp=".time();
		$signB = $signA.'&key='.ConfigParse::getWeixinKey("key");
		$sign =strtoupper(MD5($signB));
		$requestPayment = array(
			'nonceStr' => $bankNo,
			'package'=> $package,
			'signType'=> 'MD5',
			'timeStamp'=>time(),
			'paySign'=>$sign
		);
		return $requestPayment;
	}
}