<?php

/**
 * CinemaProcess - 影院接口操作类
 * @author anqing
 * @version V1.0
 */
class CinemaInterfaceProcess
{

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
            case ConfigParse::getInterfaceKey('InterfaceType_Hipiao'):
                return HipiaoProviderProcess::getInstance();
            case ConfigParse::getInterfaceKey('InterfaceType_Ipiao'):
                return IpiaoProviderProcess::getInstance();
            case ConfigParse::getInterfaceKey('InterfaceType_Wangpiao'):
                return WangpiaoProviderProcess::getInstance();
            case ConfigParse::getInterfaceKey('InterfaceType_GWL'):
                return GWLProviderProcess::getInstance();
            case ConfigParse::getInterfaceKey('InterfaceType_MaoYan'):
                return MaoyanProviderProcess::getInstance();
            default:
                break;
        }
        return NULL;
    }

    /**
     * 获取影厅座位图
     * $bookSeatingArrange参数sRoomMovieInterfaceNo，iInterfaceID，sCinemaInterfaceNo，sRoomInterfaceNo
     * @param array[] $fields 所要字段列表，一维数组，如果为空则获取全部
     * @return array[][] 影院列表，二维数组
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
     * 获取订单信息
     * $bookSeatingArrange参数sRoomMovieInterfaceNo，iInterfaceID，sCinemaInterfaceNo，sRoomInterfaceNo
     * @param array[] $fields 所要字段列表，一维数组，如果为空则获取全部
     * @return array[][] 影院列表，二维数组
     */
    public static function GetCreateOrderResult($outOrderId)
    {
        $orderInfo = OrderProcess::getOrderSeatInfoByOuterOrderId($outOrderId);
        if (empty($orderInfo))
        {
            return array();
        }

        $arArrangeInfo = CinemaProcess::getRoomMovieListByiRoommovieID($orderInfo['iRoomMovieID']);
        return self::TryCreateBSObjectProvider($orderInfo['iInterfaceID'])->GetCreateOrderResult(
            $orderInfo['sSeatInfo'],  $orderInfo['sRoomMovieInterfaceNo'],$orderInfo['sPhone'],
            $orderInfo['outerOrderId'], $orderInfo['order_seatId'],$orderInfo['sCinemaInterfaceNo'],
            $orderInfo['sRoomInterfaceNo'],$orderInfo['sMovieInterfaceNo'],
            $orderInfo['mPrice'],$orderInfo['mFee'] ,$arArrangeInfo['mSettlementPrice'],$arArrangeInfo['sDimensional'],$arArrangeInfo['sLanguage'],$orderInfo['iUserId']);
    }

    /**
     * 取消订单
     * $bookSeatingArrange参数sRoomMovieInterfaceNo，iInterfaceID，sCinemaInterfaceNo，sRoomInterfaceNo
     * @param array[] $fields 所要字段列表，一维数组，如果为空则获取全部
     * @return array[][] 影院列表，二维数组
     */
    public static function GetCancelOrderResult($outOrderId)
    {
        $orderInfo = OrderProcess::getOrderSeatInfoByOuterOrderId($outOrderId);
        if (empty($orderInfo))
        {
            return array();
        }

        $arArrangeInfo = CinemaProcess::getRoomMovieListByiRoommovieID($orderInfo['iRoomMovieID']);

        return self::TryCreateBSObjectProvider($orderInfo['iInterfaceID'])->GetCancelOrderResult(
            $orderInfo[ConfigParse::getOrderSeatInfoKey('sInterfaceOrderNo')],
            $orderInfo[ConfigParse::getOrderSeatInfoKey('sRoomMovieInterfaceNo')],
            $orderInfo[ConfigParse::getOrderSeatInfoKey('sCinemaInterfaceNo')],
            $orderInfo[ConfigParse::getOrderSeatInfoKey('sPhone')],
            $orderInfo[ConfigParse::getOrderSeatInfoKey('userId')]
            );

    }

    /*
     * 从接口方获取订单信息
     * */
    public static function GetConfirmOrderResult($outOrderId)
    {
        $orderInfo = OrderProcess::getOrderSeatInfoByOuterOrderId($outOrderId);
        if (empty($orderInfo))
        {
            return array();
        }

        if (Yii::app()->params['Setting_Debug']==1)
        {

            $validNo = '1|2*3';
            $arPayOrderResult['ResultCode'] = ConfigParse::getOrderStatusKey('ORDER_STATUS_Successed');
            $arPayOrderResult['InterfaceValidCode'] = $validNo;
            $arPayOrderResult['InterfaceVerificationCode'] =  'ttt';
            return $arPayOrderResult;
        }
//$interfaceOrderNo,$price,$fee,$mobile,$consumeNo,$seat,$roomMovieId
        return self::TryCreateBSObjectProvider($orderInfo['iInterfaceID'])-> GetConfirmOrderResult
           ($orderInfo[ConfigParse::getOrderSeatInfoKey('sInterfaceOrderNo')],
            $orderInfo[ConfigParse::getOrderSeatInfoKey('price')],
            $orderInfo[ConfigParse::getOrderSeatInfoKey('mFee')],
            $orderInfo[ConfigParse::getOrderSeatInfoKey('sPhone')],
            $orderInfo[ConfigParse::getOrderSeatInfoKey('outerOrderId')],
            $orderInfo[ConfigParse::getOrderSeatInfoKey('sSeatInfo')],
            $orderInfo[ConfigParse::getOrderSeatInfoKey('roomMovieNo')]
        );
    }

    public static function GetOrderInfoResult($outOrderId)
    {

        $orderInfo = OrderProcess::getOrderSeatInfoByOuterOrderId($outOrderId);
        if (empty($orderInfo))
        {
            return array();
        }
        return self::TryCreateBSObjectProvider($orderInfo['iInterfaceID'])->GetBookSeatingOrder
        ($orderInfo['sInterfaceOrderNo'], $orderInfo['sPhone'],$outOrderId);
    }
}