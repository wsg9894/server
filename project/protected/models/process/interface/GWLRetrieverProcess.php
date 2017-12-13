<?php
date_default_timezone_set('PRC');
class GWLRetrieverProcess
{
    const GewalaApiUrl = "http://openapi.gewara.com/router/rest";
    const GewalaAppkey = 'sswh';
    const GewalaSecretcode = '412739be79efde96fc9455ef857c3e3b';
    const GewalaV = '1.0';
    const GewalaFormat = 'xml';
    const GewalaSignmethod = 'MD5';
    //根据参数生成发送给接口方的数据格式
    public static function getParam($methodName, $arPara = array()) {
        $arPara["appkey"] = self::GewalaAppkey;
        $arPara['timestamp'] = date('Y-m-d H:i:s',time());
        $arPara["v"] = self::GewalaV;
        $arPara["format"] = self::GewalaFormat;
        $arPara["method"] = $methodName;

        ksort($arPara);
        $value = '';
        foreach($arPara as $key=>$val){
            $value .=$key.'='.$val.'&';
        }
        $arPara['sign']=strtoupper(md5(substr($value,0,-1).self::GewalaSecretcode));
        $arPara['signmethod']=self::GewalaSignmethod;
        ksort($arPara);
        foreach($arPara as $key=>$value){
            $arStr[] = $key . "=" . $value;
        }
        //return $arStr;
        return implode('&',$arStr);
    }

    private static function getJsonFromHttp($methodName, $arPara = array()) {
        $para = self::getParam($methodName, $arPara);
        try {
            $xmlString = self::MyHttpPost(self::GewalaApiUrl, $para,'utf-8',240,1);
        } catch (Exception $e) {
            $xmlString = '';
        }

        return $xmlString;
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
     * 合作商已开通的城市
     * @return mixed|string
     */
    public static function Get_Base_CityBll(){
        $xmlString = self::getJsonFromHttp("com.gewara.partner.movie.openPartnerCityList");
        return $xmlString;
    }

    /**
     * 获取城市下面的区县
     * @param $citycode
     * @return mixed|string
     */
    public static function Get_Base_District($citycode){
        $cityCode = array('citycode'=>$citycode);
        $xmlString = self::getJsonFromHttp("com.gewara.partner.countyList",$cityCode);
        return $xmlString;
    }
    /**
     * 通过影院id、影片id、放映日期获取场次列表
     * @param $CinemaID
     * @param $Date
     * @param string $FilmID
     * @return mixed|string
     */
    public static function Get_Base_FilmShow($CinemaID,$Date, $FilmID=''){
        $arPara = array(
            'cinemaid' => $CinemaID,
            'playdate'=> $Date
        );
        if(!empty($FilmID)){
            $arPara['FilmID'] = $FilmID;
        }
        $xmlString = self::getJsonFromHttp("com.gewara.partner.movie.opiList", $arPara);
        return $xmlString;
    }
    /**
     *通过场次id查询场次已经售出或已经锁定的座位信息
     * @param $seq_no 场次id
     * @return mixed|string
     */
    public static function Get_Base_SellSeat($seq_no){
        $arPara = array('mpid' => $seq_no);
//        $xmlString = self::getJsonFromHttp("com.gewara.partner.movie.opiLockSeatInfo", $arPara);
        $xmlString = self::getJsonFromHttp("com.gewara.partner.movie.opiLockSeatInfo", $arPara);
        return $xmlString;
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
    public static function Get_Sell_LockSeat($roomMovieNo,$mobile,$sLanguage,$sDimensional,$seat,$iUserID){
        $arPara = array('mpid' => $roomMovieNo,'mobile'=>$mobile,'language'=>$sLanguage,'edition'=>$sDimensional,'seatLabel'=>$seat,'ukey'=>md5($iUserID));
        $xmlString = self::getJsonFromHttp("com.gewara.partner.movie.addTicketOrder", $arPara);
        return $xmlString;
    }

    /**
     * 取消接口方订单
     * @param $SID
     * @return mixed|string
     */
    public static function Get_Sell_UnLockSeat($SID, $iUserId) {
        $arPara = array('tradeNo' => $SID,'ukey'=>md5($iUserId));
        $jsonString = self::getJsonFromHttp("com.gewara.partner.order.cancelOrder", $arPara);
        return $jsonString;
    }

    public static  function Get_Sell_SearchOrderInfoBySID($SID) {
        $arPara = array('tradeno' => $SID);
        $jsonString = self::getJsonFromHttp("com.gewara.partner.movie.ticketOrderDetail", $arPara);
        return $jsonString;
    }

    public static function Get_Sell_PayNotify($interfaceOrderNo,$Amount,$outOrderId){
        $arPara = array('tradeno' => $interfaceOrderNo,'paidAmount'=>$Amount,'payseqno'=>$outOrderId);
        $jsonString = self::getJsonFromHttp("com.gewara.partner.movie.imprest.payNotify", $arPara);
        return $jsonString;
    }

    public static function Get_Sell_BuyTicket($interfaceOrderNo){
        $arPara = array('tradeno' => $interfaceOrderNo);
        $jsonString = self::getJsonFromHttp("com.gewara.partner.movie.takeTicketCodeList", $arPara);
        return $jsonString;
    }

    public static function Get_Sell_BuyTicketMes($interfaceOrderNo){
        $arPara = array('tradeno' => $interfaceOrderNo);
        $jsonString = self::getJsonFromHttp("com.gewara.partner.movie.takeTicketSms", $arPara);
        return $jsonString;
    }
}


        
       

     
       