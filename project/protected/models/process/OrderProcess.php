<?php

/**
 * OrderProcess - 订单操作类
 * @author anqing
 * @version V1.0
 */


class OrderProcess
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

	//这个创建订单号不用验证数据库 全球唯一码本机绝对不会重复
	public static function createOuterOrderId()
	{ //创建外部订单号0
		$charid = strtoupper(substr(md5(uniqid(mt_rand(), true)),8,24));
		$hyphen = '';   //chr(45);
		$uuid = substr($charid, 0, 8).substr($charid, 8, 4).$hyphen.substr($charid,12, 4).$hyphen.substr($charid,16, 4).$hyphen.substr($charid,20,12);
		return "DD-".date("md")."-".$uuid;
	}

	/**
	 * 创建充值订单
	 *
	 * @param string $cinemas 影院id串（形如：id1,id2,id3）
	 * @return array[][] 影片列表，二维数组
	 */
	public static function createRechargeOrder($iUserID,$money,$sendPhone,$returnUrl,$type)
	{
		$outerOrderId=self::createOuterOrderId();
		$userOrderInfo=array('iUserID'=>$iUserID,
			'outerOrderId'=>$outerOrderId,
			'mPrice'=>$money,
			'totalPrice'=>$money,
			'orderInfo'=>$sendPhone.'充值'.$money.'元',
			'sendPhone'=>$sendPhone,
			'fromClient'=>'epw',
			'returnUrl'=>$returnUrl,
			'orderType'=>  $type,
			'orderStatus'=>ConfigParse::getPayStatusKey('orderNoPay'),
			'iownSeats'=> 1,
			'iHuoDongItemID'=>'',
		);
		if(self::insertUserOrderInfo($userOrderInfo)){
			return array("ok"=>true,"data"=> $userOrderInfo);
		}else{
			return array("ok"=>false,"data"=>'创建充值订单失败！');
		}
	}
	/**
	 * 创建在线选座订单
	 *
	 * @param string $cinemas 影院id串（形如：id1,id2,id3）
	 * @return array[][] 影片列表，二维数组
	 */
	public static function createSeatOnlinOrder($iUserID,$iRoommovieID,$sendPhone,$iSelectedCount,$seatInfo,$orderInfo,$fromClient,$returnUrl,$iHuoDongItemID,$type)
	{
		$outerOrderId=self::createOuterOrderId();
		$roomMovieInfo=CinemaProcess::getRoomMovieListByiRoommovieID($iRoommovieID);
		$roomMovieInfo['minPrice']=$roomMovieInfo['huodongPrice'];
		if($roomMovieInfo['iMovieID'] == 1913){
			$hour = date('H');
			$OrderSeatInfo = OrderProcess::getOrderSeatInfoByMovieId($roomMovieInfo['iMovieID']);
			if($OrderSeatInfo['OrderNum']){
				if($OrderSeatInfo['OrderNum'] < 50){
					if($iUserID!=0){
						$UserOrder = OrderProcess::getUserOrderByMovieID($iUserID,$roomMovieInfo['iMovieID']);
						if(!$UserOrder['UserNum']||$UserOrder['UserNum']<1){
							if (($hour>=0 && $hour<8) || $hour>=23)
							{
								$roomMovieInfo['minPrice'] = '19.9';
							}else{
								$roomMovieInfo['minPrice'] = '9.9';
							}
						}
					}else{
						if (($hour>=0 && $hour<8) || $hour>=23)
						{
							$roomMovieInfo['minPrice'] = '19.9';
						}else{
							$roomMovieInfo['minPrice'] = '9.9';
						}
					}
				}
				$roomMovieInfo['count'] = $OrderSeatInfo['OrderNum'];
			}else{
				if (($hour>=0 && $hour<8) || $hour>=23)
				{
					$roomMovieInfo['minPrice'] = '19.9';
				}else{
					$roomMovieInfo['minPrice'] = '9.9';
				}
			}
		}

		$userOrderInfo=array('iUserID'=>$iUserID,
			'outerOrderId'=>$outerOrderId,
			'mPrice'=>$roomMovieInfo["huodongPrice"],
			'totalPrice'=>$iSelectedCount*$roomMovieInfo["huodongPrice"],
			'orderInfo'=>$orderInfo,
			'sendPhone'=>$sendPhone,
			'fromClient'=>$fromClient,
			'returnUrl'=>$returnUrl,
			'orderType'=>  $type,
			'orderStatus'=>ConfigParse::getPayStatusKey('orderNoPay'),
			'iownSeats'=> count(explode('@@', $seatInfo)),
			'iHuoDongItemID'=>$iHuoDongItemID,
		);
		$userOrderSeatInfo=array(
			'iUserId'=>$iUserID,
			'outerOrderId'=>$outerOrderId,
			'sCinemaInterfaceNo'=>$roomMovieInfo["sCinemaInterfaceNo"],
			'sMovieInterfaceNo'=>$roomMovieInfo["sMovieInterfaceNo"],
			'sRoomInterfaceNo'=>$roomMovieInfo["sRoomInterfaceNo"],
			'sRoomMovieInterfaceNo'=>$roomMovieInfo["sRoomMovieInterfaceNo"],
			'sSeatInfo'=>$seatInfo,
			'sInterfaceOrderNo'=>'',
			'sInterfaceValidCode'=>'',
			'mFee'=>$roomMovieInfo["mFee"],
			'sCinemaName'=>$roomMovieInfo["sCinemaName"],
			'iCinemaId'=>$roomMovieInfo["iEpiaoCinemaID"],
			'iRoomID'=>$roomMovieInfo["iRoomID"],
			'sRoomName'=>$roomMovieInfo["sRoomName"],
			'sMovieName'=>$roomMovieInfo["sMovieName"],
			'iMovieID'=>$roomMovieInfo["iMovieID"],
			'iInterfaceID'=>$roomMovieInfo["iInterfaceID"],
			'sIMax'=>$roomMovieInfo["sIMax"],
			'sDimensional'=>$roomMovieInfo["sDimensional"],
			'sLanguage'=>$roomMovieInfo["sLanguage"],
			'iRoommovieID'=>$iRoommovieID,
			'sPhone'=>$sendPhone,
			'mPrice'=>$roomMovieInfo['minPrice'],
			'status'=>ConfigParse::getPayStatusKey('orderNoPay'),
			'dPlayTime'=>$roomMovieInfo['dBeginTime'],
			'mSettingPrice' =>$roomMovieInfo['mSettlementPrice'],
			'isWeChatapplet' =>1,

		);
		if(self::insertUserOrderInfo($userOrderInfo)&&self::insertUserOrderSeatInfo($userOrderSeatInfo)){
			return array("ok"=>true,"data"=> $userOrderInfo);
		}else{
			return array("ok"=>false,"data"=>'创建在线选座订单失败');
		}
	}

	public static function createSeatOrder($iUserID,$iRoommovieID,$sendPhone,$iSelectedCount,$seatInfo,$orderInfo,$fromClient,$returnUrl,$iHuoDongItemID,$type,$isWeChatapplet){
		//1.取消未支付的在线选座订单
		self::cancelSeatOnlineOrder($iUserID);
//		echo "ok";die;
		$outerOrderId=self::createOuterOrderId();
		$roomMovieInfo=CinemaProcess::getRoomMovieListByiRoommovieID($iRoommovieID);
		$userOrderInfo=array('iUserID'=>$iUserID,
			'outerOrderId'=>$outerOrderId,
			'mPrice'=>$roomMovieInfo["huodongPrice"],
			'totalPrice'=>$iSelectedCount*$roomMovieInfo["huodongPrice"],
			'orderInfo'=>$orderInfo,
			'sendPhone'=>$sendPhone,
			'fromClient'=>$fromClient,
			'returnUrl'=>$returnUrl,
			'orderType'=>  $type,
			'orderStatus'=>ConfigParse::getPayStatusKey('orderNoPay'),
			'iownSeats'=> count(explode('@@', $seatInfo)),
			'iHuoDongItemID'=>$iHuoDongItemID,
		);
		$userOrderSeatInfo=array(
			'iUserId'=>$iUserID,
			'outerOrderId'=>$outerOrderId,
			'sCinemaInterfaceNo'=>$roomMovieInfo["sCinemaInterfaceNo"],
			'sMovieInterfaceNo'=>$roomMovieInfo["sMovieInterfaceNo"],
			'sRoomInterfaceNo'=>$roomMovieInfo["sRoomInterfaceNo"],
			'sRoomMovieInterfaceNo'=>$roomMovieInfo["sRoomMovieInterfaceNo"],
			'sSeatInfo'=>$seatInfo,
			'sInterfaceOrderNo'=>'',
			'sInterfaceValidCode'=>'',
			'mFee'=>$roomMovieInfo["mFee"],
			'sCinemaName'=>$roomMovieInfo["sCinemaName"],
			'iCinemaId'=>$roomMovieInfo["iEpiaoCinemaID"],
			'iRoomID'=>$roomMovieInfo["iRoomID"],
			'sRoomName'=>$roomMovieInfo["sRoomName"],
			'sMovieName'=>$roomMovieInfo["sMovieName"],
			'iMovieID'=>$roomMovieInfo["iMovieID"],
			'iInterfaceID'=>$roomMovieInfo["iInterfaceID"],
			'sIMax'=>$roomMovieInfo["sIMax"],
			'sDimensional'=>$roomMovieInfo["sDimensional"],
			'sLanguage'=>$roomMovieInfo["sLanguage"],
			'iRoommovieID'=>$iRoommovieID,
			'sPhone'=>$sendPhone,
			'mPrice'=>$roomMovieInfo["huodongPrice"],
			'status'=>ConfigParse::getPayStatusKey('orderNoPay'),
			'dPlayTime'=>$roomMovieInfo['dBeginTime'],
			'mSettingPrice' =>$roomMovieInfo['mSettlementPrice'],
			'isWeChatapplet' =>$isWeChatapplet,
		);
		//插入数据库orders表
		try{
			self::insertUserOrderInfo($userOrderInfo);
			self::insertUserOrderSeatInfo($userOrderSeatInfo);
		}
		catch(Exception $e)
		{
			return array("ok"=>false,"data"=>$e);
		}
		return array("ok"=>true,"data"=> $userOrderInfo);
	}

	public static function cancelSeatOnlineOrder($iUserID){
		$arOrderList = OrderProcess::getUserOrderList($iUserID);
		foreach ($arOrderList as $v)
		{
			if($v['orderStatus']==10101 && $v['orderType']==100001){
				$outerOrderId = $v['outerOrderId'];
				if(empty($outerOrderId)){
					return array("ok"=>false,'err_code'=>ERROR_TYPE_ORDER_NOORDERERROR,'error'=>'订单不存在');
				}
				$orderInfo = self::getOrderSeatInfoByOuterOrderId($outerOrderId);
				if (empty($orderInfo))
				{
					return array("ok"=>false,'err_code'=>ERROR_TYPE_ORDER_NOORDERERROR,"error"=>"该用户下的订单不存在");
				}
				if ($orderInfo['iUserId'] != $iUserID)
				{
					return array("ok"=>false,'err_code'=>ERROR_TYPE_ORDER_NOORDERERROR,'error'=>'订单信息与当前用户不符');
				}
				if ($orderInfo['orderStatus'] != 10101)
				{
					return array("ok"=>false,'err_code'=>ERROR_TYPE_ORDER_NOORDERERROR,'error'=>'订单状态不对，无法取消');
				}
				$updateOrderInfo['orderStatus']=  10207;
				$updateOrderInfo['closeTime']=  date('Y-m-d H:i:s');
				$updateOrderInfo['orderInfo']=$orderInfo['orderInfo']."订单已经取消";
				$updateOrderInfo['outerOrderId']=$outerOrderId;
				self::updateUserOrderInfo($updateOrderInfo);
				$updateSeatOrder['iUserId']=$iUserID;
				$updateSeatOrder['outerOrderId']=$outerOrderId;
				$updateSeatOrder['status']=10207;
				self::updateOrderSeatByOrderInfo($iUserID,$updateSeatOrder);
				if(!empty($orderInfo['sInterfaceOrderNo'])){
					CinemaInterfaceProcess::GetCancelOrderResult($outerOrderId);
				}
				$userPayOrderInfo['status'] = 10207;
				$userPayOrderInfo['outerOrderId'] = $outerOrderId;
				self::updateUserPayOrderInfo($userPayOrderInfo);
				return array('ok'=>true);
			}
		}
	}

	/**
	 * 创建综合订单
	 *
	 * @param int $movieID 影片id
	 * @return array[] 影片信息
	 */
	public static function GetCreateOrderResult($movieID)
	{
		if(empty($movieID))
		{
			return array();
		}
		$sql = sprintf("SELECT sMovieName,sDirector,sActor,iMovieScore,iRunTime,sImageUrl,sDescription,iFavorMoiveID,sSmallImageUrl
				FROM {{b_movie}} 
				WHERE iMovieID=%d", $movieID);
		return DbUtil::queryRow($sql);
	}

	/*
	 * 更新在线选座订单
	 *
	 * */
	static function updateOrderSeatByOrderInfo($iUserID, $orderSeatInfo)
	{
		if($iUserID!=$orderSeatInfo[ConfigParse::getOrderSeatInfoKey('userId')])
		{
			return array("ok"=>false,'err_code'=>201);
		}
		$subSql = array();
		foreach($orderSeatInfo as $key =>$v)
		{
			if(!empty($v)){
				$subSql[] = "$key='".mysql_escape_string($v)."'";
			}
		}
		$SQL = sprintf("update {{l_order_seat}} set %s  where outerOrderId='%s'",implode(',', $subSql),$orderSeatInfo["outerOrderId"]);
		return array("ok"=>true,'err_code'=>DbUtil::execute($SQL));
	}

	//插入用户订单 传入参数 订单数组
	public static function insertUserOrderInfo($userOrderInfo)
	{

		if(empty($userOrderInfo))
		{
			return array();
		}
		$subSql = array();
		$csubSql=array();
		foreach($userOrderInfo as $key =>$v)
		{
			$subSql[] = "'".mysql_escape_string($v)."'";
			$csubSql[]=$key;
		}
		$SQL = sprintf("insert into  {{l_orders}} (%s) VALUES(%s)",implode(',', $csubSql),implode(',', $subSql));
		return DbUtil::execute($SQL);
	}

	//记录第三方支付订单
	public static function insertPayAccountInfo($payAccountInfo)
	{

		if(empty($payAccountInfo))
		{
			return array();
		}
		$subSql = array();
		$csubSql=array();
		foreach($payAccountInfo as $key =>$v)
		{
			$subSql[] = "'".mysql_escape_string($v)."'";
			$csubSql[]=$key;
		}
		$SQL = sprintf("insert into  {{l_alipayaccount}} (%s) VALUES(%s)",implode(',', $csubSql),implode(',', $subSql));
		return DbUtil::execute($SQL);
	}

	//插入用户订单 传入参数 订单数组
	public static function insertUserOrderSeatInfo($OrderSeatInfo)
	{
		if(empty($OrderSeatInfo))
		{
			return array();
		}
		$subSql = array();
		$csubSql=array();
		foreach($OrderSeatInfo as $key =>$v)
		{
			$subSql[] = "'".mysql_escape_string($v)."'";
			$csubSql[]=$key;
		}
		$SQL = sprintf("insert into  {{l_order_seat}} (%s) VALUES(%s)",implode(',', $csubSql),implode(',', $subSql));
		return DbUtil::execute($SQL);
	}

	//根据订单号获取到订单信息
	public static function getOrderSeatInfoByOuterOrderId($outerOrderId)
	{
		$arSeatInfo = self::getOrderSeatInfo($outerOrderId);
		$arOrderInfo = self::getOrderInfo($outerOrderId);
		return array_merge($arSeatInfo,$arOrderInfo);
	}

	//根据影片id获取到订单信息
	public static function getOrderSeatInfoByMovieId($iMovieID)
	{
		$SQL = sprintf("select sum(iownSeats) as OrderNum from {{l_order_seat}} a,{{l_orders}} b where iMovieID='%s'and (status >= 10101 and status < 10206) and sInterfaceValidCode != '1|2*3' and a.outerOrderId=b.outerOrderId",$iMovieID);
		return DbUtil::queryRow($SQL);
	}

	public static function getOrderSeatInfo($outerOrderId)
	{
		$SQL = sprintf("select * from {{l_order_seat}} where outerOrderId='%s'",$outerOrderId);
		return DbUtil::queryRow($SQL);
	}

	public static function getPayAccountInfo($outerOrderId)
	{
		$SQL = sprintf("select * from {{l_alipayaccount}} where outerOrderId='%s'",$outerOrderId);
		return DbUtil::queryRow($SQL);
	}

	//传过来键值对 解析返回数据
	public static function getOrderInfo($outerOrderId)
	{
		$SQL = sprintf("select * from {{l_orders}} where outerOrderId='%s' ",$outerOrderId);
		return DbUtil::queryRow($SQL);
	}

	public static function getOrderByiRoommovieID($iRoomMovieID,$seat_no)
	{
		$seat_no = '%'.$seat_no.'%';
		$SQL = sprintf("select * from {{l_order_seat}} a,{{l_orders}} b where iRoomMovieID='%s' and sSeatInfo like '%s' and a.outerOrderId = b.outerOrderId and b.orderStatus = 10101",$iRoomMovieID,$seat_no);
		return DbUtil::queryRow($SQL);
	}

	public static function addCardPayForSeatBycardPay($outerOrderId, $sCheckNo,$iUserID, $count, $cardCount,$couponId,$payCard,$payType,$openid='',$totalPrice='',$sVoucherPassWord = '')
	{
		$payloginfo = OrderProcess::getOrderPaylogForOuterOrderId($outerOrderId);
		$flag = 0;
		switch($payType){
			case 0:
				$type = ConfigParse::getPayTypeKey('cardPay');
				break;
			case 1:
				$type = ConfigParse::getPayTypeKey('accountPay');
				break;
			case 2:
				$type = ConfigParse::getPayTypeKey('cashPay');
				break;
			case 3:
				$type = ConfigParse::getPayTypeKey('weixinPay');
				break;
		}
		if($payloginfo) {
			foreach ($payloginfo as $k => $v) {
				if ($v['bankType'] == $type) {
					$flag = 1;
				}
			}
		}

		$arPaylog = array(
			'outerOrderId' => $outerOrderId,
			'createTime' => "",
			'totalPrice' => $totalPrice,
			'bankType' => $type,
			'payTime' => date('Y-m-d h:i:s'),
			'tradeId' => "",
			'sCheckNo' => $sCheckNo,
			'sPassword' => "",
			'iCouponID' => $couponId,
			'ticketCode' => "",
			'count' => $count,
			'tradeNo' => "",
			'status' => ConfigParse::getPayStatusKey('orderPay'),
			'iUserID' => $iUserID,
			'iHuodongItemID' => "",
			'sVoucherPassWord' => $sVoucherPassWord,
			'cardCount' => $cardCount,
		);
		if($flag == 0){
			if(self::addCardPayForSeat($arPaylog)){
				switch($payType){
					case 0:
						return CouponProcess::updateCouponiUsedFlag($sCheckNo, $payCard);      //$payCard  电影卡实际支付的点数
						break;
					case 1:
						return UserProcess::updateAccountPay($iUserID,$totalPrice);
						break;
					case 2:
						return VoucherProcess::updateVoucheriUsedFlag($sVoucherPassWord);
						break;
					case 3:
						return PayProcess::weixinPay($outerOrderId,$openid,$totalPrice,ConfigParse::getOrdersKey('onlineSeatOrder'));
						break;
				}

			}else{
				return 0;
			}
		}else{
			if($type == ConfigParse::getPayTypeKey('weixinPay')){
				return PayProcess::weixinPay($outerOrderId,$openid,$totalPrice,ConfigParse::getOrdersKey('onlineSeatOrder'));
			}
		}
		return 1;
	}

	public static function addCardPayForSeat($arPaylog)
	{
		if(empty($arPaylog)){
			return 0;
		}
		$subSql = array();
		$csubSql=array();
		foreach($arPaylog as $key =>$v)
		{
			$subSql[] = "'".mysql_escape_string($v)."'";
			$csubSql[]=$key;
		}
		$SQL = sprintf("insert into  {{l_paylog}} (%s) VALUES(%s)",implode(',', $csubSql),implode(',', $subSql));
		return DbUtil::execute($SQL);
	}

	public static function getOrderPaylogForOuterOrderId($outerOrderId)
	{
		if(empty($outerOrderId)){
			return 0;
		}
		$SQL = sprintf("SELECT * FROM {{l_paylog}} WHERE outerOrderId=%s", "'$outerOrderId'");
		return DbUtil::queryAll($SQL);
	}

	//根据userID获取订单号
	public static function getUserIDouterOrderId($iUserID)
	{
		if(empty($iUserID)){
			return 0;
		}
		$SQL = sprintf("SELECT outerOrderId FROM {{l_order_seat}} WHERE iUserId=%s and (status >= 10101 and status < 10206) order by dCreateTime DESC ", "'$iUserID'");
		return DbUtil::queryAll($SQL);
	}

	//电影卡支付订单
	public static function confirmSeatOnlineOrderCardPay($iUserID,$outerOrderId,$sendPhone,$formId){
		$orderInfo = self::getOrderSeatInfoByOuterOrderId($outerOrderId);
		if(empty($orderInfo))
		{
			return 0;
		}

		//总金额,座位数
		$totalPrice=$orderInfo['totalPrice'];
		$orderPayList=  self::getOrderPaylogForOuterOrderId($outerOrderId);
		//已支付金额
		$payPrice=0;
		if($orderPayList){
			foreach ($orderPayList as $key=>$v )
			{
				$type = ConfigParse::getPayTypeKey('cardPay');
				switch ($v['bankType'])
				{
					case ConfigParse::getPayTypeKey('cardPay'):
					{
						if($v['status']==ConfigParse::getPayStatusKey('orderPay'))
						{
							$type = ConfigParse::getPayTypeKey('cardPay');
							$payPrice +=$v['totalPrice'];
						}
						break;
					}
					case ConfigParse::getPayTypeKey('accountPay'):
					{
						if($v['status']==ConfigParse::getPayStatusKey('orderPay'))
						{
							$type = ConfigParse::getPayTypeKey('accountPay');
							$payPrice +=$v['totalPrice'];
						}
						break;
					}
					case ConfigParse::getPayTypeKey('cashPay'):
					{
						if($v['status']==ConfigParse::getPayStatusKey('orderPay'))
						{
							$type = ConfigParse::getPayTypeKey('cashPay');
							$payPrice +=$v['totalPrice'];
						}
						break;
					}
					case ConfigParse::getPayTypeKey('weixinPay'):
					{
						if($v['status']==ConfigParse::getPayStatusKey('orderPay'))
						{
							$type = ConfigParse::getPayTypeKey('weixinPay');
							$payPrice +=$v['totalPrice'];
						}
						break;
					}
				}
			}
		}

		$accountPrice = $totalPrice-$payPrice;
		if($accountPrice == $totalPrice)
		{
			return array("ok"=>false,'msg'=>'请选择支付方式或验证电影卡');
		}
		if ($accountPrice>0)
		{
			return array("ok"=>false,"msg"=>"需支付剩余金额，请选择支付方式");
		}
		$orders['orderPayType'] = $type;
		$orders['sendPhone'] = $sendPhone;
		$orders['outerOrderId'] = $outerOrderId;
		$orders['orderStatus'] = ConfigParse::getPayStatusKey('orderPay');
		self::updateUserOrderInfo($orders);
		return array("ok"=>true,'outerOrderId'=>self::confirmSeatOnlineOrder($iUserID, $outerOrderId,$sendPhone,$formId));
	}

	public static function updateUserOrderInfo($userOrderInfo)
	{
		$subSql = array();
		foreach($userOrderInfo as $key =>$v)
		{
			if(!empty($v)){
				$subSql[] = "$key='".mysql_escape_string($v)."'";
			}
		}
		$SQL = sprintf("Update {{l_orders}} set %s  where outerOrderId='%s'",implode(',', $subSql),$userOrderInfo["outerOrderId"]);
		return DbUtil::execute($SQL);
	}

	public static function updateUserPayOrderInfo($userPayOrderInfo)
	{
		$subSql = array();
		foreach($userPayOrderInfo as $key =>$v)
		{
			if(!empty($v)){
				$subSql[] = "$key='".mysql_escape_string($v)."'";
			}
		}
		$SQL = sprintf("update {{l_paylog}} set %s where outerOrderId='%s'",implode(',', $subSql),$userPayOrderInfo["outerOrderId"]);
		return DbUtil::execute($SQL);
	}

	//余额不足删除paylog
	public static function delUserPayOrderInfo($outerOrderId)
	{
		if(isset($outerOrderId) && !empty($outerOrderId)){
			$SQL = sprintf("delete from {{l_paylog}} where outerOrderId='%s'",$outerOrderId);
			return DbUtil::execute($SQL);
		}
		return 0;
	}

	//锁坐失败删除orders
	public static function delUserOrdersInfo($outerOrderId)
	{
		if(isset($outerOrderId) && !empty($outerOrderId)) {
			$SQL = sprintf("delete from {{l_orders}} where outerOrderId='%s'", $outerOrderId);
			return DbUtil::execute($SQL);
		}
		return 0;
	}

	//锁坐失败删除orderseat
	public static function delUserOrderSeatInfo($outerOrderId)
	{
		if(isset($outerOrderId) && !empty($outerOrderId)) {
			$SQL = sprintf("delete from {{l_order_seat}} where outerOrderId='%s'", $outerOrderId);
			return DbUtil::execute($SQL);
		}
		return 0;
	}

	public static function confirmSeatOnlineOrder($iUserId,$outerOrderId,$sendPhone,$formId = "")
	{
		//费用已经扣完
		$ret = CinemaInterfaceProcess::GetConfirmOrderResult($outerOrderId);

		//判断是格瓦拉还是网票网（猫眼和下面逻辑一样）
		if($ret['ResultCode'] == ConfigParse::getOrderStatusKey('ORDER_STATUS_Successed')){
			//网票网同步获取取票码
			if(isset($ret['InterfaceValidCode']) && !empty($ret['InterfaceValidCode'])){
                //完成订单
				$orderInfo['orderStatus'] = ConfigParse::getPayStatusKey('orderSucess');
				$orderInfo['outerOrderId'] = $outerOrderId;
				self::updateUserOrderInfo( $orderInfo);

				$userPayLog=  array("iUserID"=>$iUserId,"outerOrderId"=>$outerOrderId,  "ticketCode"=>$ret['InterfaceValidCode'],"status"=>ConfigParse::getPayStatusKey('orderSucess'));
				self::updateUserPayOrderInfo($userPayLog);

				$orderSeatUpdate[ConfigParse::getOrderSeatInfoKey('sInterfaceValidCode')] = $ret['InterfaceValidCode'];
				$orderSeatUpdate[ConfigParse::getOrderSeatInfoKey('outerOrderId')] = $outerOrderId;
				$orderSeatUpdate[ConfigParse::getOrderSeatInfoKey('status')]=ConfigParse::getPayStatusKey('orderSucess');
				$orderSeatUpdate[ConfigParse::getOrderSeatInfoKey('userId')] = $iUserId;
				$orderSeatUpdate[ConfigParse::getOrderSeatInfoKey('lowValue')] = 0;
				$orderSeatUpdate['sPhone'] = $sendPhone;
				self::updateOrderSeatByOrderInfo($iUserId, $orderSeatUpdate);
				self::sendSeatOnlineMsg($outerOrderId,$formId);
			}elseif(isset($ret['InterfaceValidCode']) && empty($ret['InterfaceValidCode'])){
				//网票网异步获取取票码
				//开始异步订单
				$orderInfo['orderStatus'] = ConfigParse::getPayStatusKey('orderAsynSeatBegin');
				$orderInfo['outerOrderId'] = $outerOrderId;
				self::updateUserOrderInfo( $orderInfo);
				$orderSeatUpdate[ConfigParse::getOrderSeatInfoKey('outerOrderId')] = $outerOrderId;
				$orderSeatUpdate[ConfigParse::getOrderSeatInfoKey('userId')] = $iUserId;
				$orderSeatUpdate['sPhone'] = $sendPhone;
				$orderSeatUpdate['form_id'] = $formId;
				self::updateOrderSeatByOrderInfo($iUserId, $orderSeatUpdate);
			}else{
				//格瓦拉同步获取取票码
				$arResult = CinemaInterfaceProcess::GetOrderInfoResult($outerOrderId);
				if($arResult['OrderStatus'] == ConfigParse::getOrderStatusKey('ORDER_STATUS_Successed')&&!empty($arResult['FetchNo'])){
					//完成订单
					$orderInfo['orderStatus'] = ConfigParse::getPayStatusKey('orderSucess');
					$orderInfo['outerOrderId'] = $outerOrderId;
					self::updateUserOrderInfo( $orderInfo);

					$userPayLog=  array("iUserID"=>$iUserId,"outerOrderId"=>$outerOrderId,  "ticketCode"=>$arResult['FetchNo'],"status"=>ConfigParse::getPayStatusKey('orderSucess'));
					self::updateUserPayOrderInfo($userPayLog);

					$orderSeatUpdate[ConfigParse::getOrderSeatInfoKey('sInterfaceValidCode')] = $arResult['FetchNo'];
					$orderSeatUpdate[ConfigParse::getOrderSeatInfoKey('outerOrderId')] = $outerOrderId;
					$orderSeatUpdate[ConfigParse::getOrderSeatInfoKey('status')]=ConfigParse::getPayStatusKey('orderSucess');
					$orderSeatUpdate[ConfigParse::getOrderSeatInfoKey('userId')] = $iUserId;
					$orderSeatUpdate[ConfigParse::getOrderSeatInfoKey('lowValue')] = 0;
					$orderSeatUpdate['sPhone'] = $sendPhone;
					self::updateOrderSeatByOrderInfo($iUserId, $orderSeatUpdate);
					self::sendSeatOnlineMsg($outerOrderId,$formId);
				}else{
					//异步开始
					$orderInfo['orderStatus'] = ConfigParse::getPayStatusKey('orderAsynSeatBegin');
					$orderInfo['outerOrderId'] = $outerOrderId;
					self::updateUserOrderInfo( $orderInfo);
					$orderSeatUpdate[ConfigParse::getOrderSeatInfoKey('outerOrderId')] = $outerOrderId;
					$orderSeatUpdate[ConfigParse::getOrderSeatInfoKey('userId')] = $iUserId;
					$orderSeatUpdate['sPhone'] = $sendPhone;
					$orderSeatUpdate['form_id'] = $formId;
					self::updateOrderSeatByOrderInfo($iUserId, $orderSeatUpdate);
				}
			}
		}else{
			if($ret['ResultCode'] == ConfigParse::getOrderStatusKey('PROCESSED_STATUS_NotProcessed')){
				//格瓦拉
				$orderInfo['orderStatus'] = ConfigParse::getPayStatusKey('orderPayNoticeFail');
			}else{
				$orderInfo['orderStatus'] = ConfigParse::getPayStatusKey('orderAsynSeatBegin');
			}
			$orderInfo['outerOrderId'] = $outerOrderId;
			self::updateUserOrderInfo( $orderInfo);
			$orderSeatUpdate[ConfigParse::getOrderSeatInfoKey('outerOrderId')] = $outerOrderId;
			$orderSeatUpdate[ConfigParse::getOrderSeatInfoKey('userId')] = $iUserId;
			$orderSeatUpdate['sPhone'] = $sendPhone;
			$orderSeatUpdate['form_id'] = $formId;
			self::updateOrderSeatByOrderInfo($iUserId, $orderSeatUpdate);
		}
		return $outerOrderId;

	}

	//根据userID获取订单号
	public static function getUserIunfinishOrder($iUserID)
	{
		if(empty($iUserID)){
			return 0;
		}
		$SQL = sprintf("SELECT * FROM {{l_order_seat}} WHERE iUserId=%s and (status = 10101 or status = 10102) order by dCreateTime DESC ", "'$iUserID'");
		return DbUtil::queryAll($SQL);
	}

	/**
	 * 获取用户订单
	 * @param $iUserID
	 * @return array
	 */
	public static function getUserOrderList($iUserID){
		$SQL = sprintf("SELECT * FROM {{l_orders}} WHERE iUserId=%s and orderStatus<10206 order by orderId DESC ", $iUserID);
		return DbUtil::queryAll($SQL);
	}

	//修改未支付订单状态
	public static function upUserIunfinishOrder($orderId)
	{
		if(empty($orderId)){
			return 0;
		}

		// $updateOrderInfo[Orders::closeTime]=  date('Y-m-d H:i:s') $updateOrderInfo[Orders::orderInfo]=$orderInfo[Orders::orderInfo]."订单已经取消";
		$SQL = sprintf("update {{l_order_seat}} set status = ".ConfigParse::getPayStatusKey('orderClose')."  where outerOrderId='%s'",$orderId);
		if(DbUtil::execute($SQL)){
			$SQL = sprintf("update {{l_orders}} set orderStatus = ".ConfigParse::getPayStatusKey('orderClose')." , closeTime = '%s', orderInfo = '订单已经取消' where outerOrderId='%s'",date('Y-m-d H:i:s'),$orderId);
			return DbUtil::execute($SQL);
		}
		return 0;
	}

	//购票成功下发短信
	public static function sendSeatOnlineMsg($outerOrderId,$formId = "")
	{

		$OrderInfo = self::getOrderSeatInfoByOuterOrderId($outerOrderId);
		if($OrderInfo['iInterfaceID'] == ConfigParse::getInterfaceKey('InterfaceType_Wangpiao')){
			$arResult = CinemaInterfaceProcess::GetOrderInfoResult($outerOrderId);
			if($arResult['OrderStatus'] == ConfigParse::getOrderStatusKey('ORDER_STATUS_Failed')){
				$arResult['data'] = -1;
			}
		}else{
			$arResult['data'] = -1;
		}
		$Stype = array(0,1,13);
		if(!empty($OrderInfo['sInterfaceValidCode']))
		{
			$arrCode0 = '';
			$arrCode1 = '';
			$errorCode0 = '';
			$errorCode1 = '';
			$reg = '/^[a-zA-Z0-9\|\*]*$/';                    // 正则句
			$proArr = str_split( $OrderInfo['sInterfaceValidCode'] );           // 将字符串切割成数组
			$proLen = count( $proArr );               // 计算该数组的长度
			$ValidCodeStr = '';

// 通过循环去核对数组中每个数据是否满足正则句
			for( $iCount = 0; $iCount < $proLen; $iCount++ )
			{
				if( preg_match( $reg, $proArr[$iCount] ) )    // 满足正则句表示该数据是字符
				{
					$ValidCodeStr .= $proArr[$iCount];                    // 字符存放的数组
				}
			}
			$ValidCodeStr = ltrim($ValidCodeStr,'|');
			$sInterfaceValidCode = explode('*',$ValidCodeStr);

			if(isset($sInterfaceValidCode[1]) && !empty($sInterfaceValidCode[1]))
			{
				if(count(explode('|',$sInterfaceValidCode[1])) == 2){
					$errorCode0 = explode('|',$sInterfaceValidCode[1])[0];
					$errorCode1 = explode('|',$sInterfaceValidCode[1])[1];
					if ($OrderInfo['iInterfaceID'] == ConfigParse::getInterfaceKey('InterfaceType_Wangpiao') && $arResult['data']==12){
						$sValidCodeErrMsg = '可凭取票码到网票网取票机取票'.'（如遇机器故障：故障码：'.$errorCode0.'，验证码：'.$errorCode1.'）。';
					}else{
						$sValidCodeErrMsg = '可凭取票码到自助取票机取票（如遇机器故障：故障码：'.$errorCode0.'  '.'验证码：'.$errorCode1.'）。';
					}
				}else{
					$errorCode0 = explode('|',$sInterfaceValidCode[1])[0];
					if ($OrderInfo['iInterfaceID'] == ConfigParse::getInterfaceKey('InterfaceType_Wangpiao') && $arResult['data']==12){
						$sValidCodeErrMsg = '可凭取票码到网票网取票机取票'.'（如遇机器故障：故障码：'.$errorCode0.'）。';
					}else{
						$sValidCodeErrMsg = '可凭取票码到自助取票机取票（如遇机器故障：故障码：'.$errorCode0.'）。';
					}

				}
			}else{
				if($OrderInfo['iInterfaceID'] == ConfigParse::getInterfaceKey('InterfaceType_Wangpiao') && in_array($arResult['data'],$Stype)){
					$sValidCodeErrMsg = '可凭取票码到网票网取票机取票。';
				}else{
					$sValidCodeErrMsg = '可凭取票码到自助取票机取票。';
				}
			}

			if(count(explode('|',$sInterfaceValidCode[0])) == 2){
				$arrCode0 = explode('|',$sInterfaceValidCode[0])[0];
				$arrCode1 = explode('|',$sInterfaceValidCode[0])[1];
				$errorCode0 = explode('|',$sInterfaceValidCode[0])[0];
				$errorCode1 = explode('|',$sInterfaceValidCode[0])[1];
				$sValidCodeMsg = $arrCode0.'  '.'验证码：'.$arrCode1;
			}else{
				$arrCode0 = explode('|',$sInterfaceValidCode[0])[0];
				$errorCode0 = explode('|',$sInterfaceValidCode[0])[0];
				$sValidCodeMsg = $arrCode0;
			}
		}
		$day = date("Y-m-d",strtotime($OrderInfo['dPlayTime']));
		$dateDay = strtotime(date("Y-m-d"));
		$days=round((strtotime($day)-$dateDay)/86400);
		$week = "周".mb_substr("日一二三四五六",date("w",strtotime($OrderInfo['dPlayTime'])),1,"utf-8");
		$date = explode('-',$day);
		$time = date("H:i",strtotime($OrderInfo['dPlayTime']));
		switch($days){
			case 0:
				$date = '今天'.($date[1]<=9 ? str_replace('0','',$date[1]) : $date[1]).'月'.($date[2]<=9 ? str_replace('0','',$date[2]) : $date[2]).'日'." ".$time;
				break;
			case 1:
				$date = '明天'.($date[1]<=9 ? str_replace('0','',$date[1]) : $date[1]).'月'.($date[2]<=9 ? str_replace('0','',$date[2]) : $date[2]).'日'." ".$time;
				break;
			case 2:
				$date = '后天'.($date[1]<=9 ? str_replace('0','',$date[1]) : $date[1]).'月'.($date[2]<=9 ? str_replace('0','',$date[2]) : $date[2]).'日'." ".$time;
				break;
			case 3:
				$date = $week.($date[1]<=9 ? str_replace('0','',$date[1]) : $date[1]).'月'.($date[2]<=9 ? str_replace('0','',$date[2]) : $date[2]).'日'." ".$time;
				break;
			case 4:
				$date = $week.($date[1]<=9 ? str_replace('0','',$date[1]) : $date[1]).'月'.($date[2]<=9 ? str_replace('0','',$date[2]) : $date[2]).'日'." ".$time;
				break;
			case 5:
				$date = $week.($date[1]<=9 ? str_replace('0','',$date[1]) : $date[1]).'月'.($date[2]<=9 ? str_replace('0','',$date[2]) : $date[2]).'日'." ".$time;
				break;
			case 6:
				$date = $week.($date[1]<=9 ? str_replace('0','',$date[1]) : $date[1]).'月'.($date[2]<=9 ? str_replace('0','',$date[2]) : $date[2]).'日'." ".$time;
				break;
			default:
				break;
		}
		$seatInfo = array();
		foreach(explode(',',$OrderInfo['orderInfo']) as $k => $v){
			$seatInfo[$k] = $v.'座';
		}

		if((!empty($arrCode0) && !empty($arrCode1) &&!empty($errorCode0) &&!empty($errorCode1)) && (($arrCode0 != $errorCode0) && ($arrCode0 != $errorCode1) &&($arrCode1 != $errorCode0)&&($arrCode1 != $errorCode1))){
			$type = 'SMS_7776215';
			//双码不一致
			$ValidCode = array(
				'arrCode0' => $arrCode0,
				'arrCode1' => $arrCode1,
				'errorCode0' => $errorCode0,
				'errorCode1' => $errorCode1,
			);
		}
		if((!empty($arrCode0) && !empty($arrCode1) &&!empty($errorCode0) &&!empty($errorCode1)) && (($arrCode0 == $errorCode0) && ($arrCode0 != $errorCode1) &&($arrCode1 != $errorCode0)&&($arrCode1 == $errorCode1))){
			$type = 'SMS_7806238';
			//双码一致
			$ValidCode = array(
				'arrCode0' => $arrCode0,
				'arrCode1' => $arrCode1,
			);
		}
		if((!empty($arrCode0) && !empty($arrCode1) &&!empty($errorCode0) && empty($errorCode1)) && (($arrCode0 != $errorCode0) &&($arrCode1 != $errorCode0))){
			$type = 'SMS_7741432';
			//双单码
			$ValidCode = array(
				'arrCode0' => $arrCode0,
				'arrCode1' => $arrCode1,
				'errorCode' => $errorCode0,
			);
		}
		if((!empty($arrCode0) && empty($arrCode1) &&!empty($errorCode0) && !empty($errorCode1)) && (($arrCode0 != $errorCode0) &&($arrCode0 != $errorCode1))){
			$type = 'SMS_7761503';
			//单双码
			$ValidCode = array(
				'arrCode' => $arrCode0,
				'errorCode0' => $errorCode0,
				'errorCode1' => $errorCode1
			);
		}
		if((!empty($arrCode0) && empty($arrCode1) &&!empty($errorCode0) && empty($errorCode1)) && (($arrCode0 != $errorCode0))){
			$type = 'SMS_7771407';
			//单码不一致
			$ValidCode = array(
				'validCode' => $arrCode0,
				'errorCode' => $errorCode0,
			);
		}
		if((!empty($arrCode0) && empty($arrCode1) &&!empty($errorCode0) && empty($errorCode1)) && (($arrCode0 == $errorCode0))){
			$type = 'SMS_7811111';
			//单码一致
			$ValidCode = array(
				'validCode' => $arrCode0
			);
		}

//${dateTime}${sCinemaName}的${sMovieName}，${sRoomName}${strSeatInfo}。

		$arPara = array(
			'dateTime'=>$date,
			'sCinemaName'=>$OrderInfo['sCinemaName'],
			'sMovieName'=>$OrderInfo['sMovieName'],
			'sRoomName'=>$OrderInfo['sMovieName'],
			'strSeatInfo'=>implode(',',$seatInfo)
		);

		$arPara = array_merge($ValidCode,$arPara);
		$ret = SMSProcess::sendDayuSMS($OrderInfo['sendPhone'], $arPara, $type);
		if($formId != ''){
			if($ret){
				$openid = UserProcess::getOpenid(UserProcess::getUInfo($OrderInfo['iUserId'])['sPhone'])['openid'];
				//微信通知
				$wx = new Weixin();
				$template = array(
					'touser' => "$openid",
					'template_id' => 'oIiIM1ePKylRqvpIj63R6L2JrjKWLEZU_XwL_tAiSTk',
					'page' =>"pages/movie/index",
					'form_id'=>"$formId",
					'data'=> array(
						'keyword1' => array('value'=>$OrderInfo['sMovieName'], 'color'=>'#173177'),
						'keyword2' => array('value'=>$date, 'color'=>'#173177'),
						'keyword3' => array('value'=>$OrderInfo['sCinemaName'].'　'.$OrderInfo['sRoomName'], 'color'=>'#173177'),
						'keyword4' => array('value'=>implode(',',$seatInfo), 'color'=>'#173177'),
						'keyword5' => array('value'=>$sValidCodeMsg, 'color'=>'#173177'),
						'keyword6' => array('value'=>$sValidCodeErrMsg, 'color'=>'#173177'),
					)
				);
				$wx->wxsendTemplateMsg($openid,$template);
			}
		}
		return $ret;
		//exit(0);
	}

	//充值订单 0电影卡充值订单 1余额充值订单
	public static function confirmRechargeOrder($orderId)
	{
		if(empty($orderId)){
			return array('ok' => false);
		}

		$userOrderInfo = OrderProcess::getOrderInfo($orderId);
		$orderInfo['orderPayType'] = ConfigParse::getPayTypeKey('weixinPay');
		$orderInfo['orderStatus'] = ConfigParse::getPayStatusKey('orderSucess');
		$orderInfo['outerOrderId'] = $orderId;
		$mAccountMoney = '-'.$userOrderInfo['totalPrice'];
		if(UserProcess::updateAccountPay($userOrderInfo['iUserId'],$mAccountMoney)){
			if(OrderProcess::updateUserOrderInfo($orderInfo)){
				return array('ok' => true,'userID' => $userOrderInfo['iUserId']);
			}
		}
		return array('ok' => false);
	}

	//获取余额交易记录
	public static function getOrderRechargeLog($userID)
	{
		if(empty($userID)){
			return array('ok' => false);
		}
		$orderstatus = ConfigParse::getPayStatusKey('orderSucess').','.ConfigParse::getPayStatusKey('orderPay').','.ConfigParse::getPayStatusKey('orderAsynSeatSucess').','.ConfigParse::getPayStatusKey('orderAsynSeatEnd');
		$orderpaystatus = ConfigParse::getPayTypeKey('accountPay').','.ConfigParse::getPayTypeKey('umpay');
		$SQL = sprintf("select outerOrderId, mPrice,orderType,createTime,orderPayType,orderStatus from {{l_orders}} where iUserID=%d AND (orderType = '%s' OR  orderPayType in(%s)) AND orderStatus IN (%s) ORDER BY createTime DESC ",  $userID,ConfigParse::getOrdersKey('accountRechargeOrder'),$orderpaystatus,$orderstatus);
		return DbUtil::queryAll($SQL);
	}

	//创建活动的订单
	public static function createHuoDongCardOrder($iUserID,$mPrice,$iCount,$orderInfo,$returnUrl,$sendPhone,$fromClient,$payMethod,$iHuoDongItemID,$huodong_types=0,$ordertype)
	{
		$outerOrderId=self::createOuterOrderId();
		$userOrderInfo=array('iUserId'=>$iUserID,
			'outerOrderId'=>$outerOrderId,
			'mPrice'=>$mPrice,
			'totalPrice'=>$iCount*$mPrice,
			'orderInfo'=>$orderInfo,
			'sendPhone'=>$sendPhone,
			'fromClient'=>$fromClient,
			'returnUrl'=>$returnUrl,
			'orderType'=>$ordertype,
			'orderStatus'=>ConfigParse::getPayStatusKey('orderNoPay'),
			'orderPayType'=>$payMethod,
			'iHuoDongItemID'=>$iHuoDongItemID,
			'iownSeats'=>$iCount,
			'huodong_types'=>$huodong_types
		);
		self::insertUserOrderInfo($userOrderInfo);
		return array("ok"=>true,"data"=> $userOrderInfo);
	}

	/**
	 * h5支付逻辑
	 * @param $outerOrderId
	 * @param $payType
	 * @return array
	 */
	public static function paySeatOnlineOrder($outerOrderId, $payType){
		$userOrderInfo = self::getOrderSeatInfoByOuterOrderId($outerOrderId);
		if(empty($userOrderInfo))
		{
			return array("ok"=>false,"flag"=>'orderStatus','err_code'=>401,'msg'=>'订单不存在');
		}
		if ($userOrderInfo['orderStatus'] != 10101)
		{
			return array("ok"=>false,"flag"=>'orderStatus','err_code'=>401,'msg'=>'订单状态错误');
		}
		GeneralFunc::writeLog('paySeatOnlineOrder1,'.$outerOrderId.",".$payType, Yii::app()->getRuntimePath().'/H5yii/');
		switch ($payType)
		{
			//余额支付
			case ConfigParse::getPayTypeKey('accountPay'):
				return self::confirmSeatOnlineOrderWithNoBankPay($userOrderInfo['iUserId'],$outerOrderId,$payType);
			default:
				return (array("ok"=>false,'msg'=>'请选择支付方式或验证电影卡'));
		}
	}

	/**
	 * 余额支付
	 * @param $iUserID
	 * @param $outerOrderId
	 */
	public static function confirmSeatOnlineOrderWithNoBankPay($iUserID,$outerOrderId,$payType){
		$orderInfo = self::getOrderSeatInfoByOuterOrderId($outerOrderId);
		if(empty($orderInfo))
		{
			return (array("ok"=>false,'err_code'=>401,'msg'=>'订单不存在'));
		}
		GeneralFunc::writeLog('confirmSeatOnlineOrderWithNoBankPay'.$outerOrderId.",".$iUserID.','.$orderInfo['totalPrice'], Yii::app()->getRuntimePath().'/H5yii/');
		$accountPrice = $orderInfo['totalPrice'];
		if (UserProcess::getmAccountMoney($iUserID)>=$accountPrice)
		{
			$retaccount = UserProcess::updateAccountPay($iUserID,$accountPrice);
			if ($retaccount <= 0)
			{
				return (array("ok"=>false,'err_code'=>401,'msg'=>'余额支付异常'));
			}
			$arPaylog['outerOrderId'] = $outerOrderId;
			$arPaylog['totalPrice'] = $accountPrice;
			$arPaylog['iUserID'] = $iUserID;
			$arPaylog['bankType'] = $payType;
			$arPaylog['status'] = ConfigParse::getPayStatusKey('orderPay');
			self::addPayForSeat($arPaylog);
		}
		else {
			return (array("ok"=>false,'err_code'=>401,'msg'=>'您的余额不足，请先充值'));
		}
		$updateorderInfo['orderPayType'] = ConfigParse::getPayTypeKey('accountPay');
		$updateorderInfo['outerOrderId'] = $outerOrderId;
		$updateorderInfo['orderStatus'] = ConfigParse::getPayStatusKey('orderPay');
		GeneralFunc::writeLog('confirmSeatOnlineOrderWithNoBankPay'.$outerOrderId.",".ConfigParse::getPayTypeKey('accountPay').','.ConfigParse::getPayStatusKey('orderPay'), Yii::app()->getRuntimePath().'/H5yii/');
		OrderProcess::updateUserOrderInfo($updateorderInfo);
		GeneralFunc::writeLog('confirmSeatOnlineOrderWithNoBankPay,updateUserOrderInfo'.$outerOrderId.",".ConfigParse::getPayTypeKey('accountPay').','.ConfigParse::getPayStatusKey('orderPay'), Yii::app()->getRuntimePath().'/H5yii/');
		self::confirmSeatOnlineOrder($iUserID, $outerOrderId,$orderInfo['sendPhone']);
		GeneralFunc::gotoUrl('index.php?r=output/Site/Success&orderId='.$outerOrderId.'&userId='.$iUserID);
		return array('ok'=>true);
	}

	/**
	 * 增加paylog记录
	 * @param $arPaylog
	 * @return mixed
	 */
	public static function addPayForSeat($arPaylog){
		$subSql = array();
		$csubSql=array();
		foreach($arPaylog as $key =>$v)
		{
			$subSql[] = "'".mysql_escape_string($v)."'";
			$csubSql[]=$key;
		}
		$SQL = sprintf("insert into  {{l_paylog}} (%s) VALUES(%s)",implode(',', $csubSql),implode(',', $subSql));
		return DbUtil::execute($SQL);
	}

	//追龙
	public static function getUserOrderByMovieID($iUserID,$MovieID)
	{
		if(empty($iUserID)||empty($MovieID)){
			return 0;
		}
		$SQL = sprintf("SELECT count(outerOrderId) as UserNum FROM {{l_order_seat}} WHERE iUserId=%s and iMovieID=$MovieID and (status >= 10101 and status < 10206) and sInterfaceValidCode != '1|2*3' order by dCreateTime DESC ", "'$iUserID'");
		return DbUtil::queryRow($SQL);
	}
//根据接口方订单号获取订单号
	public static function getOrderSeatInfoByInFOrderID($InFOrderID)
	{
		if(empty($InFOrderID)){
			return 0;
		}
		$SQL = sprintf("SELECT * FROM {{l_order_seat}} WHERE sInterfaceOrderNo='%s' ",$InFOrderID);
		return DbUtil::queryRow($SQL);
	}
}