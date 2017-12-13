<?php

/**
 * BInterfProcess - 影院接口操作类
 * @author lzz
 * @version V1.0
 */
class BInterfProcess
{
	const SEND_ORDER_SETTING = 1;		//下单开关（非刷票情况下一定要设置为0）
	
    private static $instance;

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new CinemaInterfaceProcess();
        }

        return self::$instance;
    }

    function __construct()
    {
    }
    function __destruct()
    {
    }
    
    /**
     * 获取影厅售卖座位信息
     * @param array[] $bookSeatingArrange 排期信息
     * @param array[] $fields 所要字段列表，一维数组，如果为空则获取全部
     * @return array[][] 影厅售卖信息
     */
    public static function GetSelectedSeat($bookSeatingArrange, $fields=array())
    {
    	if(empty($bookSeatingArrange))
    	{
    		return array();
    	}
    
    	$interfaceId = $bookSeatingArrange['iInterfaceID'];
    	$bsObjectProvider = CinemaInterfaceProcess::TryCreateBSObjectProvider($interfaceId);
    	if ($bsObjectProvider === NULL)
    	{
    		return array();
    	}
    
    	return $bsObjectProvider->GetBookSeatingLockSeats($bookSeatingArrange);
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

    public static function TryCreateBSObjectProvider($InterfaceType)
    {
    	switch ($InterfaceType)
    	{
    		case ConfigParse::getInterfaceKey('InterfaceType_Spider'):
    			return SpiderProviderProcess::getInstance();
    		case ConfigParse::getInterfaceKey('InterfaceType_Wangpiao'):
    			return WangpiaoProviderProcess::getInstance();
    		case ConfigParse::getInterfaceKey('InterfaceType_GWL'):
    			return GWLProviderProcess::getInstance();
    		default:
    			break;
    	}
    	return NULL;
    }
    
    
    /**
     * 锁座
     * 
     * @param string $outOrderId E票网订单NO
     * @return array[][] 返回结果
     */
    public static function GetCreateOrderResult($outOrderId)
    {
    	$orderInfo = BOrderProcess::getOrderSeatInfoByOuterOrderId($outOrderId);
    	if (empty($orderInfo))
    	{
    		return array();
    	}
    	
    	$arArrangeInfo = BaseProcess::getRoommovieInfoByRoommovieID($orderInfo['iRoomMovieID']);
    	
    	return self::TryCreateBSObjectProvider($orderInfo['iInterfaceID'])->GetCreateOrderResult(
    			$orderInfo['sSeatInfo'],  $orderInfo['sRoomMovieInterfaceNo'], $orderInfo['sPhone'],
    			$orderInfo['outerOrderId'], $orderInfo['order_seatId'], $orderInfo['sCinemaInterfaceNo'],
    			$orderInfo['sRoomInterfaceNo'], $orderInfo['sMovieInterfaceNo'], $orderInfo['mPrice'], 
    			$orderInfo['mFee'], $arArrangeInfo['mSettlementPrice'], $orderInfo['sDimensional'], $orderInfo['sLanguage'], $orderInfo['iUserId']
    	);
    }
    
    /**
     * 释放座位
     *
     * @param array[] $orderSeatInfo E票网座位订单信息
     * @return array[][] 返回结果
     */
    public static function GetCancelOrderResult($orderSeatInfo)
    {
    	if (empty($orderSeatInfo))
    	{
    		return array();
    	}
    
    	return self::TryCreateBSObjectProvider($orderSeatInfo['iInterfaceID'])->GetCancelOrderResult(
    			$orderSeatInfo[ConfigParse::getOrderSeatInfoKey('sInterfaceOrderNo')],
    			$orderSeatInfo[ConfigParse::getOrderSeatInfoKey('sRoomMovieInterfaceNo')],
    			$orderSeatInfo[ConfigParse::getOrderSeatInfoKey('sCinemaInterfaceNo')],
    			$orderSeatInfo[ConfigParse::getOrderSeatInfoKey('sPhone')],
    			$orderSeatInfo[ConfigParse::getOrderSeatInfoKey('userId')]
    	);
    }
    
    /**
     * 获取取票码
     *
     * @param array[] $orderSeatInfo E票网座位订单信息
     * @return array[][] 返回结果
     */
    public static function GetApplyTicket($orderSeatInfo)
    {
    	if (empty($orderSeatInfo))
    	{
    		return array();
    	}
    
    	return self::TryCreateBSObjectProvider($orderSeatInfo['iInterfaceID'])->GetBookSeatingOrder(
    			$orderSeatInfo[ConfigParse::getOrderSeatInfoKey('sInterfaceOrderNo')],
    			$orderSeatInfo[ConfigParse::getOrderSeatInfoKey('sPhone')],
    			$orderSeatInfo[ConfigParse::getOrderSeatInfoKey('outerOrderId')]
    	);
    }
    
    /**
     * 申请下单
     * 
     * @param array[] $orderInfo E票网订单信息
     * @return array[][] 返回结果
     */
     public static function GetConfirmOrderResult($orderInfo)
     {
    	if(0==self::SEND_ORDER_SETTING)	//测试
    	{
    		$validNo = '7|8*9';
    		$arPayOrderResult['ResultCode'] = ConfigParse::getOrderStatusKey('ORDER_STATUS_Successed');
    		$arPayOrderResult['InterfaceValidCode'] = $validNo;
    		return $arPayOrderResult;
    	}else{
    		
    		return self::TryCreateBSObjectProvider($orderInfo['iInterfaceID'])-> GetConfirmOrderResult(
    				$orderInfo[ConfigParse::getOrderSeatInfoKey('sInterfaceOrderNo')],
    				$orderInfo[ConfigParse::getOrderSeatInfoKey('price')],
    				$orderInfo[ConfigParse::getOrderSeatInfoKey('mFee')],
    				$orderInfo[ConfigParse::getOrderSeatInfoKey('sPhone')],
    				$orderInfo[ConfigParse::getOrderSeatInfoKey('outerOrderId')],
    				$orderInfo[ConfigParse::getOrderSeatInfoKey('sSeatInfo')],
    				$orderInfo[ConfigParse::getOrderSeatInfoKey('roomMovieNo')]
    		);

    	}
    }
    
    
    
}