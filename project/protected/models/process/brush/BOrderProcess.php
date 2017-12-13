<?php

/**
 * BOrderProcess - 订单操作类
 * @author lzz
 * @version V1.0
 */


class BOrderProcess
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

	/**
	 * 获取订单信息（By 订单编号）
	 *
	 * @param string $outerOrderId 订单编号
	 * @return array[] 订单信息
	 */
	public static function getOrderSeatInfoByOuterOrderId($outerOrderId)
	{
		$arSeatInfo = self::getOrderSeatInfo($outerOrderId);
		$arOrderInfo = self::getOrderInfo($outerOrderId);
		return array_merge($arSeatInfo,$arOrderInfo);
	}

	/**
	 * 获取订单基本信息（By 订单编号）
	 *
	 * @param string $outerOrderId 订单编号
	 * @return array[] 订单基本信息
	 */
	public static function getOrderInfo($outerOrderId)
	{
		$sql = sprintf("SELECT * FROM {{fill_orders}} WHERE outerOrderId='%s' ",$outerOrderId);
		return DbUtil::queryRow($sql);
	}
		
	/**
	 * 获取订单座位信息（By 订单编号）
	 *
	 * @param string $outerOrderId 订单编号
	 * @return array[] 订单座位信息
	 */
	public static function getOrderSeatInfo($outerOrderId)
	{
		$sql = sprintf("SELECT * FROM {{fill_order_seat}} WHERE outerOrderId='%s'",$outerOrderId);
		return DbUtil::queryRow($sql);
	}
	
	/**
	 * 修改未支付订单
	 *
	 * @param string $outerOrderId 订单编号
	 * @return bool
	 */
	public static function updateUserUnfinishOrder($outerOrderId)
	{
		if(empty($outerOrderId)){
			return FALSE;
		}
	
		$sql = sprintf("UPDATE {{fill_order_seat}} SET status=".ConfigParse::getPayStatusKey('orderClose')." WHERE outerOrderId='%s'",$outerOrderId);
		if(DbUtil::execute($sql))
		{
			$sql = sprintf("UPDATE {{fill_orders}} SET orderStatus=".ConfigParse::getPayStatusKey('orderClose').",closeTime='%s', orderInfo='订单已经取消' WHERE outerOrderId='%s'", date('Y-m-d H:i:s'),$outerOrderId);
			return DbUtil::execute($sql);
		}
		return TRUE;
	}
	
	/**
	 * 创建在线选座订单
	 *
	 * @param int $iUserID 用户id
	 * @param ......
	 * @return string 订单NO
	 */
	public static function createSeatOnlinOrder($iUserID,$iRoommovieID,$sendPhone,$iSelectedCount,$seatInfo,$orderInfo,$fromClient,$returnUrl,$type, $iSeatNum=0, $iSellNum=0, $iBrushNum=0, $lockModel=1)
	{
		$outerOrderId = self::createOuterOrderId();
		$roomMovieInfo = BaseProcess::getRoommovieInfoByRoommovieID($iRoommovieID);
		
		$userOrderInfo = array('iUserID'=>$iUserID,
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
				'iHuoDongItemID'=>0,
				'iSeatNum'=>$iSeatNum,
				'iSellNum'=>$iSellNum,
				'iBrushNum'=>$iBrushNum,
				'iLocalModel'=>$lockModel,
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
				'isWeChatapplet' =>1,
	
		);
		if(self::insertUserOrderInfo($userOrderInfo) && self::insertUserOrderSeatInfo($userOrderSeatInfo))
		{
			return $outerOrderId;
		}else{
			return '';
		}
	}
	
	/**
	 * 创建在线选座订单（用于注水）
	 *
	 * @param int $iUserID 用户id
	 * @param ......
	 * @return string 订单NO
	 */
	public static function createSeatOnlinOrderForFake($iUserID,$iRoommovieID,$sendPhone,$iSelectedCount,$seatInfo,$orderInfo,$fromClient,$returnUrl,$type, $lockModel=1)
	{
		$outerOrderId = self::createOuterOrderId();
		$roomMovieInfo = BaseProcess::getRoommovieInfoByRoommovieID($iRoommovieID);
	
		$userOrderInfo = array('iUserID'=>$iUserID,
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
				'iHuoDongItemID'=>0,
				'iBrushNum'=>count(explode('@@', $seatInfo)),
				'iFakeNum'=>count(explode('@@', $seatInfo)),
				'iLocalModel'=>$lockModel,
				'orderStatus'=>10210,
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
				'status'=>10210,
				'dPlayTime'=>$roomMovieInfo['dBeginTime'],
				'mSettingPrice' =>$roomMovieInfo['mSettlementPrice'],
				'isWeChatapplet' =>1,
				'form_id' =>$lockModel,
	
		);
		if(self::insertUserOrderInfo($userOrderInfo) && self::insertUserOrderSeatInfo($userOrderSeatInfo))
		{
			return $outerOrderId;
		}else{
			return '';
		}
	}
	/**
	 * 创建在线订单NO
	 *
	 * @return string 订单NO
	 */
	public static function createOuterOrderId()
	{ 
		$charid = strtoupper(substr(md5(uniqid(mt_rand(), true)),8,24));
		$hyphen = '';
		$uuid = substr($charid, 0, 8).substr($charid, 8, 4).$hyphen.substr($charid,12, 4).$hyphen.substr($charid,16, 4).$hyphen.substr($charid,20,12);
		return "DD-".date("md")."-".$uuid;
	}
	
	/**
	 * 创建基本订单
	 *
	 * @param array[] $userOrderInfo 基本订单信息
	 * @return string 订单NO
	 */
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
		$sql = sprintf("INSERT INTO {{fill_orders}} (%s) VALUES(%s)", implode(',', $csubSql), implode(',', $subSql));
		return DbUtil::execute($sql);
	}
	
	/**
	 * 创建座位订单
	 *
	 * @param array[] $orderSeatInfo 座位订单信息
	 * @return string 订单NO
	 */
	public static function insertUserOrderSeatInfo($orderSeatInfo)
	{
		if(empty($orderSeatInfo))
		{
			return array();
		}
		$subSql = array();
		$csubSql=array();
		foreach($orderSeatInfo as $key =>$v)
		{
			$subSql[] = "'".mysql_escape_string($v)."'";
			$csubSql[]=$key;
		}
		$sql = sprintf("INSERT INTO {{fill_order_seat}} (%s) VALUES(%s)", implode(',', $csubSql), implode(',', $subSql));
		return DbUtil::execute($sql);
	}
	
	/**
	 * 删除锁座基本订单（锁座失败）
	 *
	 * @param string $outerOrderId 订单NO
	 * @return bool
	 */
	public static function delUserOrdersInfo($outerOrderId)
	{
		if(isset($outerOrderId) && !empty($outerOrderId)) 
		{
			$sql = sprintf("DELETE FROM {{fill_orders}} WHERE outerOrderId='%s'", $outerOrderId);
			DbUtil::execute($sql);
			return TRUE;
		}
		return FALSE;
	}
	
	/**
	 * 删除锁座座位订单（锁座失败）
	 *
	 * @param string $outerOrderId 订单NO
	 * @return bool
	 */
	public static function delUserOrderSeatInfo($outerOrderId)
	{
		if(isset($outerOrderId) && !empty($outerOrderId))
		{
			$sql = sprintf("DELETE FROM {{fill_order_seat}} WHERE outerOrderId='%s'", $outerOrderId);
			DbUtil::execute($sql);
			return TRUE;
		}
		return FALSE;
	}
	
	/**
	 * 删除锁座订单（锁座失败）
	 *
	 * @param string $outerOrderId 订单NO
	 * @return bool
	 */
	public static function delOrderInfo($outerOrderId)
	{
		if(isset($outerOrderId) && !empty($outerOrderId))
		{
			$sql = sprintf("DELETE FROM {{fill_orders}} WHERE outerOrderId='%s'", $outerOrderId);
			DbUtil::execute($sql);
			
			$sql = sprintf("DELETE FROM {{fill_order_seat}} WHERE outerOrderId='%s'", $outerOrderId);
			DbUtil::execute($sql);
			
			return TRUE;
		}
		return FALSE;
	}
	
	/**
	 * 获取paylog订单信息
	 *
	 * @param string $outerOrderId 订单NO
	 * @return array[][] 订单信息
	 */
	public static function getOrderPaylogList($outerOrderId)
	{
		if(empty($outerOrderId)){
			return array();
		}
		$sql = sprintf("SELECT * FROM {{fill_paylog}} WHERE outerOrderId=%s", "'$outerOrderId'");
		return DbUtil::queryAll($sql);
	}
	
	/**
	 * 删除paylog订单信息
	 *
	 * @param string $outerOrderId 订单NO
	 * @return bool
	 */
	public static function delOrderPaylog($outerOrderId)
	{
		if(isset($outerOrderId) && !empty($outerOrderId))
		{
			$sql = sprintf("DELETE FROM {{fill_paylog}} WHERE outerOrderId='%s'", $outerOrderId);
			DbUtil::execute($sql);
			return TRUE;
		}
		return FALSE;
	}
	
	/**
	 * 添加Paylog订单信息
	 *
	 * @param string $outerOrderId 订单NO
	 * @param ......
	 * @return bool
	 */
	public static function addOrderPaylog($outerOrderId, $sCheckNo,$iUserID, $count, $cardCount,$couponId,$payCard,$payType,$openid='',$totalPrice='',$sVoucherPassWord = '')
	{
		$paylogInfo = array(
				'outerOrderId' => $outerOrderId,
				'createTime' => '',
				'totalPrice' => $totalPrice,
				'bankType' => '400003',
				'payTime' => date('Y-m-d h:i:s'),
				'tradeId' => '',
				'sCheckNo' => $sCheckNo,
				'sPassword' => '',
				'iCouponID' => $couponId,
				'ticketCode' => '',
				'count' => $count,
				'tradeNo' => '',
				'status' => ConfigParse::getPayStatusKey('orderPay'),
				'iUserID' => $iUserID,
				'iHuodongItemID' => '',
				'sVoucherPassWord' => $sVoucherPassWord,
				'cardCount' => $cardCount,
		);
		if(self::insertOrderPaylog($paylogInfo))
		{
			return TRUE;
		}
		
		return FALSE;
	}
	
	/**
	 * 添加Paylog订单信息（直接入库）
	 *
	 * @param array[] $paylogInfo 订单信息
	 * @return bool
	 */
	public static function insertOrderPaylog($paylogInfo)
	{
		if(empty($paylogInfo))
		{
			return FALSE;
		}
		$subSql = array();
		$csubSql=array();
		foreach($paylogInfo as $key =>$v)
		{
			$subSql[] = "'".mysql_escape_string($v)."'";
			$csubSql[] = $key;
		}
		$sql = sprintf("INSERT INTO {{fill_paylog}} (%s) VALUES(%s)", implode(',', $csubSql), implode(',', $subSql));
		DbUtil::execute($sql);
		
		return TRUE;
	}
	
	/**
	 * 修改在线选座基本订单信息
	 *
	 * @param array[] $orderSeatInfo 座位基本信息
	 * @return bool
	 */
	public static function updateOrderInfo($userOrderInfo)
	{
		if(empty($userOrderInfo))
		{
			return FALSE;
		}
		$subSql = array();
		foreach($userOrderInfo as $key =>$v)
		{
			if(!empty($v)){
				$subSql[] = "$key='".mysql_escape_string($v)."'";
			}
		}
		$sql = sprintf("UPDATE {{fill_orders}} SET %s WHERE outerOrderId='%s'", implode(',', $subSql), $userOrderInfo["outerOrderId"]);
		DbUtil::execute($sql);
		
		return TRUE;
	}
	
	/**
	 * 修改在线选座座位订单信息
	 *
	 * @param int $iUserID 用户id
	 * @param array[] $orderSeatInfo 座位订单信息
	 * @return bool
	 */
	static function updateOrderSeatInfo($iUserID, $orderSeatInfo)
	{
		if($iUserID!=$orderSeatInfo['iUserId'])
		{
			return FALSE;
		}
		$subSql = array();
		foreach($orderSeatInfo as $key =>$v)
		{
			if(!empty($v)){
				$subSql[] = "$key='".mysql_escape_string($v)."'";
			}
		}
		$sql = sprintf("UPDATE {{fill_order_seat}} SET %s WHERE outerOrderId='%s'", implode(',', $subSql), $orderSeatInfo["outerOrderId"]);
		DbUtil::execute($sql);
		
		return TRUE;
	}
	
	/**
	 * 修改在线选座支付订单信息
	 *
	 * @param array[] $paylogInfo 座位支付信息
	 * @return bool
	 */
	public static function updatePaylogInfo($paylogInfo)
	{
		if(empty($paylogInfo))
		{
			return FALSE;
		}
		
		$subSql = array();
		foreach($paylogInfo as $key =>$v)
		{
			if(!empty($v)){
				$subSql[] = "$key='".mysql_escape_string($v)."'";
			}
		}
		$sql = sprintf("UPDATE {{fill_paylog}} SET %s WHERE outerOrderId='%s'", implode(',', $subSql), $paylogInfo["outerOrderId"]);
		DbUtil::execute($sql);
		
		return TRUE;
	}
	
	/**
	 * 申请下单
	 *
	 * @param string $orderSeatInfo 座位订单信息
	 * @param array[] $uInfo 用户信息
	 * @return bool
	 */
	public static function applyTicket($outerOrderId, $uInfo)
	{
		$orderInfo = self::getOrderSeatInfoByOuterOrderId($outerOrderId);
		if (empty($orderInfo))
		{
			return FALSE;
		}
		
		$ret = BInterfProcess::GetConfirmOrderResult($orderInfo);		
		if($ret['ResultCode'] == ConfigParse::getOrderStatusKey('ORDER_STATUS_Successed')){
			if(isset($ret['InterfaceValidCode']) && !empty($ret['InterfaceValidCode'])){		//网票同步
				
				echo "Pay Type: WANGPIAO SYNC\r\n";
				//完成订单
				$upOrderInfo = array('orderStatus'=>ConfigParse::getPayStatusKey('orderSucess'), 'outerOrderId'=>$outerOrderId);
				self::updateOrderInfo($upOrderInfo);
				
				$upPaylogInfo = array('iUserID'=>$uInfo['iUserID'], 'outerOrderId'=>$outerOrderId
						, 'ticketCode'=>$ret['InterfaceValidCode'], 'status'=>ConfigParse::getPayStatusKey('orderSucess'));
				self::updatePaylogInfo($upPaylogInfo);
					
				$upOrderSeatInfo = array('sInterfaceValidCode'=>$ret['InterfaceValidCode'], 'outerOrderId'=>$outerOrderId, 'lowValue'=>0
						, 'status'=>ConfigParse::getPayStatusKey('orderSucess'), 'iUserId'=>$uInfo['iUserID'], 'sPhone'=>$uInfo['sPhone']);
				self::updateOrderSeatInfo($uInfo['iUserID'], $upOrderSeatInfo);
				
				//刷量统计
				self::addBrushSeatNum($orderInfo['iRoomMovieID'], $orderInfo['iownSeats']);
			}elseif(isset($ret['InterfaceValidCode']) && empty($ret['InterfaceValidCode'])){	//网票异步
				
				echo "Pay Type: WANGPIAO ASYNC\r\n";
				//开始异步订单，定时请求取票码
				$upOrderInfo = array('orderStatus'=>ConfigParse::getPayStatusKey('orderAsynSeatBegin'), 'outerOrderId'=>$outerOrderId);
				self::updateOrderInfo($upOrderInfo);
					
				$upOrderSeatInfo = array('outerOrderId'=>$outerOrderId, 'iUserId'=>$uInfo['iUserID'], 'sPhone'=>$uInfo['sPhone']);
				self::updateOrderSeatInfo($uInfo['iUserID'], $upOrderSeatInfo);
				
			}else{
				//格瓦拉同步获取取票码
				//$arResult = CinemaInterfaceProcess::GetOrderInfoResult($outerOrderId);
				$arResult = BInterfProcess::GetApplyTicket($orderInfo);
				
				if($arResult['OrderStatus'] == ConfigParse::getOrderStatusKey('ORDER_STATUS_Successed')&&!empty($arResult['FetchNo'])){	//格瓦拉同步
					
					echo "Pay Type: GWL SYNC\r\n";
					//完成订单
					$upOrderInfo = array('orderStatus'=>ConfigParse::getPayStatusKey('orderSucess'), 'outerOrderId'=>$outerOrderId);
					self::updateOrderInfo($upOrderInfo);
					
					$upPaylogInfo = array('iUserID'=>$uInfo['iUserID'], 'outerOrderId'=>$outerOrderId
							, 'ticketCode'=>$ret['FetchNo'], 'status'=>ConfigParse::getPayStatusKey('orderSucess'));
					self::updatePaylogInfo($upPaylogInfo);
						
					$upOrderSeatInfo = array('sInterfaceValidCode'=>$ret['FetchNo'], 'outerOrderId'=>$outerOrderId, 'lowValue'=>0
							, 'status'=>ConfigParse::getPayStatusKey('orderSucess'), 'iUserId'=>$uInfo['iUserID'], 'sPhone'=>$uInfo['sPhone']);
					self::updateOrderSeatInfo($uInfo['iUserID'], $upOrderSeatInfo);
						
					//刷量统计
					self::addBrushSeatNum($orderInfo['iRoomMovieID'], $orderInfo['iownSeats']);

				}else{																						//格瓦拉异步
					
					echo "Pay Type: GWL ASYNC\r\n";
					//异步开始
					$upOrderInfo = array('orderStatus'=>ConfigParse::getPayStatusKey('orderAsynSeatBegin'), 'outerOrderId'=>$outerOrderId);
					self::updateOrderInfo($upOrderInfo);
					
					$upOrderSeatInfo = array('outerOrderId'=>$outerOrderId, 'iUserId'=>$uInfo['iUserID'], 'sPhone'=>$uInfo['sPhone']);
					self::updateOrderSeatInfo($uInfo['iUserID'], $upOrderSeatInfo);
				}
			}
		}else{
			
			echo "Order Status Not Sucess: INTO ASYNC\r\n";
			//异步开始
			$upOrderInfo = array('orderStatus'=>ConfigParse::getPayStatusKey('orderAsynSeatBegin'), 'outerOrderId'=>$outerOrderId);
			self::updateOrderInfo($upOrderInfo);
			
			$upOrderSeatInfo = array('outerOrderId'=>$outerOrderId, 'iUserId'=>$uInfo['iUserID'], 'sPhone'=>$uInfo['sPhone']);
			self::updateOrderSeatInfo($uInfo['iUserID'], $upOrderSeatInfo);
		}
		
		return TRUE;
	}

	/**
	 * 获取已过期订单列表（未支付）
	 * 
	 * @param int $overSecond 过期时长（秒）
	 * @return array[][] 订单列表
	 */
	public static function getNopayOrderList($overSecond=900)
	{
		$sql = sprintf("SELECT outerOrderId,iInterfaceID,sInterfaceOrderNo,sCinemaInterfaceNo,sRoomMovieInterfaceNo,sPhone,iUserId FROM {{fill_order_seat}} WHERE status=%d AND TIMESTAMPDIFF(SECOND,dCreateTime,NOW())>%d", ConfigParse::getPayStatusKey('orderNoPay'), $overSecond);
		return DbUtil::queryAll($sql);
	}
	
	/**
	 * 获取异步订单列表
	 * 
	 * @return array[][] 订单列表
	 */
	public static function getAsyOrderList()
	{
		$sql = sprintf("SELECT o.outerOrderId,o.createTime,o.iownSeats,os.iInterfaceID,os.sPhone,os.sInterfaceOrderNo,os.iUserID,os.sCinemaInterfaceNo,os.sRoomMovieInterfaceNo,os.iRoomMovieID FROM {{fill_orders}} o,{{fill_order_seat}} os WHERE o.orderStatus=%d AND o.outerOrderId=os.outerOrderId", ConfigParse::getPayStatusKey('orderAsynSeatBegin'));
		return DbUtil::queryAll($sql);
	}
	
	/**
	 * 获取待支付订单列表
	 *
	 * @param int $limit 执行上限（每次执行多少条）
	 * @return array[][] 订单列表
	 */
	public static function getTopayOrderList($limit=100)
	{
		$sql = sprintf("SELECT outerOrderId,iownSeats,sendPhone,iUserId FROM {{fill_orders}} WHERE orderStatus=%d ORDER BY createTime DESC LIMIT %d", ConfigParse::getPayStatusKey('orderNoPay'), $limit);
		return DbUtil::queryAll($sql);
	}
	
	/**
	 * 修改售卖订单信息
	 *
	 * @param string $iRoommovieID 排片ID
	 * @param array[] $sellInfo 售卖信息
	 * @return bool
	 */
	public static function updateSellInfo($iRoommovieID, $sellInfo)
	{
		if(empty($iRoommovieID) or empty($sellInfo))
		{
			return FALSE;
		}
		$subSql = array();
		foreach($sellInfo as $key =>$v)
		{
			if(!empty($v)){
				$subSql[] = "$key='".mysql_escape_string($v)."'";
			}
		}
		
		$sql = sprintf("UPDATE {{fill_sell}} SET %s WHERE iRoomMovieID='%s'", implode(',', $subSql), $iRoommovieID);
		DbUtil::execute($sql);
	
		return TRUE;
	}
	
	/**
	 * 添加刷票座位数量
	 *
	 * @param string $iRoommovieID 排片ID
	 * @param int $brushSeatNum 刷票座位数量
	 * @return bool
	 */
	public static function addBrushSeatNum($iRoommovieID, $brushSeatNum)
	{
		if(empty($iRoommovieID) or empty($brushSeatNum) or !is_numeric($brushSeatNum))
		{
			return FALSE;
		}
	
		$sql = sprintf("UPDATE {{fill_sell}} SET iBrushNum=iBrushNum+%d WHERE iRoomMovieID='%s'", $brushSeatNum, $iRoommovieID);
		DbUtil::execute($sql);
	
		return TRUE;
	}
	
	
}