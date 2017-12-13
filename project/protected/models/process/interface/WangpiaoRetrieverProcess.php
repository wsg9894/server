<?php

class WangpiaoRetrieverProcess {

    const WangpiaoApiUrl = "http://channel.api.wangpiao.com/2.0/";
    const WangpiaoUser = 'WP_EPWAPI';
    const WangpiaoKey = 'Ex2X6m5guRs65TbK';

    public static function getParam($methodName, $arPara = array()) {
        $arPara["UserName"] = self::WangpiaoUser;
        $arPara['Target'] = $methodName;

        ksort($arPara);
        $arStr = array();
        $value = '';
        foreach ($arPara as $key => $v) {
            $arStr[] = $key . "=" . $v;
            $value .= $v;
        }
        $value .= self::WangpiaoKey;
        $arStr['Sign'] = 'Sign=' . md5($value);
        return implode('&', $arStr);
    }

    //1.13. 已售出座位信息查询 — Base_SellSeat
    static public function Get_Base_SellSeat($CinemaID, $ShowIndex) {
        $arPara = array('CinemaID' => $CinemaID,
            'ShowIndex' => $ShowIndex);
        $jsonString = self::getJsonFromHttp("Base_SellSeat", $arPara);
        return $jsonString;
    }

    /**
     *
     * @param type $methodName
     * @param type $arPara
     * @return string
     */
    private static function getJsonFromHttp($methodName, $arPara = array()) {
        $para = self::getParam($methodName, $arPara);
        try {
            $jsonString = self::MyHttpPost(self::WangpiaoApiUrl, $para,'utf-8',90);
        } catch (Exception $e) {
            $jsonString = '';
        }

        return $jsonString;
    }

    public static function MyHttpPost($url, $postDataStr,$encoding = "utf-8",$timeout=30,$WriteLog=0)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER,0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postDataStr);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    static public function Get_Sell_LockSeat($CinemaID, $ShowIndex, $SeatInfo,$mobile) {
        $arPara = array('CinemaID' => $CinemaID,
            'ShowIndex' => $ShowIndex,
            'SeatInfo' => $SeatInfo,
            'Mobile' => $mobile);
        $jsonString = self::getJsonFromHttp("Sell_LockSeat", $arPara);
        return $jsonString;
    }

    static public function Get_Sell_ApplyTicket($SID,$PayType,$AID,$Mobile,$MsgType, $Amount,$UserAmount,$GoodsType) {
        $arPara = array('SID' => $SID,
            'PayType' => $PayType,
            'AID' => $AID,
            'Mobile' => $Mobile,
            'MsgType' => $MsgType,
            'Amount' => $Amount,
            'UserAmount' => $UserAmount,
            'GoodsType' => $GoodsType);
        $jsonString = self::getJsonFromHttp("Sell_ApplyTicket", $arPara);
        return $jsonString;
    }

    //2.6 申请购票 — Sell_BuyTicket
    static public function Get_Sell_BuyTicket($SID, $PayNo,$PlatformPayNo) {
        $arPara = array('SID' => $SID,
            'PayNo' => $PayNo,
            'PlatformPayNo' => $PlatformPayNo);
        $jsonString = self::getJsonFromHttp("Sell_BuyTicket", $arPara);
        return $jsonString;
    }

    //2.3 释放座位 — Sell_UnLockSeat
    static public function Get_Sell_UnLockSeat($SID) {
        $arPara = array('SID' => $SID);
        $jsonString = self::getJsonFromHttp("Sell_UnLockSeat", $arPara);
        return $jsonString;
    }

    //2.8 订单查询 — Sell_SearchOrderInfoBySID
    static public function Get_Sell_SearchOrderInfoBySID($SID) {
        $arPara = array('SID' => $SID);
        $jsonString = self::getJsonFromHttp("Sell_SearchOrderInfoBySID", $arPara);
        return $jsonString;
    }
}
