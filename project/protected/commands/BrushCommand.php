<?php

/**
 * BrushCommand - 刷票操作
 * @author luzhizhong
 * @version V1.0
 * 
 * 执行命令（锁座）：/alidata/server/php/bin/php /data/epw/PHPProject/server/project/protected/yiic.php Brush Lock Arg1 Arg2
 * 参数说明：
 * 			Brush：Command
 * 			Lock：Action
 * 			Arg1：参数1；$args[0]
 * 			Arg2：参数2；$args[1]
 */

Yii::import('application.models.data.db.brush.*');
Yii::import('application.models.process.brush.*');

class BrushCommand extends CConsoleCommand
{
/*     
	public function run($args)
    {
    }
 */

	/**
	 * 导入刷票配置
	 *
	 * @param array[] $args 输入参数
	 * 			$args[0]：影片ID（E票网ID）
	 */
	public function actionLoadConfig($args)
	{
		$movieID = $args[0];
		$inFileName = Yii::app()->basePath.'/commands/config.txt';
		if(file_exists($inFileName))
		{
			//读取配置信息
			$configList = array();
			$fp = fopen($inFileName, "r");
			if($fp)
			{
				while(!feof($fp))
				{
					$tempArr = BaseProcess::trimArray(explode('	', fgets($fp, 2048)));
					$configList[] = $tempArr;
				}
			}
			fclose($fp);
			
			//录入配置信息
			foreach ($configList as $configInfo)
			{
				//数据验证
				if(empty($configInfo[0]) or empty($configInfo[1]) or empty($configInfo[2]) or empty($configInfo[3]))
				{
					continue;
				}
				
				$inConfArr = array('sNo'=>$configInfo[0], 'iCinemaID'=>$configInfo[1], 'dStartTime'=>$configInfo[2], 'dEndTime'=>$configInfo[3]
						, 'iBuySum'=>$configInfo[4], 'iRemainSum'=>$configInfo[4], 'iMovieID'=>$movieID);
				BaseProcess::addConfigToSell($inConfArr);
			}
		}
	}
	
	/**
	 * 执行锁座操作
	 *
	 * @param array[] $args 输入参数
	 * 			$args[0]：影片ID（E票网ID）
	 * 			$args[1]：锁座模式
	 * 			$args[2]：锁座数量
	 */
    public function actionLock($args)
    {
    	
    	exit(0);
    	
    	$movieID = $args[0];
    	$lockModel = $args[1];
    	$lockCount = $args[2];
    	
    	echo GeneralFunc::getCurTime()."：start\r\n";
    	
    	$rmList = BaseProcess::getRoommovieListToLock($movieID, $lockModel);
    	echo GeneralFunc::getCurTime()."：count=".count($rmList)."\r\n";
    	foreach($rmList as $k => $rmInfo)
    	{
    		echo "iRoomMovieID：".$rmInfo['iRoomMovieID']."\r\n";
    		
    		switch($lockModel)
    		{
    			case 1:			//占座模式，记录排片
    			case 11:
    			case 12:		//‘追龙’专用
    			case 13:		//‘追龙’专用
    				$rmInfo['dCreateTime'] = GeneralFunc::getCurTime();
    				BaseProcess::addRoommovieToSell($rmInfo);
    				
    				$lockCount = rand(2,4);
    				if($lockCount==2)
    				{
    					$lockCount = rand(2,4);
    				}
// 					if(BaseProcess::isFirstTierCity($rmInfo['iEpiaoCinemaID']))
// 					{
// 						$lockCount = rand(3,4);
// 					}else{
// 						$lockCount = rand(2,4);
// 					}
					
    				break;
    			case 21:		//刷票模式：‘惊天解密’专用
    			case 23:		//刷票模式：‘追龙’专用
    			case 24:		//刷票模式：‘追龙’专用
    				$lockCount = rand(2,4);
    				break;
    			case 22:		//刷票模式：‘惊天解密’专用
    				$lockCount = rand(2,4);
    				$iBrushNum = empty($rmInfo['iBrushNum']) ? 6 : $rmInfo['iBrushNum'];
    				if((6-$iBrushNum)<$lockCount)
    				{
    					$lockCount = 6-$iBrushNum;
    				}
    				
    				//echo "lockCount：".$lockCount."\r\n";
    				break;
    			case 201:		//刷票模式：只处理匹配了配置的排片，需要控制刷票总量（‘S的秘密’专用）
    				$lockedSum = BaseProcess::getLockedSumByRM($rmInfo['iRoomMovieID']);
    				$remainLockSum = $rmInfo['iBrushLimitNum']-$lockedSum;
    				if($remainLockSum<=0)
    				{
    					$lockCount = 0;
    				}else{
    					$lockCount = $lockCount<=$remainLockSum ? $lockCount : $remainLockSum;
    				}
    				
    				break;
    		}
    		
	    	//获取排期座位图
	    	$seatList = BaseProcess::getMovieSeatList($rmInfo['iRoomMovieID']);
	    	
	    	if(FALSE == empty($seatList))
	    	{
	    		//获取待锁座座位信息
	    		$lockSeatList = BaseProcess::getLockSeatList($seatList, $lockModel, $lockCount);
	    		
	    		if(FALSE == empty($lockSeatList))
	    		{
	    			$uInfo = BaseProcess::getRandUInfo();	//随机用户 
	    			
	    			//临时处理
	    			if($uInfo['sPhone']=='18601338828')
	    			{
	    				$uInfo = BaseProcess::getRandUInfo();	//随机用户
	    			}
	    			//临时处理 结束
					
	    			$iSeatNum = empty($rmInfo['iSeatNum']) ? 0 : $rmInfo['iSeatNum'];
	    			$iBrushNum = empty($rmInfo['iBrushNum']) ? 0 : $rmInfo['iBrushNum'];
	    			
	    			$tempInfo = BaseProcess::getSellInfoByRMId($rmInfo['iRoomMovieID']);
	    			$iSellNum = empty($tempInfo) ? 0 : $tempInfo['iSellNum'];
	    			
	    			//‘追龙’专用
	    			if($lockModel==12 and $movieID==2238 and $iSellNum>0)
	    			{
	    				continue;
	    			}
	    			if($lockModel==23 and $movieID==2238 and ROUND($iSellNum/$iSeatNum,2)>=0.1)
	    			{
	    				continue;
	    			}
	    			if($lockModel==24 and $movieID==2238 and ROUND($iSellNum/$iSeatNum,2)>=0.08)
	    			{
	    				continue;
	    			}
	    			//‘追龙’专用结束
	    			
	    			//锁座
	    			//$outerOrderId = BaseProcess::lockSeat($rmInfo['iRoomMovieID'], $lockSeatList, $uInfo);
	    			$outerOrderId = BaseProcess::lockSeat($rmInfo['iRoomMovieID'], $lockSeatList, $uInfo, $iSeatNum, $iSellNum, $iBrushNum, $lockModel);
	    		}
	    	}
	    	
    	}
    	echo GeneralFunc::getCurTime()."：end\r\n";
    }
        
    /**
     * 锁座超时处理（释放座位）
     *
     * @param array[] $args 输入参数
	 * 			$args[0]：过期时长（秒，默认900）
     */
    public function actionUnLock($args)
    {
    	$overTime = $args[0];
    	if(empty($overTime) or FALSE == is_numeric($overTime))
    	{
    		$overTime = 900;
    	}
    	
    	//N秒后自动置为已过期；此操作会向接口方提交‘座位解锁’操作
    	$nopayOrderList = BOrderProcess::getNopayOrderList($overTime);
    	foreach($nopayOrderList as $nopayOrderInfo)
    	{
    		//修改orders表
    		$sql = sprintf("UPDATE tb_fill_orders SET orderStatus=10208,closeTime=NOW(),orderInfo=CONCAT(orderInfo,'订单已经过期') WHERE outerOrderId='%s'", $nopayOrderInfo['outerOrderId']);
    		DbUtil::execute($sql);
    	
    		//修改order_seat表
    		$sql = sprintf("UPDATE tb_fill_order_seat SET status=10208 WHERE outerOrderId='%s'", $nopayOrderInfo['outerOrderId']);
    		DbUtil::execute($sql);
    	
    		//释放座位（To 接口方）
			BInterfProcess::GetCancelOrderResult($nopayOrderInfo);
    	}
    }
    
    /**
     * 获取取票码
     *
     * @param array[] $args 输入参数
	 * 			$args[0]：过期时长（秒，默认900）
     */
    public function actionApplyTicket($args)
    {
        $overTime = $args[0];
    	if(empty($overTime) or FALSE == is_numeric($overTime))
    	{
    		$overTime = 900;
    	}
    	
    	//异步订单列表
    	$asyOrderList = BOrderProcess::getAsyOrderList();
    	foreach($asyOrderList as $asyOrderInfo)
    	{
    		$outerOrderId = $asyOrderInfo['outerOrderId'];
    		
    		//echo $outerOrderId."\r\n";
    		//从接口方获取取票码
    		$arRet = BInterfProcess::GetApplyTicket($asyOrderInfo);
    		
    		if($arRet['OrderStatus']==7 && !empty($arRet['FetchNo']))
    		{
    			echo GeneralFunc::getCurTime()." ".$outerOrderId."： Get FetchNo OK \r\n";
    			//获取到取票码，修改订单状态、补充取票码
    			$updateInfo = array('outerOrderId'=>$outerOrderId, 'orderStatus'=>ConfigParse::getPayStatusKey('orderAsynSeatSucess'));
    			BOrderProcess::updateOrderInfo($updateInfo);
    			
    			$updateInfo = array('outerOrderId'=>$outerOrderId, 'iUserId'=>$asyOrderInfo['iUserID'], 'status'=>ConfigParse::getPayStatusKey('orderAsynSeatSucess'), 'sInterfaceValidCode'=>$arRet['FetchNo']);
    			BOrderProcess::updateOrderSeatInfo($asyOrderInfo['iUserID'], $updateInfo);
    			
    			//刷量统计
    			BOrderProcess::addBrushSeatNum($asyOrderInfo['iRoomMovieID'], $asyOrderInfo['iownSeats']);
    			
    		}else{
    			
    			echo GeneralFunc::getCurTime()." ".$outerOrderId."： Get FetchNo Empty \r\n";
    			//未获取到取票码，如果超时，则置为‘订单失败’
    			$createTime = $asyOrderInfo['createTime'];
    			$gapTime = abs(time()-strtotime($createTime));
    			
    			if ($gapTime > $overTime)
    			{
    				echo GeneralFunc::getCurTime()." ".$outerOrderId."： Get FetchNo Timeout \r\n";
    				$updateInfo = array('outerOrderId'=>$outerOrderId, 'orderStatus'=>ConfigParse::getPayStatusKey('orderAsynSeatFail'));
    				BOrderProcess::updateOrderInfo($updateInfo);
    	
    				$updateInfo = array('outerOrderId'=>$outerOrderId, 'iUserId'=>$asyOrderInfo['iUserID'], 'status'=>ConfigParse::getPayStatusKey('orderAsynSeatFail'));
    				BOrderProcess::updateOrderSeatInfo($asyOrderInfo['iUserID'], $updateInfo);
    			}
    		}//end if($arRet['OrderStatus']==7 && !empty($arRet['FetchNo']))
    	}//end foreach
    }
    
    /**
     * 执行购票操作
     *
     * @param array[] $args 输入参数
	 * 			$args[0]：limit数量（默认900）
     */
    public function actionPay($args)
    {
    	$limit = $args[0];
    	if(empty($limit) or FALSE == is_numeric($limit))
    	{
    		$limit = 100;
    	}
    	
    	//待支付订单列表
    	$topayOrderList = BOrderProcess::getTopayOrderList($limit);
    	foreach($topayOrderList as $topayOrderInfo)
    	{
    		$uInfo = array('iUserID'=>$topayOrderInfo['iUserId'], 'sPhone'=>$topayOrderInfo['sendPhone']);
    		
    		echo GeneralFunc::getCurTime()." Pay Start: ".$topayOrderInfo['outerOrderId']."\r\n";
    		//下单
    		if(FALSE == BaseProcess::subPayOrder($topayOrderInfo['outerOrderId'], $topayOrderInfo['iownSeats'], $uInfo))
    		{
    			//echo "Pay Err: ".$topayOrderInfo['outerOrderId']."\r\n";
    		}
    	}
    }
    
    /**
     * 解析配置，安排相匹配的排片
     *
     * @param array[] $args 输入参数
     * 			$args[0]：影片ID（E票网ID）
     */
    public function actionParseConfig($args)
    {
    	$movieID = $args[0];
    	
    	//获取新增的排片
    	$rmList = BaseProcess::getRoommovieListToLock($movieID);
    	foreach($rmList as $rmInfo)
    	{
    		$confInfo = BaseProcess::getConfListToMatchWithRM($rmInfo);
    		if(FALSE == empty($confInfo))
    		{
    			//记录排片信息（同时加入了配置的匹配关系）
    			$rmInfo['sConfigNo'] = $confInfo['sNo'];
    			//$rmInfo['iBrushLimitNum'] = $rmInfo['iSeatNum']<=$confInfo['iRemainSum'] ? $rmInfo['iSeatNum'] : $confInfo['iRemainSum'];
    			$rmInfo['iBrushLimitNum'] = $confInfo['iRemainSum'];
    			$rmInfo['dCreateTime'] = GeneralFunc::getCurTime();
    			BaseProcess::addRoommovieToSell($rmInfo);
    			
    			//检查配置的剩余匹配关系
//     			if($rmInfo['iBrushLimitNum']>=$confInfo['iRemainSum'])
//     			{
//     				//配置中的票量已分配完毕
//     				BaseProcess::updateConfigInfo($confInfo['cid'], array('iRemainSum'=>0, 'iStatus'=>2));
//     			}else{
//     				//配置中的票量分配了一部分，还需要下一个排片消化
//     				BaseProcess::updateConfigInfo($confInfo['cid'], array('iRemainSum'=>$confInfo['iRemainSum']-$rmInfo['iBrushLimitNum']));
//     			}
    			BaseProcess::updateConfigInfo($confInfo['sNo'], array('iStatus'=>2));
    			
    		}else{
    			//只记录排片信息，便于以后刷自然排片
    			$rmInfo['dCreateTime'] = GeneralFunc::getCurTime();
    			BaseProcess::addRoommovieToSell($rmInfo);
    		}
    	}
    }
    
    /**
     * 售卖/刷票监控
     *
     * @param array[] $args 输入参数
     */
    public function actionSellMonitor($args)
    {
    	$confList = BaseProcess::getConfigListToSellOut();
    	foreach($confList as $confInfo)
    	{
    		$sNo = $confInfo['sConfigNo'];
    		BaseProcess::updateConfigInfo($sNo, array('iStatus'=>3));
    	}
    }
    
    /**
     * 输出刷量数据
     *
     * @param array[] $args 输入参数
     * 			$args[0]：影片ID（E票网ID）
     */
    public function actionOutputPackage($args)
    {
    	$movieID = $args[0];
    	$fPath = Yii::app()->basePath.'/commands/package/';
    	
    	$fIndexName = sprintf("index.xls");
    	$msg = '取票码编号	影院id	开始时间	结束时间	影厅总座位数	刷票需求	出票数	平均价	场次数据';
    	BaseProcess::writeFile($fIndexName, $msg);

    	$confList = BaseProcess::getConfigListByStatus($status=3);
    	foreach($confList as $confInfo)
    	{
    		$sNo = $confInfo['sNo'];
    		
    		$sellList = BaseProcess::getSellListByConfigNo($sNo);
    		$sellCount = count($sellList);
    		if($sellCount>0)
    		{
    			$fPackageName = sprintf("%s%s.xls", $fPath, $sNo);
    			$msg = '取票码编号	影院名称	放映时间	取票码	座位信息';
    			BaseProcess::writeFile($fPackageName, $msg);
    			
    			$seatTotalNum = 0;		//影厅总座位数
    			$brushTotalNum = 0;		//刷票总数
    			$totalMPrice = 0;		//总价格
    			foreach($sellList as $sellInfo)
    			{
    				$fetchInfo = BaseProcess::getFetchNosByRM($sellInfo['iRoomMovieID']);
    				$msg = sprintf("%s	%s	%s	%s	%s", $sNo, $sellInfo['sCinemaName'], $sellInfo['dBeginTime'], "取票码：".$fetchInfo['sInterfaceValidCode'], $fetchInfo['sSeatInfo']);
    				BaseProcess::writeFile($fPackageName, $msg);
    				
    				$seatTotalNum += $sellInfo['iSeatNum'];
    				$brushTotalNum +=$sellInfo['iBrushNum'];
    				$totalMPrice += $sellInfo['mPrice'];
    			}
    		}
    		
    		$msg = sprintf("%s	%s	%s	%s	%s	%s	%s	%s	%s", $sNo, $confInfo['iCinemaID'], $confInfo['dStartTime']
    				, $confInfo['dEndTime'], $seatTotalNum, $confInfo['iBuySum'], $brushTotalNum, round($totalMPrice/$sellCount, 2), $sellCount);
    		BaseProcess::writeFile($fIndexName, $msg);
    		
    		//修改iOutput状态
    		BaseProcess::updateConfigInfo($sNo, array('iOutputFlag'=>1));
    	}
    }
    
    /**
     * 输出刷量数据（‘惊天解密’、‘追龙’专用）
     *
     * @param array[] $args 输入参数
     * 			$args[0]：影片ID（E票网ID）
     */
    public function actionOutputPackage1($args)
    {
    	//当前导出的form_id为3
    	
    	ini_set('memory_limit', '512M');
    	$movieID = $args[0];
    	$formID = $args[1];
    	$fPath = Yii::app()->basePath.'/commands/package/';
    	
    	$cinemaList = BaseProcess::getOutputCinemaList($formID);
    	foreach($cinemaList as $cinemaInfo)
    	{
    		$iCinemaID = $cinemaInfo['iCinemaID'];
    		$sCinemaName = $cinemaInfo['sCinemaName'];
    		$sCityName = $cinemaInfo['sCityName'];
    		
    		$fPackageName = sprintf("%s(%s)%s.txt", $fPath, $sCityName, $sCinemaName);
    		$msg = '影厅	放映时间	取票码	座位信息';
    		BaseProcess::writeFile($fPackageName, $msg);
    		
    		$orderList = BaseProcess::getOrderListByCinemaID($iCinemaID, $movieID, $formID);
    		foreach($orderList as $orderInfo)
    		{
    			$msg = sprintf("%s	%s	%s	%s", $orderInfo['sRoomName'], $orderInfo['dPlayTime'], "取票码：".$orderInfo['sInterfaceValidCode'], $orderInfo['orderInfo']);
    			BaseProcess::writeFile($fPackageName, $msg);
    		}
    	}
    }
    
    /**
     * 输出刷量数据（‘追龙’专用，去除填充数据）
     *
     * @param array[] $args 输入参数
     * 			$args[0]：影片ID（E票网ID）
     */
    public function actionOutputPackage2($args)
    {
    	//当前导出的form_id为4
    	 
    	ini_set('memory_limit', '512M');
    	$movieID = $args[0];
    	$formID = $args[1];
    	$fPath = Yii::app()->basePath.'/commands/package/';
    	 
    	$cinemaList = BaseProcess::getOutputCinemaList($formID);
    	foreach($cinemaList as $cinemaInfo)
    	{
    		$iCinemaID = $cinemaInfo['iCinemaID'];
    		$sCinemaName = $cinemaInfo['sCinemaName'];
    		$sCityName = $cinemaInfo['sCityName'];
    		
    		$fPackageName = sprintf("%s(%s)%s.txt", $fPath, $sCityName, $sCinemaName);
    		$msg = '影厅	放映时间	取票码	座位信息';
    		BaseProcess::writeFile($fPackageName, $msg);
    
    		$orderList = BaseProcess::getOrderListByCinemaID($iCinemaID, $movieID, $formID);
    		foreach($orderList as $orderInfo)
    		{
    			if($orderInfo['iFakeNum']>0 and $orderInfo['iownSeats']>2)
    			{
    				//如果有充量标识，且锁座>2，则此订单有充量动作，需要把充量座位信息去掉
    				$tempArr = explode(',', $orderInfo['orderInfo']);
    				array_pop($tempArr);
    				$orderInfo['orderInfo'] = implode(',', $tempArr);
    			}
    			
    			$msg = sprintf("%s	%s	%s	%s", $orderInfo['sRoomName'], $orderInfo['dPlayTime'], "取票码：".$orderInfo['sInterfaceValidCode'], $orderInfo['orderInfo']);
    			BaseProcess::writeFile($fPackageName, $msg);
    		}
    	}
    }
    
    /**
     * 填充假数据（‘追龙’专用）（取部分订单，人为填充一张票）
     *
     * @param array[] $args 输入参数
     * 			$args[0]：影片ID（E票网ID）
     */
    public function actionFake($args)
    {
    	$movieID = $args[0];
    	$indexID = rand(0,9);			//id末一位（已填充0、1、2、3）
    	$delCitys = '2,1,9,23,3,17,19,10,6,85,82,12,84,4,16';		//排除的城市
    	
    	$sql = sprintf("SELECT os.outerOrderId,os.iRoomMovieID,os.sSeatInfo,o.mPrice,os.iRoomID FROM {{fill_order_seat}} os,{{fill_orders}} o WHERE os.iMovieID=%d AND o.iFakeNum=0 AND (os.order_seatId%%10)=%d AND os.form_id IS NULL AND os.status in(10104,10105) AND o.iownSeats<4 AND os.sInterfaceValidCode!='' AND os.iRoomMovieID IN(SELECT iRoomMovieID FROM {{e_roommovie}} WHERE iMovieID=%d) AND os.iCinemaID NOT IN(SELECT iCinemaID FROM {{b_cinema}} WHERE iCityID IN(%s)) AND os.outerOrderId=o.outerOrderId", $movieID, $indexID, $movieID, $delCitys);
    	//$sql = sprintf("SELECT os.outerOrderId,os.iRoomMovieID,os.sSeatInfo,o.mPrice,os.iRoomID FROM {{fill_order_seat}} os,{{fill_orders}} o WHERE os.iMovieID=%d AND o.iFakeNum=0 AND (os.order_seatId%%10)=%d AND os.form_id=3 AND os.status in(10104,10105) AND o.iownSeats<4 AND os.sInterfaceValidCode!='' AND os.iRoomMovieID IN(SELECT iRoomMovieID FROM {{e_roommovie}} WHERE iMovieID=%d) AND os.iCinemaID NOT IN(SELECT iCinemaID FROM {{b_cinema}} WHERE iCityID IN(%s)) AND os.outerOrderId=o.outerOrderId", $movieID, $indexID, $movieID, $delCitys);
    	//$sql = sprintf("SELECT os.outerOrderId,os.iRoomMovieID,os.sSeatInfo,o.mPrice,os.iRoomID FROM {{fill_order_seat}} os,{{fill_orders}} o WHERE os.iMovieID=%d AND o.iFakeNum=0 AND (os.order_seatId%%10)=%d AND os.form_id is null AND os.status in(10104,10105) AND o.iownSeats<4 AND os.sInterfaceValidCode!='' AND TIMESTAMPDIFF(MINUTE,dPlayTime,NOW())>120 AND os.iCinemaID IN(SELECT iCinemaID FROM {{b_cinema}} WHERE iCityID IN(%s)) AND os.outerOrderId=o.outerOrderId", $movieID, $indexID, $delCitys);
    	//$sql = sprintf("SELECT os.outerOrderId,os.iRoomMovieID,os.sSeatInfo,o.mPrice,os.iRoomID FROM {{fill_order_seat}} os,{{fill_orders}} o WHERE os.iMovieID=%d AND o.iFakeNum=1 AND (os.order_seatId%%10)=%d AND os.form_id is null AND os.status in(10104,10105) AND o.iownSeats<4 AND os.sInterfaceValidCode!='' AND os.iRoomMovieID IN(SELECT iRoomMovieID FROM {{e_roommovie}} WHERE iMovieID=%d) AND os.iCinemaID NOT IN(SELECT iCinemaID FROM {{b_cinema}} WHERE iCityID IN(%s)) AND os.outerOrderId=o.outerOrderId", $movieID, $indexID, $movieID, $delCitys);
    	 
    	$orderList = DbUtil::queryAll($sql);
		
    	echo GeneralFunc::getCurTime().'indexID：'.$indexID."\r\n";
    	foreach($orderList as $orderInfo)
    	{
    		$iRoomMovieID = $orderInfo['iRoomMovieID'];
    		$sSeatInfo = $orderInfo['sSeatInfo'];
    		$outerOrderId = $orderInfo['outerOrderId'];
    		$mPrice = $orderInfo['mPrice'];
    		$iRoomID = $orderInfo['iRoomID'];
    		
    		$seatList = BaseProcess::getAllMovieSeatList($iRoomMovieID, $iRoomID);
    		if(empty($seatList))
    		{
    			continue;
    		}
    		$tempArr = explode('@@', $sSeatInfo);
    		$tempStr = $tempArr[count($tempArr)-1];
    		
    		$ret = array();
    		foreach($seatList['seatinfo'] as $key => $seatInfo)
    		{
    			if($seatInfo['SeatNo']==$tempStr)
    			{
    				if(empty($seatList['seatinfo'][$key+1]))
    				{
    					continue;
    				}
    				$ret['seatNo'] = $seatList['seatinfo'][$key+1]['SeatNo'];
    				if(count(explode(':',$ret['seatNo']))==1)
    				{
    					$ret['seatInfo'] = $seatList['seatinfo'][$key+1]['seatRow'] .'排'.$seatList['seatinfo'][$key+1]['seatCol'];
    				}else{
    					$ret['seatInfo'] = str_replace(':', '排', $ret['seatNo']);
    				}
    			}
    		}
    		
    		if(!empty($ret))
    		{
	    		//修改order_seat表的sSeatInfo
	    		$sql = sprintf("UPDATE {{fill_order_seat}} SET sSeatInfo=CONCAT(sSeatInfo,'@@%s') WHERE outerOrderId='%s'", $ret['seatNo'], $outerOrderId);
	    		DbUtil::execute($sql);
	
	    		//修改order_seat表的sSeatInfo、iownSeats
	    		$sql = sprintf("UPDATE {{fill_orders}} SET orderInfo=CONCAT(orderInfo,',%s'),iownSeats=iownSeats+1,iFakeNum=iFakeNum+1,totalPrice=totalPrice+%d WHERE outerOrderId='%s'", $ret['seatInfo'], $mPrice, $outerOrderId);
	    		DbUtil::execute($sql);
    		}
    		
    		echo GeneralFunc::getCurTime().'iRoomMovieID:'.$iRoomMovieID."\r\n";
    	}
    }
    
    /**
     * 填充假数据（‘追龙’专用）（取自然排期/自然锁座）
     *
     * @param array[] $args 输入参数
     * 			$args[0]：影片ID（E票网ID）
     */
    public function actionFake1($args)
    {
    	$movieID = $args[0];
    	$lockModel = 110;
    	
    	$confSelCinemas = '1923,5516,2049,1652,2926,3761,4317,5796,4639,6047,4946,5883,5951,4245,1007,251,249,248,250,247,1933,5489,6064,5355,1632,2105,2590,2114,4698,1692,2247,1972,584,1975,1837,2094,1964,1514,2071,2064,2262,2630';	//排除影院
    	   
    	$sql = sprintf("SELECT rm.*,r.iSeatNum FROM {{e_roommovie}} rm LEFT JOIN {{b_room}} r ON rm.iRoomID=r.iRoomID WHERE rm.iMovieID=%d AND rm.iInterfaceID!=10 AND rm.mPrice<=35 AND LEFT(dbegintime,10)='2017-10-03' AND iEpiaoCinemaID NOT IN(%s) AND rm.iRoomMovieID NOT IN(SELECT iRoomMovieID FROM {{fill_sell}}) ORDER BY rm.dBeginTime DESC", $movieID, $confSelCinemas);
    	$rmList = DbUtil::queryAll($sql);
    	foreach($rmList as $k => $rmInfo)
    	{
    		$rmInfo['dCreateTime'] = GeneralFunc::getCurTime();
    		$lockCount = 4;
    		
    		//获取已售出座位图
    		$lockedSeatList = BaseProcess::getLockedMovieSeatList($rmInfo['iRoomMovieID']);
    		
    		if(FALSE == empty($lockedSeatList) and count($lockedSeatList)>1)
    		{
    			BaseProcess::addRoommovieToSell($rmInfo);
    			$uInfo = BaseProcess::getRandUInfo();	//随机用户
    			
    			//获取待锁座座位信息
    			$lockSeatList = BaseProcess::getLockSeatList($lockedSeatList, $lockModel, $lockCount);
    			
    			//锁座
    			$outerOrderId = BaseProcess::lockSeatForFake($rmInfo['iRoomMovieID'], $lockSeatList, $uInfo, $lockModel, $rmInfo['iInterfaceID']);
    		}
    	}
    }
    
    /**
     * 测试
     *
     * @param array[] $args 输入参数
     */
    public function actionTest($args)
    {
    	$iRoomMovieID = '10430217375';
    	$seatList = BaseProcess::getMovieSeatList($iRoomMovieID);
    	print_r($seatList);
    	exit(0);
    	
    	$sql = sprintf("SELECT iRoomMovieID FROM {{fill_sell}} WHERE iMovieid=2216");
    	$cinemaList = DbUtil::queryAll($sql);
    	
    	foreach($cinemaList as $cinemaInfo)
    	{
    		$iRoomMovieID = $cinemaInfo['iRoomMovieID'];
    		
    		$sql = sprintf("SELECT SUM(o.iownSeats) as brushNum from {{fill_orders}} o,{{fill_order_seat}} os WHERE os.iroommovieid='%s' AND (os.status=10105 OR os.status=10104) AND o.outerOrderId=os.outerOrderId", $iRoomMovieID);
    		$brushInfo = DbUtil::queryRow($sql);
    		$iBrushNum = empty($brushInfo['brushNum']) ? 0 : $brushInfo['brushNum'];
    		
    		$sql = sprintf("UPDATE {{fill_sell}} SET iBrushNum=%d WHERE iRoomMovieID='%s'", $iBrushNum, $iRoomMovieID);
    		DbUtil::execute($sql);
    	}
    }
    
    /**
     * 输出刷量统计（‘追龙’专用，导出所有订单）
     *
     * @param array[] $args 输入参数
     */
    public function actionOutputStat1($args)
    {
    	ini_set('memory_limit','1024M');
    	$fPath = Yii::app()->basePath.'/commands/package/';
    	$noCinemas = array(
		    '5528'=>array('sProName'=>'辽宁省', 'sCityName'=>'抚顺'),
		    '3917'=>array('sProName'=>'黑龙江省', 'sCityName'=>'哈尔滨'),
		    '2976'=>array('sProName'=>'河南省', 'sCityName'=>'商丘'),
		    '5360'=>array('sProName'=>'湖南省', 'sCityName'=>'长沙'),
		    '2503'=>array('sProName'=>'福建省', 'sCityName'=>'漳州'),
		    '3718'=>array('sProName'=>'广东省', 'sCityName'=>'东莞'),
		    '5337'=>array('sProName'=>'广东省', 'sCityName'=>'东莞'),
		    '3319'=>array('sProName'=>'湖北省', 'sCityName'=>'荆门'),
		    '5409'=>array('sProName'=>'江苏省', 'sCityName'=>'泰州'),
		    '1883'=>array('sProName'=>'江苏省', 'sCityName'=>'镇江'),
		    '3375'=>array('sProName'=>'浙江省', 'sCityName'=>'嘉兴'),
		    '4857'=>array('sProName'=>'浙江省', 'sCityName'=>'嘉兴'),
		    '5416'=>array('sProName'=>'山西省', 'sCityName'=>'运城'),
		    '5324'=>array('sProName'=>'云南省', 'sCityName'=>'红河自治州'),
		    '3502'=>array('sProName'=>'安徽省', 'sCityName'=>'合肥'),
		    '3051'=>array('sProName'=>'四川省', 'sCityName'=>'成都'),
		    '5520'=>array('sProName'=>'湖北省', 'sCityName'=>'武汉'),
		    '4465'=>array('sProName'=>'湖北省', 'sCityName'=>'襄阳'),
		    '2879'=>array('sProName'=>'浙江省', 'sCityName'=>'杭州'),
		    '3408'=>array('sProName'=>'山东省', 'sCityName'=>'济南'),
		    '3358'=>array('sProName'=>'河南省', 'sCityName'=>'焦作'),
		    '3057'=>array('sProName'=>'河南省', 'sCityName'=>'郑州'),
		    '3674'=>array('sProName'=>'广东省', 'sCityName'=>'佛山'),
		    '5405'=>array('sProName'=>'福建省', 'sCityName'=>'泉州'),
		    '3474'=>array('sProName'=>'湖北省', 'sCityName'=>'荆州'),
		    '3033'=>array('sProName'=>'山东省', 'sCityName'=>'青岛'),
		    '5288'=>array('sProName'=>'辽宁省', 'sCityName'=>'丹东'),
		    '5478'=>array('sProName'=>'河北省', 'sCityName'=>'沧州'),
		    '1527'=>array('sProName'=>'江苏省', 'sCityName'=>'南通'),
		    '5344'=>array('sProName'=>'浙江省', 'sCityName'=>'金华'),
		    '5816'=>array('sProName'=>'广东省', 'sCityName'=>'东莞'),
		    '5414'=>array('sProName'=>'山东省', 'sCityName'=>'烟台'),
		    '3012'=>array('sProName'=>'四川省', 'sCityName'=>'达州'),
		    '6278'=>array('sProName'=>'湖北省', 'sCityName'=>'武汉'),
		    '3519'=>array('sProName'=>'浙江省', 'sCityName'=>'杭州'),
		    '6277'=>array('sProName'=>'广西自治区', 'sCityName'=>'南宁'),
		    '1503'=>array('sProName'=>'安徽省', 'sCityName'=>'滁州'),
		    '2904'=>array('sProName'=>'山东省', 'sCityName'=>'青岛'),
		    '2214'=>array('sProName'=>'陕西省', 'sCityName'=>'西安'),
		    '2878'=>array('sProName'=>'江西省', 'sCityName'=>'上饶'),
		    '6251'=>array('sProName'=>'广东省', 'sCityName'=>'东莞'),
		    '4466'=>array('sProName'=>'内蒙古', 'sCityName'=>'呼伦贝尔'),
		    '4681'=>array('sProName'=>'河北省', 'sCityName'=>'唐山'),
		    '5408'=>array('sProName'=>'江苏省', 'sCityName'=>'苏州'),
		    '5366'=>array('sProName'=>'河南省', 'sCityName'=>'洛阳'),
		    '4501'=>array('sProName'=>'吉林省', 'sCityName'=>'长春'),
		    '4509'=>array('sProName'=>'河南省', 'sCityName'=>'许昌'),
		    '5272'=>array('sProName'=>'江苏省', 'sCityName'=>'苏州'),
		    '5418'=>array('sProName'=>'湖北省', 'sCityName'=>'安庆'),
		    '5889'=>array('sProName'=>'甘肃省', 'sCityName'=>'酒泉'),
		    '2889'=>array('sProName'=>'四川省', 'sCityName'=>'成都'),
		    '3593'=>array('sProName'=>'贵州省', 'sCityName'=>'贵阳'),
		    '3074'=>array('sProName'=>'江苏省', 'sCityName'=>'无锡'),
		    '3535'=>array('sProName'=>'山东省', 'sCityName'=>'潍坊'),
		    '3588'=>array('sProName'=>'江苏省', 'sCityName'=>'徐州'),
		    '3492'=>array('sProName'=>'安徽省', 'sCityName'=>'合肥'),
		    '5401'=>array('sProName'=>'山东省', 'sCityName'=>'青岛'),
		    '1278'=>array('sProName'=>'河南省', 'sCityName'=>'新乡'),
		    '3903'=>array('sProName'=>'安徽省', 'sCityName'=>'安庆'),
		    '5378'=>array('sProName'=>'广东省', 'sCityName'=>'揭阳'),
		    '4896'=>array('sProName'=>'重庆市', 'sCityName'=>'重庆'),
		    '5450'=>array('sProName'=>'浙江省', 'sCityName'=>'绍兴'),
		    '4362'=>array('sProName'=>'辽宁省', 'sCityName'=>'营口'),
		);
    	
    	$sql = "SELECT os.iCinemaID,o.orderInfo,o.iownSeats,os.sRoomName,os.sCinemaName,os.dPlayTime,os.dCreateTime,ci.sCityName,p.sProName FROM tb_fill_orders o, tb_fill_order_seat os LEFT JOIN tb_b_cinema c ON os.iCinemaID=c.iCinemaID LEFT JOIN tb_b_city ci ON c.iCityID=ci.iCityID LEFT JOIN tb_b_province p ON p.iProID=ci.priviceID WHERE os.imovieid=2238 AND os.status IN(10104,10105) AND o.outerOrderId=os.outerOrderId ORDER BY os.dCreateTime";
    	$orderList = DbUtil::queryAll($sql);
    	
    	foreach ($orderList as $key => $orderInfo)
    	{
    		$fPackageName = sprintf("%s%s.xls", $fPath, date('Y-m-d',strtotime($orderInfo['dCreateTime'])));
    		
    		if(!file_exists($fPackageName))
    		{
    			$msg = '省份	城市	影院	影厅	放映时间	座位信息	锁座数	下单时间';
    			BaseProcess::writeFile($fPackageName, $msg);
    		}
    		
    		if($orderInfo['sCityName']=='' or $orderInfo['sProName']=='')
    		{
    			$iCinemaID = $orderInfo['iCinemaID'];
    			$msg = sprintf("%s	%s	%s	%s	%s	%s	%s	%s", trim($noCinemas[$iCinemaID]['sProName']), trim($noCinemas[$iCinemaID]['sCityName']), trim($orderInfo['sCinemaName'])
    				, trim($orderInfo['sRoomName']), $orderInfo['dPlayTime'], $orderInfo['orderInfo'], $orderInfo['iownSeats'], $orderInfo['dCreateTime']);
    		}else{
    			$msg = sprintf("%s	%s	%s	%s	%s	%s	%s	%s", trim($orderInfo['sProName']), trim($orderInfo['sCityName']), trim($orderInfo['sCinemaName'])
    				, trim($orderInfo['sRoomName']), $orderInfo['dPlayTime'], $orderInfo['orderInfo'], $orderInfo['iownSeats'], $orderInfo['dCreateTime']);
    		}
    		BaseProcess::writeFile($fPackageName, $msg);
    		
    		echo GeneralFunc::getCurTime().'orderInfo:'.$orderInfo['orderInfo']."\r\n";
    	}
    	
    	echo 'ok';
    }
    
    /**
     * 输出刷量统计（‘追龙’专用，导出所有排期）
     *
     * @param array[] $args 输入参数
     */
    public function actionOutputStat2($args)
    {
    	ini_set('memory_limit','1024M');
    	$fPath = Yii::app()->basePath.'/commands/package/';
    	$noCinemas = array(
    			'5528'=>array('sProName'=>'辽宁省', 'sCityName'=>'抚顺'),
    			'3917'=>array('sProName'=>'黑龙江省', 'sCityName'=>'哈尔滨'),
    			'2976'=>array('sProName'=>'河南省', 'sCityName'=>'商丘'),
    			'5360'=>array('sProName'=>'湖南省', 'sCityName'=>'长沙'),
    			'2503'=>array('sProName'=>'福建省', 'sCityName'=>'漳州'),
    			'3718'=>array('sProName'=>'广东省', 'sCityName'=>'东莞'),
    			'5337'=>array('sProName'=>'广东省', 'sCityName'=>'东莞'),
    			'3319'=>array('sProName'=>'湖北省', 'sCityName'=>'荆门'),
    			'5409'=>array('sProName'=>'江苏省', 'sCityName'=>'泰州'),
    			'1883'=>array('sProName'=>'江苏省', 'sCityName'=>'镇江'),
    			'3375'=>array('sProName'=>'浙江省', 'sCityName'=>'嘉兴'),
    			'4857'=>array('sProName'=>'浙江省', 'sCityName'=>'嘉兴'),
    			'5416'=>array('sProName'=>'山西省', 'sCityName'=>'运城'),
    			'5324'=>array('sProName'=>'云南省', 'sCityName'=>'红河自治州'),
    			'3502'=>array('sProName'=>'安徽省', 'sCityName'=>'合肥'),
    			'3051'=>array('sProName'=>'四川省', 'sCityName'=>'成都'),
    			'5520'=>array('sProName'=>'湖北省', 'sCityName'=>'武汉'),
    			'4465'=>array('sProName'=>'湖北省', 'sCityName'=>'襄阳'),
    			'2879'=>array('sProName'=>'浙江省', 'sCityName'=>'杭州'),
    			'3408'=>array('sProName'=>'山东省', 'sCityName'=>'济南'),
    			'3358'=>array('sProName'=>'河南省', 'sCityName'=>'焦作'),
    			'3057'=>array('sProName'=>'河南省', 'sCityName'=>'郑州'),
    			'3674'=>array('sProName'=>'广东省', 'sCityName'=>'佛山'),
    			'5405'=>array('sProName'=>'福建省', 'sCityName'=>'泉州'),
    			'3474'=>array('sProName'=>'湖北省', 'sCityName'=>'荆州'),
    			'3033'=>array('sProName'=>'山东省', 'sCityName'=>'青岛'),
    			'5288'=>array('sProName'=>'辽宁省', 'sCityName'=>'丹东'),
    			'5478'=>array('sProName'=>'河北省', 'sCityName'=>'沧州'),
    			'1527'=>array('sProName'=>'江苏省', 'sCityName'=>'南通'),
    			'5344'=>array('sProName'=>'浙江省', 'sCityName'=>'金华'),
    			'5816'=>array('sProName'=>'广东省', 'sCityName'=>'东莞'),
    			'5414'=>array('sProName'=>'山东省', 'sCityName'=>'烟台'),
    			'3012'=>array('sProName'=>'四川省', 'sCityName'=>'达州'),
    			'6278'=>array('sProName'=>'湖北省', 'sCityName'=>'武汉'),
    			'3519'=>array('sProName'=>'浙江省', 'sCityName'=>'杭州'),
    			'6277'=>array('sProName'=>'广西自治区', 'sCityName'=>'南宁'),
    			'1503'=>array('sProName'=>'安徽省', 'sCityName'=>'滁州'),
    			'2904'=>array('sProName'=>'山东省', 'sCityName'=>'青岛'),
    			'2214'=>array('sProName'=>'陕西省', 'sCityName'=>'西安'),
    			'2878'=>array('sProName'=>'江西省', 'sCityName'=>'上饶'),
    			'6251'=>array('sProName'=>'广东省', 'sCityName'=>'东莞'),
    			'4466'=>array('sProName'=>'内蒙古', 'sCityName'=>'呼伦贝尔'),
    			'4681'=>array('sProName'=>'河北省', 'sCityName'=>'唐山'),
    			'5408'=>array('sProName'=>'江苏省', 'sCityName'=>'苏州'),
    			'5366'=>array('sProName'=>'河南省', 'sCityName'=>'洛阳'),
    			'4501'=>array('sProName'=>'吉林省', 'sCityName'=>'长春'),
    			'4509'=>array('sProName'=>'河南省', 'sCityName'=>'许昌'),
    			'5272'=>array('sProName'=>'江苏省', 'sCityName'=>'苏州'),
    			'5418'=>array('sProName'=>'湖北省', 'sCityName'=>'安庆'),
    			'5889'=>array('sProName'=>'甘肃省', 'sCityName'=>'酒泉'),
    			'2889'=>array('sProName'=>'四川省', 'sCityName'=>'成都'),
    			'3593'=>array('sProName'=>'贵州省', 'sCityName'=>'贵阳'),
    			'3074'=>array('sProName'=>'江苏省', 'sCityName'=>'无锡'),
    			'3535'=>array('sProName'=>'山东省', 'sCityName'=>'潍坊'),
    			'3588'=>array('sProName'=>'江苏省', 'sCityName'=>'徐州'),
    			'3492'=>array('sProName'=>'安徽省', 'sCityName'=>'合肥'),
    			'5401'=>array('sProName'=>'山东省', 'sCityName'=>'青岛'),
    			'1278'=>array('sProName'=>'河南省', 'sCityName'=>'新乡'),
    			'3903'=>array('sProName'=>'安徽省', 'sCityName'=>'安庆'),
    			'5378'=>array('sProName'=>'广东省', 'sCityName'=>'揭阳'),
    			'4896'=>array('sProName'=>'重庆市', 'sCityName'=>'重庆'),
    			'5450'=>array('sProName'=>'浙江省', 'sCityName'=>'绍兴'),
    			'4362'=>array('sProName'=>'辽宁省', 'sCityName'=>'营口'),
    	);
    	 
    	$sql = "SELECT DISTINCT(os.iRoomMovieID),os.iCinemaID,o.iSeatNum,SUM(o.iownSeats) AS iownSeats,os.sRoomName,os.sCinemaName,os.dPlayTime,ci.sCityName,p.sProName FROM tb_fill_orders o,tb_fill_order_seat os LEFT JOIN tb_b_cinema c ON os.iCinemaID=c.iCinemaID LEFT JOIN tb_b_city ci ON c.iCityID=ci.iCityID LEFT JOIN tb_b_province p ON p.iProID=ci.priviceID WHERE os.imovieid=2238 AND os.status IN(10104,10105) AND o.outerOrderId=os.outerOrderId GROUP BY 1 ORDER BY os.dPlayTime";
    	$rmList = DbUtil::queryAll($sql);
    	
    	foreach ($rmList as $key => $rmInfo)
    	{
    		$rmID = $rmInfo['iRoomMovieID'];
    		$fPackageName = sprintf("%s%s.xls", $fPath, date('Y-m-d',strtotime($rmInfo['dPlayTime'])));
    		
    		if(!file_exists($fPackageName))
    		{
    			$msg = '省份	城市	影院	影厅	放映时间	总座位数	总售卖数	总锁座数';
    			BaseProcess::writeFile($fPackageName, $msg);
    		}
    		
    		//排期日志信息
    		$sql = sprintf("SELECT iSeatNum,iSellSeatNum FROM epiaowang_bak.tb_l_sellseatlog_2238 WHERE iRoomMovieID='%s'", $rmID);
    		$rmLogInfo = DbUtil::queryRow($sql);
    		
    		$iSeatNum = $iSellSeatNum = $iownSeats = 0;
    		$iSeatNum = empty($rmLogInfo) ? $rmInfo['iSeatNum'] : $rmLogInfo['iSeatNum'];	//总座位数
    		$iSellSeatNum = empty($rmLogInfo) ? $rmInfo['iownSeats'] : $rmLogInfo['iSellSeatNum'];	//总售卖数
    		$iownSeats = $rmInfo['iownSeats'];										//总刷票数
    		
    		if($iSeatNum==0)
    		{
    			$sql = sprintf("SELECT iSeatNum FROM tb_fill_sell WHERE iRoomMovieID='%s'", $rmID);
    			$tempInfo = DbUtil::queryRow($sql);
    			$iSeatNum = empty($tempInfo) ? 0 : $tempInfo['iSeatNum'];
    		}
    		if($iSellSeatNum < $iownSeats)
    		{
    			$iSellSeatNum = $iownSeats;
    		}
    		if($iSellSeatNum > $iSeatNum)
    		{
    			$iSellSeatNum = $iSeatNum;
    		}
    		
    	    if($rmInfo['sCityName']=='' or $rmInfo['sProName']=='')
    		{
    			$iCinemaID = $rmInfo['iCinemaID'];
    			$msg = sprintf("%s	%s	%s	%s	%s	%s	%s	%s", trim($noCinemas[$iCinemaID]['sProName']), trim($noCinemas[$iCinemaID]['sCityName']), trim($rmInfo['sCinemaName'])
    				, trim($rmInfo['sRoomName']), $rmInfo['dPlayTime'], $iSeatNum, $iSellSeatNum, $iownSeats);
    		}else{
    			$msg = sprintf("%s	%s	%s	%s	%s	%s	%s	%s", trim($rmInfo['sProName']), trim($rmInfo['sCityName']), trim($rmInfo['sCinemaName'])
    				, trim($rmInfo['sRoomName']), $rmInfo['dPlayTime'], $iSeatNum, $iSellSeatNum, $iownSeats);
    		}
    		BaseProcess::writeFile($fPackageName, $msg);
    	}
    }
}
