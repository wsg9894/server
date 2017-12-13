<?php

/**
 * CinemaProcess - ӰԺ�ӿڲ�����
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
     * ��Ч�ֶι��ˣ�������Ч���ֶ��б�
     *
     * @param array[] $inputFields	������ֶ��б�
     * @param array[] $defFields ���ݿ��ֶ��б�
     * @return array[] ���˺���ֶ��б�
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
     * ��ȡӰ����λͼ
     * $bookSeatingArrange����sRoomMovieInterfaceNo��iInterfaceID��sCinemaInterfaceNo��sRoomInterfaceNo
     * @param array[] $fields ��Ҫ�ֶ��б�һά���飬���Ϊ�����ȡȫ��
     * @return array[][] ӰԺ�б���ά����
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
     * ��ȡ������Ϣ
     * $bookSeatingArrange����sRoomMovieInterfaceNo��iInterfaceID��sCinemaInterfaceNo��sRoomInterfaceNo
     * @param array[] $fields ��Ҫ�ֶ��б�һά���飬���Ϊ�����ȡȫ��
     * @return array[][] ӰԺ�б���ά����
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
     * ȡ������
     * $bookSeatingArrange����sRoomMovieInterfaceNo��iInterfaceID��sCinemaInterfaceNo��sRoomInterfaceNo
     * @param array[] $fields ��Ҫ�ֶ��б�һά���飬���Ϊ�����ȡȫ��
     * @return array[][] ӰԺ�б���ά����
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
     * �ӽӿڷ���ȡ������Ϣ
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