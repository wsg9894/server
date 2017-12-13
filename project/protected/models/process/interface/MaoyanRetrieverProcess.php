<?php
date_default_timezone_set('PRC');
class MaoyanRetrieverProcess
{
    //接口请求地址
    const MaoyanApiUrl = "http://open.maoyan.com/api/";
    //猫眼渠道编号
    const MaoyanUser = '1000036';
    //猫眼秘钥
    const MaoyanKey = 'go1idbfnsnhzm1wzx4gc5j5xlrhh5wbr';
    //接口版本
    const MaoyanApiVersion = '1.0';
    //签名方式
    const SignType = 'MD5';
    //根据参数生成发送给接口方的数据格式
    private static $arApiUrl = array(
        'sync' => '/sync/gateway',
        'base' => '/base/gateway',
        'trade' => '/trade/gateway',
    );

    public static function getParam($methodName, $arPara = array()) {
        $arParams = array();
        if(!empty($arPara)){
            $arParams['bizData'] = json_encode($arPara);
        }
        $arParams["merCode"] = self::MaoyanUser;
        $arParams["timestamp"] = time();
        $arParams["version"] = self::MaoyanApiVersion;
        $arParams["signType"] = self::SignType;
        $arParams['api'] = $methodName;
        ksort($arParams);
        $arStr = array();
        foreach ($arParams as $key => $v) {
            $arStr[] = $key . "=" . $v;
        }
        $signMsg = strtoupper(md5(implode('&', $arStr).'&key='.self::MaoyanKey));
        $arParams['signMsg'] = $signMsg;
        $arStr1 = array();
        foreach($arParams as $k=>$val){
            $arStr1[] = $k . "=" . $val;
        }
        return implode('&',$arStr1);
    }

    /**
     *
     * @param type $methodName
     * @param type $arPara
     * @return string
     */
    private static function getJsonFromHttp($methodName,$methodUrl, $arPara = array()) {
        $para = self::getParam($methodName, $arPara);
        try {
            $apiUrl = self::MaoyanApiUrl.self::$arApiUrl[$methodUrl];
            $jsonString = self::MyHttpPost($apiUrl, $para,'utf-8',90,1);
        } catch (Exception $e) {
            $jsonString = '';
        }

        return $jsonString;
    }

    /**
     * 发送请求post
     * @param $url
     * @param $postDataStr
     * @param string $encoding
     * @param int $timeout
     * @param int $WriteLog
     * @return mixed
     */
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

    /**
     * 动态座位图
     * @param $ShowIndex 影讯id
     * @return string
     */
    static public function Get_Base_SellSeat($ShowIndex) {
        $arPara = array('showId' => $ShowIndex);
        $jsonString = self::getJsonFromHttp("gateway.trade.seat",'trade', $arPara);
        return $jsonString;
    }

    /**
     * 下订单并锁定座位接口
     * @param $roomMovieNo
     * @param $mobile
     * @param $sLanguage
     * @param $sDimensional
     * @param $seat
     *
     */
    static public function Get_Sell_LockSeat($CinemaID, $ShowIndex, $SeatInfo,$orderCode,$mobile,$settlePrice,$sellPrice) {
        $arPara = array('cinemaId' => $CinemaID,
            'showId' => $ShowIndex,
            'seatsJSON' => $SeatInfo,
            'orderCode' => $orderCode,
            'mobile' => $mobile,
            'settlePrice' => $settlePrice*100,
            'sellPrice' => $sellPrice*100
        );
        $jsonString = self::getJsonFromHttp("gateway.trade.lock",'trade', $arPara);
        return $jsonString;
    }
    /**
     * 取消接口方订单
     * @param $SID
     * @return mixed|string
     */
    static public function Get_Sell_UnLockSeat($orderId,$orderCode) {
        $arPara = array(
            'orderId' => $orderId,
            'orderCode' => $orderCode
        );
        $jsonString = self::getJsonFromHttp("gateway.trade.unlock",'trade', $arPara);
        return $jsonString;
    }

    /**
     * 订单查询
     * @param $orderId
     * @return string
     */
    static public function Get_Sell_SearchOrderInfoBySID($orderId) {
        $arPara = array('orderId' => $orderId);
        $jsonString = self::getJsonFromHttp("gateway.trade.queryOrder",'trade', $arPara);
        return $jsonString;
    }

    /**
     * 确认订单
     * @param $SID
     * @param $orderCode
     * @return string
     */
    static public function Get_Sell_ApplyTicket($SID,$orderCode) {
        $arPara = array('orderId' => $SID,
            'orderCode' => $orderCode);
        $jsonString = self::getJsonFromHttp("gateway.trade.fixOrder",'trade', $arPara);
        return $jsonString;
    }
}


        
       

     
       