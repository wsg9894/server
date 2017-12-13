<?php

class SpiderRetrieverProcess
{
    const filetype="json";

    private static function SpiderKey()
    {
        return "epiao";
    }

    private static  function SpiderValue()
    {
        return '7233440J2L$A0';
    }

    private static  function SpiderApiUrl()
    {
        return "http://filmapi.spider.com.cn/v2/".self::SpiderKey(). "/";
    }

    static private function SpiderApiUrlV2()
    {
        return "http://filmapi.spider.com.cn/v2/".self::SpiderKey(). "/";
    }
    //蜘蛛网配置
    private static  function SpiderApiCityList()
    {
        return "cityList.html?";
    }
    private static  function SpiderApiRegionList()
    {
        return "regionList.html?";
    }

    static private function SpiderApiCinemaList()
    {
        return "cinemaList.html?";
    }

    private static function SpiderApiShowList()
    {
        return "showList.html?";
    }

    private static function SpiderApiFilmList()
    {
        return "filmList.html?";
    }

    static  private function SpiderApiHallList()
    {
        return "hallList.html?";
    }

    private static  function SpiderApiSeatList()
    {
        return "seatList.html?";
    }

    private static  function SpiderApiShowSeatList()
    {
        return "showSeatList.html?";
    }

    private static  function SpiderApiLockSeatList()
    {
        return "lockSeatList.html?";
    }

    private static  function SpiderApiConfirmOrder()
    {
        return "confirmOrder.html?";
    }

    private static function SpiderApiQryOrderStatus()
    {
        return "qryOrderStatus.html?";
    }

    private static function SpiderApiUnlockSeat()
    {
        return "unLockSeat.html?";
    }

    private    static  function  downlaodStringFromURL($url,$WriteLog=0)
    {
        ini_set("memory_limit","1024M");
        //$url ='http://filmapi.spider.com.cn/v2/epiao/lockSeatList.html?showId=1448285100000251101&cinemaId=JY2511&hallId=01&filmId=201511978408&seatId=5:1&merPrice=40.0000&feePrice=8.0000&parorderId=DD-151123-972E3AE263&mobile=13810934543&activityId=&notifyUrl=&key=epiao&sign=7517f9628a8efdae84096f37ccb8b692&filetype=json';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $response = curl_exec($ch);
        return $response;
    }

    private static  function getParam( $arPara)
    {
        $arUrlPara = array();
        $strSign = "";
        foreach ($arPara as $key =>$v)
        {
            $arUrlPara[] = "$key=$v";
            $strSign = $strSign.$v;
        }

        $strSign = $strSign.self::SpiderKey().self::SpiderValue();
        $arUrlPara[] = "key=".self::SpiderKey();
        $arUrlPara[] = "sign=".md5($strSign);
        $arUrlPara[] = "filetype=".self::filetype;


        return implode("&", $arUrlPara);
    }

    //2.2.6	场次座位信息查询
    public static function Get_Spider_ShowSeatList_Data($showId)
    {
        //将参数按顺序加入odParameter,可选参数可以不添加
        $arPara = array("showId"=>$showId);
        $jsonString = "";
        try
        {
            //将odParameterzhi
            $jsonString = self::downlaodStringFromURL(self::SpiderApiUrl().self::SpiderApiShowSeatList() . self::getParam($arPara));
        }
        catch (Exception $e)
        {

        }
        return $jsonString;
    }

    //2.2.6	锁定座位接口
    public static function Get_Spider_BookSeatingLockSeatList_Data($showId, $cinemaId, $hallId, $filmId, $mobile,
                                                                   $seatId, $feePrice, $parorderId, $partnerPrice)
    {
        //将参数按顺序加入odParameter,可选参数可以不添加
        $arPara = array();
        $arPara["showId"] = $showId;
        $arPara["cinemaId"] = $cinemaId;
        $arPara["hallId"] = $hallId;
        $arPara["filmId"] = $filmId;

        $arPara["seatId"] = $seatId;
        $arPara["merPrice"] = $partnerPrice;
        //$parorderId = 'DD-151123-014B0FD98B';
        $arPara["feePrice"] = $feePrice;
        $arPara["parorderId"] = $parorderId;
        $arPara["mobile"] = $mobile;
        $arPara["activityId"] = "";
        $arPara["notifyUrl"] = "";
        $jsonString = "";
        try
        {
            //将odParameterzhi
            $jsonString = self::downlaodStringFromURL(self::SpiderApiUrl().self::SpiderApiLockSeatList() . self::getParam($arPara), 1);
        }
        catch (Exception $e)
        {

        }
        return $jsonString;
    }

    //2.2.6	确认订单接口
    public static function Get_Spider_ConfirmOrder_Data($parorderId, $mobile)
    {
        //将参数按顺序加入odParameter,可选参数可以不添加
        $arPara = array("orderId"=>$parorderId,"mobile"=>$mobile);
        $jsonString = "";
        try
        {
            //将odParameterzhi
            $jsonString = self::downlaodStringFromURL(self::SpiderApiUrl(). self::SpiderApiConfirmOrder() . self::getParam($arPara),1);
        }
        catch (Exception $e)
        {

        }
        return $jsonString;
    }

    //2.3.4	订单座位解锁接口
    public static function Get_Spider_UnLockSeatingLockSeat_Data($parorderId, $showId, $cinemaId)
    {
        //将参数按顺序加入odParameter,可选参数可以不添加
        $arPara = array(
            "orderId"=>$parorderId
        );

        $jsonString = "";
        try
        {
            //将odParameterzhi
            $jsonString = self::downlaodStringFromURL(self::SpiderApiUrl().self::SpiderApiUnlockSeat() .self::getParam($arPara));
        }
        catch (Exception $e)
        {

        }
        return $jsonString;
    }
    
    //2.3.3	订单状态查询
    public static function Get_Spider_QryOrderStatus_Data( $parorderId)
    {
    	//将参数按顺序加入odParameter,可选参数可以不添加
    	$arPara = array("orderId"=>$parorderId);
    
    	$jsonString = "";
    	try
    	{
    		//将odParameterzhi
    		$jsonString = self::downlaodStringFromURL(self::SpiderApiUrl().self::SpiderApiQryOrderStatus() . self::getParam($arPara));
    	}
    	catch (Exception $e)
    	{
    
    	}
    	return $jsonString;
    }
    
}


        
       

     
       