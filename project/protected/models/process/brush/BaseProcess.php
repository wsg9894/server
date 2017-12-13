<?php

/**
 * BaseProcess - 刷票专用
 * @author luzhizhong
 * @version V1.0
 */

class BaseProcess
{
	const LOCK_PRICE_LIMIT = 45;			//锁座价格上限（仅用于‘占座模式’），即超过35元的排片不予处理
	const LOCK_ENDBUY_COUNTDOWN = 18000;	//电影停止售票处理时间冗余（5小时，仅用于‘抢票模式’），即距停止售票还有5小时则需要抢票，防止排片流失
	
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
	 * 获取场次座位图（未售出的）
	 * @remark seatType=0为普通座；seatType=1为情侣座的第一座；seatType=2为情侣座的第二座
	 *
	 * @param string $iRoommovieID 场次ID
	 * @return array[][] 场次座位图列表
	 */
	public static function getMovieSeatList($iRoommovieID)
	{
		$aSeatList = array();
		if(empty($iRoommovieID))
		{
			echo "iRoommovieID is Empty\r\n";
			return $aSeatList;
		}
		$arArrangeInfo = self::getRoommovieInfoByRoommovieID($iRoommovieID);
		if (empty($arArrangeInfo))
		{
			echo "ArrangeInfo is Empty\r\n";
			return $aSeatList;
		}
		
		//获取影厅座位图状态
		$arRoomInfo = self::getRoomInfo($arArrangeInfo['iRoomID'], array('sSeatInfo'));
		if (empty($arRoomInfo['sSeatInfo']))
		{
			echo "Room-SeatInfo is Empty\r\n";
			return $aSeatList;
		}
		$arSeatInfo =json_decode($arRoomInfo['sSeatInfo'], true);
		
		//从接口方获取座位售卖信息
		$bookSeatingArrange['sRoomMovieInterfaceNo'] = $arArrangeInfo['sRoomMovieInterfaceNo'];
		$bookSeatingArrange['iInterfaceID'] = $arArrangeInfo['iInterfaceID'];
		$bookSeatingArrange['sCinemaInterfaceNo'] = $arArrangeInfo['sCinemaInterfaceNo'];
		$bookSeatingArrange['sRoomInterfaceNo'] = $arArrangeInfo['sRoomInterfaceNo'];
		$arLockSeatInfo = BInterfProcess::GetSelectedSeat($bookSeatingArrange);
		if(empty($arLockSeatInfo))
		{
			foreach($arSeatInfo['seatinfo'] as $k => &$seatInfo)
			{
				//$aSeatList[$k]['seatRow'] = self::parseSeatRow($seatInfo['seatRow']);
				$aSeatList[$k]['seatRow'] = $seatInfo['seatRow'];
				$aSeatList[$k]['seatNo'] = $seatInfo['SeatNo'];
				$aSeatList[$k]['seatState'] = $seatInfo['seatState'];
				$aSeatList[$k]['seatType'] = $seatInfo['seatType'];
				$aSeatList[$k]['seatId'] = 0;
				if(count(explode(':',$aSeatList[$k]['seatNo']))==1){
					$aSeatList[$k]['seatId'] = $aSeatList[$k]['seatNo'];
					$aSeatList[$k]['seatNo'] = $seatInfo['seatRow'] .':'.$seatInfo['seatCol'];
				}
			}
		}else{
			foreach ($arLockSeatInfo as $lockSeatInfo)
			{
				foreach($arSeatInfo['seatinfo'] as $k => &$seatInfo)
				{
					//$aSeatList[$k]['seatRow'] = self::parseSeatRow($seatInfo['seatRow']);
					$aSeatList[$k]['seatRow'] = $seatInfo['seatRow'];
					$aSeatList[$k]['seatNo'] = $seatInfo['SeatNo'];
					$aSeatList[$k]['seatType'] = $seatInfo['seatType'];
					$aSeatList[$k]['seatId'] = 0;
					if (($lockSeatInfo['ColumnId'] == $seatInfo['seatCol'] && $lockSeatInfo['RowId'] == $seatInfo['seatRow']) || $lockSeatInfo['SeatId'] == $seatInfo['SeatNo'])
					{
						$seatInfo['seatState'] = $lockSeatInfo['SeatStatus'];
					}
					if(count(explode(':',$aSeatList[$k]['seatNo']))==1){
						$aSeatList[$k]['seatId'] = $aSeatList[$k]['seatNo'];
						$aSeatList[$k]['seatNo'] = $seatInfo['seatRow'] .':'.$seatInfo['seatCol'];
					}
	
					$aSeatList[$k]['seatState'] = $seatInfo['seatState'];
				}
			}
		}
		
		//统计售卖座位信息
		if(FALSE == empty($arLockSeatInfo))
		{
			BOrderProcess::updateSellInfo($iRoommovieID, array('iSellNum'=>count($arLockSeatInfo)));
		}
		
		//数组梳理
		$retArr = array();
		foreach ($aSeatList as $k => $aSeatInfo)
		{
			if($aSeatInfo['seatState']==1 or $aSeatInfo['seatType']!=0)
			{
				//过滤掉‘已售出’的座位和情侣座
				continue;
			}
			if(empty($retArr[$aSeatInfo['seatRow']]['count']))
			{
				$retArr[$aSeatInfo['seatRow']]['count'] = 0;
			}
			
			$retArr[$aSeatInfo['seatRow']]['list'][] = array('seatNo'=>$aSeatInfo['seatNo'], 'seatId'=>$aSeatInfo['seatId']);
			$retArr[$aSeatInfo['seatRow']]['count'] ++;
		}
		
		return array_values($retArr);	//此处需将数组重新排序（Key值从0开始）
	}

	/**
	 * 获取场次座位图（已售出的，用于注水）
	 *
	 * @param string $iRoommovieID 场次ID
	 * @return array[][] 场次座位图列表
	 */
	public static function getLockedMovieSeatList($iRoommovieID)
	{
		$aSeatList = array();
		$arArrangeInfo = self::getRoommovieInfoByRoommovieID($iRoommovieID);
		if (empty($arArrangeInfo))
		{
			echo "ArrangeInfo is Empty\r\n";
			return $aSeatList;
		}
	
		//获取影厅座位图状态
		$arRoomInfo = self::getRoomInfo($arArrangeInfo['iRoomID'], array('sSeatInfo'));
		if (empty($arRoomInfo['sSeatInfo']))
		{
			echo "Room-SeatInfo is Empty\r\n";
			return $aSeatList;
		}
		$arSeatInfo =json_decode($arRoomInfo['sSeatInfo'], true);
	
		//从接口方获取座位售卖信息
		$bookSeatingArrange['sRoomMovieInterfaceNo'] = $arArrangeInfo['sRoomMovieInterfaceNo'];
		$bookSeatingArrange['iInterfaceID'] = $arArrangeInfo['iInterfaceID'];
		$bookSeatingArrange['sCinemaInterfaceNo'] = $arArrangeInfo['sCinemaInterfaceNo'];
		$bookSeatingArrange['sRoomInterfaceNo'] = $arArrangeInfo['sRoomInterfaceNo'];
		$arLockSeatInfo = BInterfProcess::GetSelectedSeat($bookSeatingArrange);
		if(!empty($arLockSeatInfo))
		{
			foreach ($arLockSeatInfo as $lockSeatInfo)
			{
				foreach($arSeatInfo['seatinfo'] as $k => &$seatInfo)
				{
					//$aSeatList[$k]['seatRow'] = self::parseSeatRow($seatInfo['seatRow']);
					$aSeatList[$k]['seatRow'] = $seatInfo['seatRow'];
					$aSeatList[$k]['seatNo'] = $seatInfo['SeatNo'];
					$aSeatList[$k]['seatId'] = 0;
					if (($lockSeatInfo['ColumnId'] == $seatInfo['seatCol'] && $lockSeatInfo['RowId'] == $seatInfo['seatRow']) || $lockSeatInfo['SeatId'] == $seatInfo['SeatNo'])
					{
						$seatInfo['seatState'] = $lockSeatInfo['SeatStatus'];
					}
					if(count(explode(':',$aSeatList[$k]['seatNo']))==1){
						$aSeatList[$k]['seatId'] = $aSeatList[$k]['seatNo'];
						$aSeatList[$k]['seatNo'] = $seatInfo['seatRow'] .':'.$seatInfo['seatCol'];
					}
	
					$aSeatList[$k]['seatState'] = $seatInfo['seatState'];
				}
			}
		}
	
		//数组梳理
		$retArr = array();
		foreach ($aSeatList as $k => $aSeatInfo)
		{
			if($aSeatInfo['seatState']==0)
			{
				continue;
			}
			if(empty($retArr[$aSeatInfo['seatRow']]['count']))
			{
				$retArr[$aSeatInfo['seatRow']]['count'] = 0;
			}
				
			$retArr[$aSeatInfo['seatRow']]['list'][] = array('seatNo'=>$aSeatInfo['seatNo'], 'seatId'=>$aSeatInfo['seatId']);
			$retArr[$aSeatInfo['seatRow']]['count'] ++;
		}
	
		return array_values($retArr);	//此处需将数组重新排序（Key值从0开始）
	}
	
	/**
	 * 获取场次座位图（全部）
	 *
	 * @param string $iRoommovieID 场次ID
	 * @param int $iRoomID 影厅id
	 * @return array[][] 场次座位图列表
	 */
	public static function getAllMovieSeatList($iRoommovieID, $iRoomID=0)
	{
		$aSeatList = array();
		if(empty($iRoommovieID))
		{
			echo "iRoommovieID is Empty\r\n";
			return $aSeatList;
		}
		
		if($iRoomID==0)
		{
			$arArrangeInfo = self::getRoommovieInfoByRoommovieID($iRoommovieID);
			if (empty($arArrangeInfo))
			{
				return $aSeatList;
			}
			$iRoomID = $arArrangeInfo['iRoomID'];
		}
	
		//获取影厅座位图状态
		$arRoomInfo = self::getRoomInfo($iRoomID, array('sSeatInfo'));
		if (empty($arRoomInfo['sSeatInfo']))
		{
			echo "Room-SeatInfo is Empty\r\n";
			return $aSeatList;
		}
		$arSeatInfo =json_decode($arRoomInfo['sSeatInfo'], true);
	
		foreach($arSeatInfo['seatinfo'] as $k => &$seatInfo)
		{
			$aSeatList[$k]['seatRow'] = $seatInfo['seatRow'];
			$aSeatList[$k]['seatNo'] = $seatInfo['SeatNo'];
			$aSeatList[$k]['seatState'] = $seatInfo['seatState'];
			$aSeatList[$k]['seatId'] = 0;
			if(count(explode(':',$aSeatList[$k]['seatNo']))==1){
				$aSeatList[$k]['seatId'] = $aSeatList[$k]['seatNo'];
				$aSeatList[$k]['seatNo'] = $seatInfo['seatRow'] .':'.$seatInfo['seatCol'];
			}
		}
		
		return $arSeatInfo;
	}
	
	/**
	 * 提取待锁座的座位信息
	 * 锁座类型：
	 * 		1、占座模式：锁定最佳座位
	 * 		2、刷票模式：随机找一排，锁定前N个座或后N个座（排号为单数：锁前座；排号为双数：锁后座【此处排号指的物理单数、双数，数组还是从0开始】）
	 * 		3、抢票模式：同‘刷票模式’
	 * 
	 * @remark seatType=0为普通座；seatType=1为情侣座的第一座；seatType=2为情侣座的第二座
	 * 
	 * @param array[][] $inSeatList 座位列表，座位行号从0开始
	 * @param int $lockModel 锁座模式
	 * @param int $lockCount 锁座数量
	 * @return array[][] 待锁座的座位列表 
	 */
	public static function getLockSeatList($inSeatList, $lockModel=1, $lockCount=2)
	{
		$lockSeatList = array();
		if(empty($inSeatList) or $lockCount==0)
		{
			return $lockSeatList;
		}
		
		$inSeatRowCount = count($inSeatList);
		
		if($inSeatRowCount==1)
		{
			//此处处理几乎满座的情形（只有一排还有余座）
			$lockSeatList = array_slice($inSeatList[0]['list'], 0, $lockCount);
		}else{
			switch($lockModel)
			{
				case 1:			//占座模式
				case 11:		//占座模式
				case 21:		//刷票模式（‘惊天解密’专用）
								
					$rowNo = floor($inSeatRowCount/2);											//最佳排号
					if(FALSE == empty($inSeatList[$rowNo]) and $inSeatList[$rowNo]['count']>0)
					{
						$colNo = floor($inSeatList[$rowNo]['count']/2) - floor($lockCount/2);	//最佳列号
						if($colNo<0)
						{
							$colNo = 0;
						}
						$lockSeatList = array_slice($inSeatList[$rowNo]['list'], $colNo, $lockCount);
					}
					break;
				case 12:		//占座模式（‘追龙’专用）
				case 13:		//占座模式（‘追龙’专用）
				case 23:		//刷票模式（‘追龙’专用）
								
					//取最佳座位，但是在一定范围内浮动，避免同一个厅取到相同的座位
					$rowNo = floor($inSeatRowCount/2);											//最佳排号
					$rowNo = rand($rowNo-1, $rowNo+1);
					if(FALSE == empty($inSeatList[$rowNo]) and $inSeatList[$rowNo]['count']>0)
					{
						$colNo = floor($inSeatList[$rowNo]['count']/2) - floor($lockCount/2);	//最佳列号
						$colNo = rand($colNo-1, $colNo+1);
						if($colNo<0)
						{
							$colNo = 0;
						}
						$lockSeatList = array_slice($inSeatList[$rowNo]['list'], $colNo, $lockCount);
					}
					break;								
				case 2:			//刷票模式
				case 22:		//刷票模式（‘惊天解密’专用）
				case 3:			//抢票模式
				case 4:			//自定义模式

					$rowNo = rand(0, $inSeatRowCount-1);
					if($lockCount < $inSeatList[$rowNo]['count'])
					{
						//选定排的座位数足够锁座
						$lockSeatList = $rowNo%2==0 ? array_slice($inSeatList[$rowNo]['list'], 0, $lockCount) : array_slice($inSeatList[$rowNo]['list'], -$lockCount, $lockCount);
					}else{
						//选定排的座位数不足以锁座，需要前后排补足
						//$lockSeatList = is_array($inSeatList[$rowNo]['list']) ? $inSeatList[$rowNo]['list'] : array();
						if($rowNo%2==0)
						{
							for($i=$rowNo; $i<$inSeatRowCount; $i++)
							{
								if(empty($inSeatList[$i]) or !is_array($inSeatList[$i]['list']))
								{
									continue;
								}
								$remainCount = $lockCount-count($lockSeatList);
								$lockSeatList = array_merge($lockSeatList, array_slice($inSeatList[$i]['list'], 0, $remainCount));
								if(count($lockSeatList)>=$lockCount)
								{
									break;
								}
							}
						}else{
							for($i=$rowNo; $i>=0; $i--)
							{
								if(empty($inSeatList[$i]) or !is_array($inSeatList[$i]['list']))
								{
									continue;
								}
								$remainCount = $lockCount-count($lockSeatList);
								$lockSeatList = array_merge($lockSeatList, array_slice($inSeatList[$i]['list'], -$remainCount, $remainCount));
								if(count($lockSeatList)>=$lockCount)
								{
									break;
								}
							}
						}//end if($rowNo%2==0)
					}
					break;
					
				case 201:		//刷票模式：只处理匹配了配置的排片（从后往前刷）
				case 24:
					
					//$rowNo = rand(0, $inSeatRowCount-1);
					$rowNo = $inSeatRowCount-1;
					if($lockCount < $inSeatList[$rowNo]['count'])
					{
						//选定排的座位数足够锁座
						$lockSeatList = array_slice($inSeatList[$rowNo]['list'], -$lockCount, $lockCount);
					}else{
						//选定排的座位数不足以锁座，需要前后排补足
						for($i=$rowNo; $i>=0; $i--)
						{
							if(empty($inSeatList[$i]) or !is_array($inSeatList[$i]['list']))
							{
								continue;
							}
							$remainCount = $lockCount-count($lockSeatList);
							$lockSeatList = array_merge($lockSeatList, array_slice($inSeatList[$i]['list'], -$remainCount, $remainCount));
							if(count($lockSeatList)>=$lockCount)
							{
								break;
							}
						}
					}
					break;
				case 110:
					//$rowNo = rand(0, $inSeatRowCount-1);
					$rowNo = $inSeatRowCount-1;
					if($lockCount < $inSeatList[$rowNo]['count'])
					{
						//选定排的座位数足够锁座
						$lockSeatList = array_slice($inSeatList[$rowNo]['list'], -$lockCount, $lockCount);
					}else{
						//选定排的座位数不足以锁座，需要前后排补足
						for($i=$rowNo; $i>=0; $i--)
						{
							if(empty($inSeatList[$i]) or !is_array($inSeatList[$i]['list']))
							{
								continue;
							}
							$remainCount = $lockCount-count($lockSeatList);
							$lockSeatList = array_merge($lockSeatList, array_slice($inSeatList[$i]['list'], -$remainCount, $remainCount));
							if(count($lockSeatList)>=$lockCount)
							{
								break;
							}
						}
					}
					break;
				default:
					break;
			}
		}
		
		$retArr = array();
		$seatIdFlag = 1;	//seatId为空标识
		
		if(FALSE == empty($lockSeatList))
		{
			foreach ($lockSeatList as $k=>$seatInfo)
			{
				if(empty($seatInfo['seatId']) or $seatInfo['seatId']=='0')
				{
					$seatIdFlag = 0;
				}
				$retArr['seatNo'] = $k==0 ? $seatInfo['seatNo'] : $retArr['seatNo'].'@@'.$seatInfo['seatNo'];
				$retArr['seatId'] = $k==0 ? $seatInfo['seatId'] : $retArr['seatId'].'@@'.$seatInfo['seatId'];
				$retArr['seatInfo'] = $k==0 ? str_replace(':', '排', $seatInfo['seatNo']) : $retArr['seatInfo'].','.str_replace(':', '排', $seatInfo['seatNo']);
			}
			$retArr['seatCount'] = count($lockSeatList);
		}
		if(0 == $seatIdFlag)
		{
			$retArr['seatId'] = '';
		}
		
		return $retArr;
	}

	/**
	 * 锁座操作
	 *
	 * @param string $iRoommovieID 场次ID
	 * @param array[] $seatList 待锁的座位列表
	 * @param array[] $uInfo 用户信息
	 * @return string 订单NO，异常则返回空
	 */
	public static function lockSeat_bak($iRoommovieID, $seatList, $uInfo)
	{
		if(empty($seatList) or empty($seatList['seatNo']))
		{
			echo "seatNo empty：".$iRoommovieID."\r\n";
			return '';
		}
		if(isset($seatList["seatId"]) && !empty($seatList["seatId"]))
		{
			$seatList['seatNo'] = $seatList['seatId'];
		}
	
		$roommovieInfo= self::getRoommovieInfoByRoommovieID($iRoommovieID);
		if (empty($roommovieInfo) or strtotime($roommovieInfo['dEndBuyDate'])<time())
		{
			echo "dEndBuyDate Err：".$iRoommovieID."\r\n";
			return '';
		}
	
		if(self::isLockSeat($iRoommovieID, $seatList['seatNo']))
		{
			echo "is Locked：".$iRoommovieID."\r\n";
			return '';
		}
	
		//本地订单创建
		$outerOrderId = BOrderProcess::createSeatOnlinOrder($uInfo['iUserID'], $iRoommovieID, $uInfo['sPhone'], $seatList['seatCount']
				, $seatList['seatNo'], $seatList['seatInfo'], 'epiaowang', '', ConfigParse::getOrdersKey('onlineSeatOrder'));
	
		if(FALSE == empty($outerOrderId))
		{
		//接口方锁座
			$interfRet = BInterfProcess::GetCreateOrderResult($outerOrderId);
					
				if($interfRet['ResultCode']==ConfigParse::getOrderStatusKey('ORDER_STATUS_Successed'))
			{
						$orderSeatInfo=array(ConfigParse::getOrderSeatInfoKey('userId')=>$uInfo['iUserID']
						, ConfigParse::getOrderSeatInfoKey('outerOrderId')=>$outerOrderId
						, ConfigParse::getOrderSeatInfoKey('sInterfaceOrderNo')=>$interfRet["InterfaceOrderNo"]);
						if(BOrderProcess::updateOrderSeatInfo($uInfo['iUserID'], $orderSeatInfo))
						{
						return $outerOrderId;
				}
				}
			else{
				echo "Interf Retu Err：".$iRoommovieID."\r\n";
		BOrderProcess::delOrderInfo($outerOrderId);
		}
		}
	
		return '';
	}
	
	/**
	 * 锁座操作
	 * 
	 * @param string $iRoommovieID 场次ID
	 * @param array[] $seatList 待锁的座位列表
	 * @param array[] $uInfo 用户信息
	 * @return string 订单NO，异常则返回空 
	 */
	public static function lockSeat($iRoommovieID, $seatList, $uInfo, $iSeatNum=0, $iSellNum=0, $iBrushNum=0, $lockModel=1)
	{
		if(empty($seatList) or empty($seatList['seatNo']))
		{
			echo "seatNo empty：".$iRoommovieID."\r\n";
			return '';
		}
		if(isset($seatList["seatId"]) && !empty($seatList["seatId"]))
		{
			$seatList['seatNo'] = $seatList['seatId'];
		}
	
		$roommovieInfo= self::getRoommovieInfoByRoommovieID($iRoommovieID);
		if (empty($roommovieInfo) or strtotime($roommovieInfo['dEndBuyDate'])<time())
		{
			echo "dEndBuyDate Err：".$iRoommovieID."\r\n";
			return '';
		}
	
		if(self::isLockSeat($iRoommovieID, $seatList['seatNo']))
		{
			echo "is Locked：".$iRoommovieID."\r\n";
			return '';
		}
		
		//处理用户待支付的其他订单（此处的逻辑不需要）
/* 		$orderList = self::getUserUnfinishOrderList($uInfo['iUserID']);
		if($orderList)
		{
			foreach($orderList as $v)
			{
				if (!empty($v['sInterfaceOrderNo']) && (900-(time()- strtotime($v["dCreateTime"])))>0 && $v['isWeChatapplet'] == 1)
				{
					$arResult = BInterfProcess::GetCancelOrderResult($v['outerOrderId']);
					if($arResult['ResultCode'] == ConfigParse::getOrderStatusKey('ORDER_STATUS_Successed')){
						if(!BOrderProcess::updateUserUnfinishOrder($v['outerOrderId']))
						{
							return '';
						}
					}else{
						return '';
					}
				}
			}
		}
 */		
		//本地订单创建
		$outerOrderId = BOrderProcess::createSeatOnlinOrder($uInfo['iUserID'], $iRoommovieID, $uInfo['sPhone'], $seatList['seatCount']
				, $seatList['seatNo'], $seatList['seatInfo'], 'epiaowang', '', ConfigParse::getOrdersKey('onlineSeatOrder'), $iSeatNum, $iSellNum, $iBrushNum, $lockModel);
		
		if(FALSE == empty($outerOrderId))
		{
			//接口方锁座
			$interfRet = BInterfProcess::GetCreateOrderResult($outerOrderId);
			
			if($interfRet['ResultCode']==ConfigParse::getOrderStatusKey('ORDER_STATUS_Successed'))
			{
				$orderSeatInfo=array(ConfigParse::getOrderSeatInfoKey('userId')=>$uInfo['iUserID']
						, ConfigParse::getOrderSeatInfoKey('outerOrderId')=>$outerOrderId
						, ConfigParse::getOrderSeatInfoKey('sInterfaceOrderNo')=>$interfRet["InterfaceOrderNo"]);
				if(BOrderProcess::updateOrderSeatInfo($uInfo['iUserID'], $orderSeatInfo))
				{
					return $outerOrderId;
				}
			}
			else{
				echo "Interf Retu Err：".$iRoommovieID."\r\n";
				BOrderProcess::delOrderInfo($outerOrderId);
			}
		}
		
		return '';
	}
	
	
	/**
	 * 锁座操作（用于注水）
	 *
	 * @param string $iRoommovieID 场次ID
	 * @param array[] $seatList 待锁的座位列表
	 * @param array[] $uInfo 用户信息
	 * @param int $lockModel 锁座类型
	 * @param int $iInterfaceID 上游接口方
	 * @return string 订单NO，异常则返回空
	 */
	public static function lockSeatForFake($iRoommovieID, $seatList, $uInfo, $lockModel=1, $iInterfaceID=8)
	{
		if(empty($seatList) or empty($seatList['seatNo']))
		{
			echo "seatNo empty：".$iRoommovieID."\r\n";
			return '';
		}
		if(isset($seatList["seatId"]) && !empty($seatList["seatId"]))
		{
			$seatList['seatNo'] = $seatList['seatId'];
		}
	
		$roommovieInfo= self::getRoommovieInfoByRoommovieID($iRoommovieID);
		if (empty($roommovieInfo) or strtotime($roommovieInfo['dEndBuyDate'])<time())
		{
			echo "dEndBuyDate Err：".$iRoommovieID."\r\n";
			return '';
		}
	
		if(self::isLockSeat($iRoommovieID, $seatList['seatNo']))
		{
			echo "is Locked：".$iRoommovieID."\r\n";
			return '';
		}
	
		//本地订单创建
		$outerOrderId = BOrderProcess::createSeatOnlinOrderForFake($uInfo['iUserID'], $iRoommovieID, $uInfo['sPhone'], $seatList['seatCount']
				, $seatList['seatNo'], $seatList['seatInfo'], 'epiaowang', '', ConfigParse::getOrdersKey('onlineSeatOrder'), $lockModel);
	
		if(FALSE == empty($outerOrderId))
		{
			//修改 order_seat
			$orderInterfaceInfo = self::createOrderInterfaceInfoForFake($iInterfaceID);
			$orderSeatInfo = array('iUserId' => $uInfo['iUserID']
					, 'outerOrderId' => $outerOrderId
					, 'sInterfaceOrderNo' => $orderInterfaceInfo['sInterfaceOrderNo']
					, 'sInterfaceValidCode' => $orderInterfaceInfo['sInterfaceValidCode']);
			
			BOrderProcess::updateOrderSeatInfo($uInfo['iUserID'], $orderSeatInfo);
			
			//修改 sell
			BOrderProcess::updateSellInfo($iRoommovieID, array('iBrushNum'=>count(explode('@@', $seatList['seatNo']))));
		}
		
		return $outerOrderId;
	}
	/**
	 * 创建订单接口信息（用于注水）
	 *
	 * @param int $iInterfaceID 接口ID
	 * @return array[] 返回信息
	 */
	
	public static function createOrderInterfaceInfoForFake($iInterfaceID=8)
	{
		$orderInterfaceInfo = array();
		switch($iInterfaceID)
		{
			case 8:		//网票
				$sInterfaceValidCode = rand(1,2)==1 ? '9'.self::getRandString(5).'|6'.self::getRandString(5) : date('Ymd').self::getRandString(8);
				$orderInterfaceInfo = array('sInterfaceOrderNo' => '0047'.self::getRandString(6)
						,'sInterfaceValidCode' => $sInterfaceValidCode);
				break;
			case 5:		//蜘蛛
				$orderInterfaceInfo = array('sInterfaceOrderNo' => '16'.self::getRandString(6)
						,'sInterfaceValidCode' => 'W'.strtoupper(self::getRandString(6,3)));
				break;
			default:
				$orderInterfaceInfo = array('sInterfaceOrderNo' => '0088465325', 'sInterfaceValidCode' => '539831602|393469');
				break;
		}
		
		return $orderInterfaceInfo;
	}
	
	/**
	 * 获取随机字符
	 * @param int $len 字符长度
	 * @param int $type 类型（1-仅数字；2-仅小写字母；3-数字+小写字母）
	 * @return string 验证码
	 */
	public static function getRandString($len=6, $type=1)
	{
		switch($type)
		{
			case 1:
				$chars = array(	'0', '1', '2', '3', '4', '5', '6', '7', '8', '9' );
				break;
			case 2:
				$chars = array(
						'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u'
						, 'v', 'w', 'x', 'y', 'z'
				);
				break;
			case 3:
				$chars = array(
						'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u'
						, 'v', 'w', 'x', 'y', 'z', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'
				);
				break;
		}
	
		$charsLen = count($chars) - 1;
		shuffle($chars);	//将数组打乱
	
		$output = '';
		for( $i=0; $i<$len; $i++ )
		{
			$output .= $chars[mt_rand(0,$charsLen)];
		}
	
		return $output;
	}
	/**
	 * 下单操作
	 *
	 * @param string $outerOrderId 订单NO
	 * @param int $seatCount 已锁座的座位数量
	 * @param array[] $uInfo 用户信息
	 * @return bool 操作结果
	 */
	
	public static function subPayOrder($outerOrderId, $seatCount, $uInfo)
	{
		$userOrderInfo = BOrderProcess::getOrderSeatInfoByOuterOrderId($outerOrderId);
	
		//判断该订单是否超过15分钟
		$userOrderInfo['createTime'] = 900-(time()- strtotime($userOrderInfo['createTime']));
		if ($userOrderInfo['createTime']<=0)
		{
			echo "Pay Err1: ".$outerOrderId."\r\n";
			return FALSE;
		}
	
		if ($userOrderInfo['orderStatus'] != ConfigParse::getPayStatusKey('orderNoPay'))
		{
			echo "Pay Err2: ".$outerOrderId."\r\n";
			return FALSE;
		}
	
		if (empty($userOrderInfo['sInterfaceOrderNo']))
		{
			echo "Pay Err3: ".$outerOrderId."\r\n";
			return FALSE;
		}
		
		//先删除该订单号下的所有待支付订单-仅是payLog表，再重新生成订单
		$payloginfo = BOrderProcess::getOrderPaylogList($outerOrderId);
		if(!empty($payloginfo))
		{
			echo "Pay Err4: ".$outerOrderId."\r\n";
			BOrderProcess::delOrderPaylog($outerOrderId);
		}
		
		//生成Paylog
		$paylogInfo = array('outerOrderId' => $outerOrderId, 'totalPrice' => $userOrderInfo['totalPrice']
				, 'bankType' => '400003', 'payTime' => date('Y-m-d h:i:s'), 'count' => $seatCount
				, 'status' => ConfigParse::getPayStatusKey('orderPay'), 'iUserID' => $uInfo['iUserID']
		);
		if(FALSE == BOrderProcess::insertOrderPaylog($paylogInfo))
		{
			echo "Pay Err5: ".$outerOrderId."\r\n";
			return FALSE;
		}
		
		//订单已支付（模拟‘余额’支付），修改订单状态
		$orderInfo = array('orderPayType' => '400003', 'sendPhone' => $uInfo['sPhone']
				, 'orderStatus' => ConfigParse::getPayStatusKey('orderPay'), 'outerOrderId' => $outerOrderId
		);
		if(FALSE == BOrderProcess::updateOrderInfo($orderInfo))
		{
			echo "Pay Err6: ".$outerOrderId."\r\n";
			return FALSE;
		}
	
		//接口方下单
		if(FALSE == BOrderProcess::applyTicket($outerOrderId, $uInfo))
		{
			echo "Pay Err7: ".$outerOrderId."\r\n";
			return FALSE;
		}

		return TRUE;
	}
	
	/**
	 * 获取场次信息（By 场次ID）
	 *
	 * @param string $iRoommovieID 场地id
	 * @param array[] $param 输入的字段列表
	 * @return array[] 场次信息
	 */
	public static function getRoommovieInfoByRoommovieID($iRoommovieID, $fields=array())
	{
		if(empty($iRoommovieID))
		{
			return array();
		}
	
		$cinemaDBObj = new B_CinemaDB();
		//过滤掉无效字段
		$fields = array_intersect($fields, $cinemaDBObj->attributeNames());
		
		$selStr = (is_array($fields) and !empty($fields)) ? '`'.implode('`,`', $fields).'`' : '*';
		$sql = sprintf("SELECT ".$selStr." FROM {{e_roommovie}} where iRoommovieID='%s'",$iRoommovieID);
		return DbUtil::queryRow($sql);
	}
	
	/**
	 * 验证待锁座位是否已经生成订单
	 *
	 * @param string $iRoommovieID 场地id
	 * @param string $seatNo 座位NO串
	 * @return bool
	 */
	public static function isLockSeat($iRoomMovieID, $seatNo)
	{
		$seatNo = '%'.$seatNo.'%';
		$sql = sprintf("SELECT b.orderId FROM {{fill_order_seat}} a,{{fill_orders}} b WHERE iRoomMovieID='%s' AND sSeatInfo LIKE '%s' AND a.outerOrderId=b.outerOrderId AND b.orderStatus = 10101", $iRoomMovieID, $seatNo);
		$orderInfo = DbUtil::queryRow($sql);
		
		return empty($orderInfo) ? 	FALSE : TRUE;
	}
	
	/**
	 * 获取用户未完成的订单列表
	 *
	 * @param int $iUserID 用户id
	 * @return array[][] 订单列表
	 */
	public static function getUserUnfinishOrderList($iUserID)
	{
		$orderList = array();
		if(empty($iUserID)){
			return $orderList;
		}
		$sql = sprintf("SELECT * FROM {{fill_order_seat}} WHERE iUserId=%s AND (status = 10101 OR status = 10102) ORDER BY dCreateTime DESC ", "'$iUserID'");
		$orderList = DbUtil::queryAll($sql);
		
		return $orderList;
	}
	
	/**
	 * 获取影厅信息（By 影厅id）
	 *
	 * @param int $iRoomID 影厅id
	 * @param array[] $fields 影厅id
	 * @return array[][] 订单列表
	 */
	public static function getRoomInfo($iRoomID, $fields=array())
	{
		if(empty($iRoomID))
		{
			return array();
		}
	
		$cinemaDBObj = new B_CinemaDB();
		//过滤掉无效字段
		$fields = array_intersect($fields, $cinemaDBObj->attributeNames());
		$selStr = (is_array($fields) and !empty($fields)) ? '`'.implode('`,`', $fields).'`' : '*';
		$sql = sprintf("SELECT ".$selStr." FROM {{b_room}} where iRoomID='%s'",$iRoomID);
		return DbUtil::queryRow($sql);
	}
	
	/**
	 * 随机获取用户信息（iUserID,sPhone）
	 *
	 * @param int $iRoomID 影厅id
	 * @param array[] $fields 影厅id
	 * @return array[][] 订单列表
	 */
	public static function getRandUInfo()
	{
		$sql = 'SELECT u1.iUserID,u1.sPhone FROM tb_e_userbaseinfo AS u1 
				JOIN (SELECT ROUND(RAND() * ((SELECT MAX(iUserID) FROM tb_e_userbaseinfo)-(SELECT MIN(iUserID) FROM tb_e_userbaseinfo))+(SELECT MIN(iUserID) FROM tb_e_userbaseinfo)) AS iUserID) AS u2 
				WHERE LENGTH(u1.sPhone)=11 AND u1.iUserID >= u2.iUserID ORDER BY u1.iUserID LIMIT 1';
		
		return DbUtil::queryRow($sql);
	}
	
	/**
	 * 获取待锁座的排片列表
	 *
	 * @param int $movieID 影片id
	 * @param int $lockModel 锁座模式
	 * @param int $limit 锁座排片的数量上限
	 * @return array[][] 排片列表
	 */
	public static function getRoommovieListToLock($movieID, $lockModel=1, $limit=1)
	{
		$rmList = array();
		switch($lockModel)
		{
			case 1:		//占座模式：只锁定新增的排片
 				$sql = sprintf("SELECT rm.*,r.iSeatNum FROM {{e_roommovie}} rm LEFT JOIN {{b_room}} r ON rm.iRoomID=r.iRoomID WHERE rm.iMovieID=%d AND rm.mPrice<=%d AND rm.iRoomMovieID NOT IN(SELECT iRoomMovieID FROM {{fill_sell}}) ORDER BY rm.dBeginTime DESC LIMIT %d", $movieID, self::LOCK_PRICE_LIMIT, $limit);
 				$rmList = DbUtil::queryAll($sql);
				break;
			case 11:	//占座模式（‘惊天解密’专用）
				$confSeatNum = 1;		//只处理**座以上的影厅
				$confPriceLimit = 38;	//票价不高于**
				$confSelCinemas = '248,249,1949,247,1964,2064,2071,2262,5516,1627,1415,3053,1634,199,2630,5521,2921,1811,2049,1007,1923,1514,2247,2167,2782,2781,584,1975,210,1837,2114,5559,1933,4698,4245,4317,3761,415,1857,1692,2590,2094,1632,1652,3613,4989,4946,2602,1762,1785,4639,2926,5796,5883,3923,5951,6047,4993,5588,5591';	//选定影院
				$confBeginTime = "((rm.dBeginTime>='2017-09-22 00:00:00' AND rm.dBeginTime<'2017-09-22 23:59:59') OR (rm.dBeginTime>='2017-09-23 00:00:00' AND rm.dBeginTime<'2017-09-23 23:59:59'))";	//周六日：14:00~21:00；周五：18:00~21:00
 				$sql = sprintf("SELECT rm.*,r.iSeatNum FROM {{e_roommovie}} rm LEFT JOIN {{b_room}} r ON rm.iRoomID=r.iRoomID WHERE rm.iMovieID=%d AND rm.mPrice<=%d AND r.iSeatNum>=%d AND %s AND iEpiaoCinemaID IN(%s) AND rm.iRoomMovieID NOT IN(SELECT iRoomMovieID FROM {{fill_sell}}) ORDER BY rm.dBeginTime DESC LIMIT %d", $movieID, $confPriceLimit, $confSeatNum, $confBeginTime, $confSelCinemas, $limit);
 				
 				$rmList = DbUtil::queryAll($sql);
				break;
			case 12:	//占座模式（‘追龙’专用）
				$confSeatNum = 1;		//只处理**座以上的影厅
				$confPriceLimit = 29;	//票价不高于**
				$confSelCinemas = '1923,5516,2049,1652,2926,3761,4317,5796,4639,6047,4946,5883,5951,4245,1007,251,249,248,250,247,1933,5489,6064,5355,1632,2105,2590,2114,4698,1692,2247,1972,584,1975,1837,2094,1964,1514,2071,2064,2262,2630';	//排除影院
				$confBeginTime = "(rm.dBeginTime>='2017-10-03 00:00:00' AND rm.dBeginTime<'2017-10-03 23:59:59')";			//放映时间
 				$confSpacingTime = date('Y-m-d H:i:s', strtotime("-1800 second"));	//新排期需沉淀**秒（防止一放出来就被刷）
 				//$sql = sprintf("SELECT rm.*,r.iSeatNum FROM {{e_roommovie}} rm LEFT JOIN {{b_room}} r ON rm.iRoomID=r.iRoomID WHERE rm.iMovieID=%d AND rm.mPrice<=%d AND r.iSeatNum>=%d AND %s AND iEpiaoCinemaID NOT IN(%s) AND rm.iRoomMovieID NOT IN(SELECT iRoomMovieID FROM {{fill_sell}}) ORDER BY rm.dBeginTime DESC LIMIT %d", $movieID, $confPriceLimit, $confSeatNum, $confBeginTime, $confSelCinemas, $limit);
				$sql = sprintf("SELECT rm.*,r.iSeatNum FROM {{e_roommovie}} rm LEFT JOIN {{b_room}} r ON rm.iRoomID=r.iRoomID WHERE rm.iMovieID=%d AND rm.iInterfaceID!=10 AND rm.mPrice<=%d AND rm.dCreateTime<='%s' AND r.iSeatNum>=%d AND %s AND iEpiaoCinemaID NOT IN(%s) AND rm.iRoomMovieID NOT IN(SELECT iRoomMovieID FROM {{fill_sell}}) ORDER BY rm.dBeginTime DESC LIMIT %d", $movieID, $confPriceLimit, $confSpacingTime, $confSeatNum, $confBeginTime, $confSelCinemas, $limit);
 				
 				$rmList = DbUtil::queryAll($sql);
				break;
			case 13:	//占座模式（‘追龙’专用）
				$confSeatNum = 1;		//只处理**座以上的影厅
				$confPriceLimit = 25;	//票价不高于**
				$confSelCinemas = '1923,5516,2049,1652,2926,3761,4317,5796,4639,6047,4946,5883,5951,4245,1007,251,249,248,250,247,1933,5489,6064,5355,1632,2105,2590,2114,4698,1692,2247,1972,584,1975,1837,2094,1964,1514,2071,2064,2262,2630';	//排除影院
				$confBeginTime = "(rm.dBeginTime>='2017-10-11 14:00:00' AND rm.dBeginTime<'2017-10-11 21:00:00')";
 				$confSpacingTime = date('Y-m-d H:i:s', strtotime("-1200 second"));	//新排期需沉淀**秒（防止一放出来就被刷）
				$sql = sprintf("SELECT rm.*,r.iSeatNum FROM {{e_roommovie}} rm LEFT JOIN {{b_room}} r ON rm.iRoomID=r.iRoomID WHERE rm.iMovieID=%d AND rm.iInterfaceID!=10 AND rm.mPrice<=%d AND rm.dCreateTime<='%s' AND r.iSeatNum>=%d AND %s AND iEpiaoCinemaID NOT IN(%s) AND rm.iRoomMovieID NOT IN(SELECT iRoomMovieID FROM {{fill_sell}}) ORDER BY rm.dBeginTime DESC LIMIT %d", $movieID, $confPriceLimit, $confSpacingTime, $confSeatNum, $confBeginTime, $confSelCinemas, $limit);
 				
 				$rmList = DbUtil::queryAll($sql);
				break;
			case 2:		//刷票模式：在已占座的排片中进行刷票（上座率低者优先）
				$sql = sprintf("SELECT iRoomMovieID FROM {{fill_sell}} WHERE iMovieID=%d AND iSellNum<iSeatNum AND TIMESTAMPDIFF(SECOND,NOW(),dEndBuyDate)>300 AND iRoomMovieID in(SELECT iRoomMovieID FROM {{e_roommovie}} WHERE iMovieID=%d) ORDER BY ROUND(iSellNum/iSeatNum,2) LIMIT %d", $movieID, $movieID, $limit);
				$rmList = DbUtil::queryAll($sql);
				break;
			case 21:	//刷票模式（同2，‘惊天解密’专用）
				$confBrushLimit = 5;		//单场次刷量上限
				$sql = sprintf("SELECT iRoomMovieID,iSeatNum,iSellNum,iBrushNum FROM {{fill_sell}} WHERE iMovieID=%d AND ROUND(iSellNum/iSeatNum,2)<0.1 AND iBrushNum<=%d AND TIMESTAMPDIFF(SECOND,NOW(),dEndBuyDate)>300 AND iRoomMovieID in(SELECT iRoomMovieID FROM {{e_roommovie}} WHERE iMovieID=%d) ORDER BY dBeginTime DESC LIMIT %d", $movieID, $confBrushLimit, $movieID, $limit);
				$rmList = DbUtil::queryAll($sql);
				break;
			case 22:	//刷票模式（同2，‘惊天解密’专用）
				$confBrushLimit = 7;		//单场次刷量上限
				$sql = sprintf("SELECT iRoomMovieID,iSeatNum,iSellNum,iBrushNum FROM {{fill_sell}} WHERE iMovieID=%d AND iBrushNum<%d AND iSellNum<%d AND TIMESTAMPDIFF(SECOND,NOW(),dEndBuyDate)>300 AND iRoomMovieID in(SELECT iRoomMovieID FROM {{e_roommovie}} WHERE iMovieID=%d) ORDER BY dBeginTime DESC LIMIT %d", $movieID, $confBrushLimit, $confBrushLimit, $movieID, $limit);
				$rmList = DbUtil::queryAll($sql);
				break;
			case 23:	//刷票模式（同2，‘追龙’专用，上座率<10%，产品语言：二刷）
				$confPriceLimit = 35;	//票价不高于**
				$confSellRateLimit = 0.1;	//上座率不高于**
				$confBeginTime = "(dBeginTime>='2017-09-30 00:00:00' AND dBeginTime<'2017-09-30 23:59:59')";
				//$sql = sprintf("SELECT iRoomMovieID,iSeatNum,iSellNum,iBrushNum FROM {{fill_sell}} WHERE iMovieID=%d AND iBrushNum=0 AND mPrice<=%d AND ROUND(iSellNum/iSeatNum,2)<%f AND iRoomMovieID IN(SELECT iRoomMovieID FROM {{e_roommovie}} WHERE iMovieID=%d) AND iRoomMovieID NOT IN(SELECT DISTINCT(iRoomMovieID) FROM {{fill_order_seat}} WHERE status=10101) ORDER BY dBeginTime DESC LIMIT %d", $movieID, $confPriceLimit, $confSellRateLimit, $movieID, $limit);
				//$sql = sprintf("SELECT iRoomMovieID,iSeatNum,iSellNum,iBrushNum FROM {{fill_sell}} WHERE iMovieID=%d AND iBrushNum=0 AND mPrice<=%d AND ROUND(iSellNum/iSeatNum,2)<%f AND iRoomMovieID IN(SELECT iRoomMovieID FROM {{e_roommovie}} WHERE iMovieID=%d AND iInterfaceID=8) AND iRoomMovieID NOT IN(SELECT DISTINCT(iRoomMovieID) FROM {{fill_order_seat}} WHERE status=10101) ORDER BY dBeginTime DESC LIMIT %d", $movieID, $confPriceLimit, $confSellRateLimit, $movieID, $limit);
				$sql = sprintf("SELECT iRoomMovieID,iSeatNum,iSellNum,iBrushNum FROM {{fill_sell}} WHERE iMovieID=%d AND iBrushNum=0 AND mPrice<=%d AND %s AND ROUND(iSellNum/iSeatNum,2)<%f AND iRoomMovieID IN(SELECT iRoomMovieID FROM {{e_roommovie}} WHERE iMovieID=%d AND iInterfaceID!=10) AND iRoomMovieID NOT IN(SELECT DISTINCT(iRoomMovieID) FROM {{fill_order_seat}} WHERE status=10101) ORDER BY dBeginTime LIMIT %d", $movieID, $confPriceLimit, $confBeginTime, $confSellRateLimit, $movieID, $limit);
				$rmList = DbUtil::queryAll($sql);
				break;
			case 24:	//刷票模式（同2，‘追龙’专用，产品语言：二刷）
				$confPriceLimit = 27;	//票价不高于**
				$confBeginTime = "(dBegintime>='2017-10-03 14:00:00' and dBegintime<'2017-10-03 23:59:59')";
				$sql = sprintf("SELECT iRoomMovieID,iSeatNum,iSellNum,iBrushNum FROM {{fill_sell}} WHERE iMovieID=%d AND iBrushNum=0 AND iSellNum<iSeatNum AND mPrice<=%d AND %s AND iRoomMovieID IN(SELECT iRoomMovieID FROM {{e_roommovie}} WHERE iMovieID=%d AND iInterfaceID!=10 AND mPrice<=%d) AND iRoomMovieID NOT IN(SELECT DISTINCT(iRoomMovieID) FROM {{fill_order_seat}} WHERE status=10101) ORDER BY dBeginTime DESC LIMIT %d", $movieID, $confPriceLimit, $confBeginTime, $movieID, $confPriceLimit, $limit);
				
				//$sql = "select iRoomMovieID,iSeatNum,iSellNum,iBrushNum from tb_fill_sell where imovieid=2238 and dBegintime>='2017-09-30 09:00:00' and dBegintime<'2017-09-30 16:00:00' and ibrushnum=0 and mprice<=35 and iroommovieid in(select iroommovieid from tb_e_roommovie where imovieid=2238 and mprice<=35) order by ";
				$rmList = DbUtil::queryAll($sql);
				break;
			case 3:		//抢票模式：在即将停止售卖的排片中进行刷票（只处理5小时内停止售卖的排片）
				$sql = sprintf("SELECT iRoomMovieID FROM {{fill_sell}} WHERE iMovieID=%d AND iSellNum<iSeatNum AND TIMESTAMPDIFF(SECOND,NOW(),dEndBuyDate)>0 AND TIMESTAMPDIFF(SECOND,NOW(),dEndBuyDate)<%d AND iRoomMovieID in(SELECT iRoomMovieID FROM {{e_roommovie}} WHERE iMovieID=%d) ORDER BY dEndBuyDate LIMIT %d", $movieID, self::LOCK_ENDBUY_COUNTDOWN, $movieID, $limit);
				$rmList = DbUtil::queryAll($sql);
				break;
			case 4:		//自定义模式：根据临时需求，在特定排片、特定影院、特定区域内进行刷票
				
				//第一种：特定排片
				$inRMList = array('10411715080');
				$sql = sprintf("SELECT iRoomMovieID FROM {{fill_sell}} WHERE iMovieID=%d AND iRoomMovieID in('%s') AND iRoomMovieID in(SELECT iRoomMovieID FROM {{e_roommovie}} WHERE iMovieID=%d) ORDER BY ROUND(iSellNum/iSeatNum,2) LIMIT %d", $movieID, implode("','", $inRMList), $movieID, $limit);
				
				//第二种：特定影院
// 				$inCinemaList = array(2712, 5867, 5908);
// 				$sql = sprintf("SELECT iRoomMovieID FROM {{fill_sell}} WHERE iMovieID=%d AND iCinemaID in(%s) AND iRoomMovieID in(SELECT iRoomMovieID FROM {{e_roommovie}} WHERE iMovieID=%d) ORDER BY ROUND(iSellNum/iSeatNum,2) LIMIT %d", $movieID, implode(',', $inCinemaList), $movieID, $limit);

 				//第三种：特定区域（城市）
// 				$inCityList = array(1, 2, 3);
// 				$sql = sprintf("SELECT iRoomMovieID FROM {{fill_sell}} WHERE iMovieID=%d AND iCinemaID in(SELECT iCinemaID FROM {{b_cinema}} WHERE iCityID in(%s)) AND iRoomMovieID in(SELECT iRoomMovieID FROM {{e_roommovie}} WHERE iMovieID=%d) ORDER BY ROUND(iSellNum/iSeatNum,2) LIMIT %d", $movieID, implode(',', $inCityList), $movieID, $limit);
				
				$rmList = DbUtil::queryAll($sql);
				break;
			case 201:	//刷票模式：只处理匹配了配置的排片（此处属于定制类模式，不能作为通用操作）（‘S的秘密’专用）
				$sql = sprintf("SELECT iRoomMovieID,iBrushLimitNum,iBrushNum FROM {{fill_sell}} WHERE iMovieID=%d AND mPrice<=%d AND iBrushNum<iBrushLimitNum AND sConfigNo IN(SELECT sNo FROM {{fill_config}} WHERE iStatus=2) AND iRoomMovieID IN(SELECT iRoomMovieID FROM {{e_roommovie}} WHERE iMovieID=%d) ORDER BY dBeginTime DESC LIMIT %d", $movieID, self::LOCK_PRICE_LIMIT, $movieID, $limit);
				$rmList = DbUtil::queryAll($sql);
				break;
			default:
				break;
		}
		return $rmList;
	}
	
	/**
	 * 添加排片（用于售卖统计）
	 *
	 * @param array[] $rmInfo 排片信息
	 * @return bool
	 */
	public static function addRoommovieToSell($rmInfo)
	{
		$fillSellDBObj = new Fill_SellDB();
		$condition = 'iRoomMovieID=:iRoomMovieID';
		$params = array(':iRoomMovieID'=>$rmInfo['iRoomMovieID']);
		
		if($fillSellDBObj->getCountByCondition($condition, $params) > 0)
		{
			return TRUE;
		}
		
		$rmInfo['iCinemaID'] = $rmInfo['iEpiaoCinemaID'];
		//过滤无效字段（将数据表中未定义的字段去除）
		$rmInfo = self::filterInputFields($rmInfo,Fill_SellDB::model()->attributes);
		try
		{
			reset($rmInfo);
			for($i=0; $i<count($rmInfo); $i++)
			{
				$cField = current($rmInfo);
				$key = key($rmInfo);
				$fillSellDBObj->$key = $cField;
				next($rmInfo);
			}
		
			if($fillSellDBObj->validate() and $fillSellDBObj->save())
			{
				return TRUE;
			}
		}
		catch(Exception $e)
		{
		}
		return FALSE;
	}
	
	/**
	 * 解析座位行号（将A/B/C或a/b/c排列的，转化为阿拉伯数字）
	 *
	 * @param string $seatRow 座位行号，一个字母
	 * @return int	座位编号（数字）
	 */
	public static function parseSeatRow($seatRow)
	{
		if(is_numeric($seatRow))
		{
			return $seatRow;
		}

		$chars = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
		
		$seatRow = strtolower($seatRow);
		$index = array_search($seatRow[0], $chars);
		$ret = ($index+1)*pow(26, 0);
			
		return $ret;
	}
	
	
	/**
	 * 添加排片配置
	 *
	 * @param array[] $confInfo 排片配置信息
	 * @return bool
	 */
	public static function addConfigToSell($confInfo)
	{
		$fillConfDBObj = new Fill_ConfDB();
		$condition = 'sNo=:sNo';
		$params = array(':sNo'=>$confInfo['sNo']);
	
		if($fillConfDBObj->getCountByCondition($condition, $params) > 0)
		{
			return TRUE;
		}
	
		$confInfo['dCreateTime'] = GeneralFunc::getCurTime();
		//过滤无效字段（将数据表中未定义的字段去除）
		$confInfo = self::filterInputFields($confInfo,Fill_ConfDB::model()->attributes);
		try
		{
			reset($confInfo);
			for($i=0; $i<count($confInfo); $i++)
			{
				$cField = current($confInfo);
				$key = key($confInfo);
				$fillConfDBObj->$key = $cField;
				next($confInfo);
			}
	
			if($fillConfDBObj->validate() and $fillConfDBObj->save())
			{
				return TRUE;
			}
		}
		catch(Exception $e)
		{
		}
		return FALSE;
	}
	
	/**
	 * 获取待锁座的排片列表
	 *
	 * @param int $status 配置状态
	 * @return array[][] 配置列表
	 */
	public static function getConfListByStatus($status=1)
	{
		$sql = sprintf("SELECT * FROM {{fill_config}} WHERE iStatus=1 ORDER BY sDate");
		$confList = DbUtil::queryAll($sql);
		
		return $confList;
	}
	
	/**
	 * 获取与排片相匹配的配置
	 *
	 * @param array[] $rmInfo 新增排片信息
	 * @return array[] 配置信息
	 */
	public static function getConfListToMatchWithRM($rmInfo)
	{
// 		$dBeginTime = $rmInfo['dBeginTime'];
// 		$tempArr = explode(' ', $dBeginTime);
// 		$dBeginDate = $tempArr[0];
		
		$sql = sprintf("SELECT * FROM {{fill_config}} WHERE iStatus=1 AND iMovieID=%d AND iCinemaID=%d AND dStartTime<='%s' AND dEndTime>='%s' ORDER BY cid LIMIT 1", $rmInfo['iMovieID'], $rmInfo['iEpiaoCinemaID'], $rmInfo['dBeginTime'], $rmInfo['dBeginTime']);
		$confInfo = DbUtil::queryRow($sql);
		
		return $confInfo;
	}
	
	/**
	 * 修改配置信息（By cid）
	 *
	 * @param string $sNo 编号
	 * @param array[] $updateConfInfo 待修改的配置信息
	 * @return bool
	 */
	public static function updateConfigInfo($sNo, $updateConfInfo)
	{
		$fillConfDBObj = new Fill_ConfDB();
	
		//过滤掉无效字段
		$updateConfInfo = self::filterInputFields($updateConfInfo, $fillConfDBObj->model()->attributes);
	
		if(empty($updateConfInfo))
		{
			return FALSE;
		}
	
		$condition = 'sNo=:sNo';
		$params = array(':sNo'=>$sNo);
		$fillConfDBObj->updateByCondition($updateConfInfo, $condition, $params);
	
		return TRUE;
	}
	
	/**
	 * 获取排片已锁座的数量（含已售出）
	 *
	 * @param string $iRoomMovieID 排片id
	 * @return int 锁座数量
	 */
	public static function getLockedSumByRM($iRoomMovieID)
	{
		$sql = sprintf("SELECT IFNULL(SUM(iownSeats),0) AS count FROM {{fill_orders}} WHERE orderStatus in(10101,10102,10103,10104,10105) AND outerOrderId IN(SELECT outerOrderId FROM {{fill_order_seat}} WHERE iRoomMovieID='%s')", $iRoomMovieID);
		$rmInfo = DbUtil::queryRow($sql);
		
		return empty($rmInfo['count']) ? 0 : $rmInfo['count'];
	}
	
	/**
	 * 获取已经刷完的场次信息（状态为2）
	 *
	 * @return array[][] 场次列表
	 */
	public static function getConfigListToSellOut()
	{
		//$sql = sprintf("SELECT sConfigNo FROM {{fill_sell}} WHERE (iSellNum>=iSeatNum or iBrushNum>=iBrushLimitNum) AND sConfigNo IN(SELECT sNo FROM {{fill_config}} WHERE iStatus=2)");
		$sql = sprintf("SELECT sConfigNo FROM {{fill_sell}} WHERE iBrushNum>=iBrushLimitNum AND sConfigNo IN(SELECT sNo FROM {{fill_config}} WHERE iStatus=2)");
		$confList = DbUtil::queryAll($sql);
	
		return $confList;
	}
	
	/**
	 * 获取配置列表（By 状态）
	 *
	 * @param int $status 状态值
	 * @return array[][] 配置列表
	 */
	public static function getConfigListByStatus($status=3)
	{
		$sql = sprintf("SELECT * FROM {{fill_config}} WHERE iStatus=%d AND iOutputFlag=0", $status);
		$confList = DbUtil::queryAll($sql);
	
		return $confList;
	}

	/**
	 * 获取导出的影院列表（‘惊天解密’专用）
	 *
	 * @param string $form_id 导出批次（此字段是借用）
	 * @return array[][] 影院列表
	 */
	public static function getOutputCinemaList($form_id='1')
	{
		$sql = sprintf("SELECT s.iCinemaID,s.sCinemaName,c.sCityName,COUNT(s.order_seatId) FROM tb_fill_order_seat s,tb_b_cinema b,tb_b_city c WHERE s.form_id='%s' AND s.iCinemaID=b.iCinemaID and b.iCityID=c.iCityID GROUP BY 1", $form_id);
		$cinemaList = DbUtil::queryAll($sql);
	
		return $cinemaList;
	}
	
	/**
	 * 获取售卖列表（By 配置No）
	 *
	 * @param string $sNo 配置No
	 * @return array[][] 配置列表
	 */
	public static function getSellListByConfigNo($sNo)
	{
		$sql = sprintf("SELECT * FROM {{fill_sell}} WHERE sConfigNo='%s'", $sNo);
		$sellList = DbUtil::queryAll($sql);
	
		return $sellList;
	}
	
	/**
	 * 获取订单列表（By 影院id）
	 *
	 * @param int $iCinemaID 影院id
	 * @param int $iMovieID 影片id
	 * @param int $formID 导出标识
	 * @return array[][] 订单列表
	 */
	public static function getOrderListByCinemaID($iCinemaID, $iMovieID, $formID=1)
	{
		$sql = sprintf("SELECT os.sRoomName,os.dPlayTime,os.sInterfaceValidCode,o.orderInfo,o.iFakeNum,o.iownSeats FROM {{fill_order_seat}} os,{{fill_orders}} o WHERE os.iCinemaID=%d AND os.iMovieID=%d AND os.form_id=%d AND os.sInterfaceValidCode!='' AND (os.status=10104 OR os.status=10105) AND os.outerOrderId=o.outerOrderId", $iCinemaID, $iMovieID, $formID);
		$sellList = DbUtil::queryAll($sql);
	
		return $sellList;
	}
	
	
	/**
	 * 获取取票码串
	 *
	 * @param string $sNo 配置No
	 * @return array[][] 配置列表
	 */
	public static function getFetchNosByRM($iRoomMovieID)
	{
		DbUtil::execute("SET GLOBAL group_concat_max_len=10240");	//GROUP_CONCAT的默认上限是1024个字符，此处需要特别处理
		$sql = sprintf("SELECT GROUP_CONCAT(os.sInterfaceValidCode SEPARATOR ',') AS sInterfaceValidCode,GROUP_CONCAT(o.orderInfo SEPARATOR ',') AS sSeatInfo FROM {{fill_order_seat}} os,{{fill_orders}} o WHERE os.iRoomMovieID='%s' AND os.sInterfaceValidCode!='' AND (os.status=10104 OR os.status=10105)AND os.outerOrderId=o.outerOrderId", $iRoomMovieID);
		$sellList = DbUtil::queryRow($sql);
	
		return $sellList;
	}
	
	/**
	 * 判断影院是否为一线城市
	 *
	 * @param int $iCinemaID 影院id
	 * @return bool
	 */
	public static function isFirstTierCity($iCinemaID)
	{
		$sql = sprintf("SELECT iCityID FROM {{b_cinema}} WHERE iCinemaID=%d", $iCinemaID);
		$cinemaInfo = DbUtil::queryRow($sql);
		
		return in_array($cinemaInfo['iCityID'], array(2,1,9,23,3,17,19,10,6,85,82,12,84,4,16)) ? TRUE : FALSE;
	}
	
	/**
	 * 写日志/文件
	 *
	 * @param string $fName 文件名
	 * @param string $message 写入内容
	 * @return int	座位编号（数字）
	 */
	public static function writeFile($fName,$msg)
	{
		try{
			$msg = iconv('UTF-8', 'GBK', $msg);
		}
		catch(Exception $e)
		{
		}
		
		/*日志文件*/
		$strlog = $msg."\r\n";
		$f = fopen($fName, 'a');
		fwrite($f, $strlog);
		fclose($f);
	
		unset($strlog);
		return TRUE;
	}
	
	/**
	 * 数组数据trim
	 *
	 * @param array[] $input 输入数组
	 * @return array[] 输出数组
	 */
	public static function trimArray($input)
	{
		if (!is_array($input))
		{
			return trim($input);
		}
		return array_map('self::trimArray', $input);
	}
	
	/**
	 * 获取订单基本信息（By 订单编号）
	 *
	 * @param string $outerOrderId 订单编号
	 * @return array[] 订单基本信息
	 */
	public static function getSellInfoByRMId($roommovieID)
	{
		$sql = sprintf("SELECT * FROM {{fill_sell}} WHERE iRoomMovieID='%s' ",$roommovieID);
		return DbUtil::queryRow($sql);
	}
}