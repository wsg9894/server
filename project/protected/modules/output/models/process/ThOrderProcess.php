<?php
/**
 * Created by PhpStorm.
 * User: ylp
 * Date: 2017/9/4
 * Time: 14:19
 */
class ThOrderProcess
{
    //接口请求地址
    const ThUrl = 'https://center.ingcore.com/';

    //e票网标识
    const EPW = '2e53bc498d5411e7ab6300163e2e452e';

    const EPWPAYURL = 'http://m.epiaowang.com/project/index.php?r=output/Site/PayNotify';

    const EPWURL = 'http://m.epiaowang.com';

    //上海银行
    private static $arrCompanyID = array(
        'shanghang'=>'06fddf3d8d5411e7ab6300163e2e452e'
    );

    public static $arrOrderSratus = array(
        //成功订单
        'successOrder' => 100000,
        //过期订单
        'timeOverOrder' => 100001,
        //已结束订单
        'endOrder' => 100002,
        //已取消订单
        'cancelOrder' => 100003,
        //支付完成，但未出票
        'payOrder' => 100004,
        //已退款订单
        'refundOrder' => 100005,
        //订单未支付
        'noPay'=> 100006,
    );

    private static $arrMethod = array(
        'create'=>array(
            'method'=>'createOrder',
            'methodUrl'=>'order/createOrder.do'
        ),
        'update'=>array(
            'method'=>'updateOrder',
            'methodUrl'=>'order/updateOrder.do'
        ),
        'getCode'=>array(
            'method'=>'updateCode',
            'methodUrl'=>'order/updateCode.do'
        ),
        'getUserInfo'=>'resDecode.do',
        'orderpay'=>array(
            'method'=>'orderPay',
            'methodUrl'=>'orderPay.do'
        )
    );
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
     * 创建订单接口
     * @param $ThMovieID
     * @param $ThMovieName
     * @param $orderId
     * @param $iCinemaID
     * @param $iMovieID
     * @param $mPrice
     * @param $iRoomMovieID
     * @param $sPhone
     * @param $sRoomName
     * @param $sDimensional
     * @param $sLanguage
     * @param $sInterfaceValidCode
     * @param $dPlayTime
     * @param $orderInfo
     * @param $iownSeats
     * @return mixed
     */
    public static function createThOrder($ThMovieID,$ThMovieName,$orderId,$iCinemaID,$iMovieID,$mPrice,$iRoomMovieID,$sPhone,$sRoomName,$sDimensional,$sLanguage,$sInterfaceValidCode,$dPlayTime,$orderInfo,$iownSeats,$sMoviePic=''){
        $param = array(
            'flag'=>self::EPW,
            'companyId'=>self::$arrCompanyID['shanghang'],
            'key'=> self::createKey(self::$arrMethod['create']['method']),
            'movieId'=> $ThMovieID,
            'movieName'=> $ThMovieName,
            'orderId'=> GeneralFunc::substrOrderID($orderId),
            'iCinemaID'=> $iCinemaID,
            'iMovieID'=> $iMovieID,
            'mPrice'=> $mPrice,
            'iRoomMovieID'=> $iRoomMovieID,
            'sPhone'=> $sPhone,
            'sRoomName'=> $sRoomName,
            'sDimensional'=> $sDimensional,
            'sMoviePic'=> self::EPWURL.MovieProcess::getMovieInfoByMovieID($iMovieID)['sSmallImageUrl'],
            'sLanguage'=> $sLanguage,
            'sInterfaceValidCode'=> $sInterfaceValidCode,
            'dCreateTime'=> date('Y-m-d H:i:s',time()),
            'dPlayTime'=> $dPlayTime,
            'orderStatus'=> self::$arrOrderSratus['noPay'],
            'orderInfo'=> $orderInfo,
            'iownSeats'=> $iownSeats,
        );
        $ret = self::MyHttpPost(self::ThUrl.self::$arrMethod['create']['methodUrl'],$param);
        return $ret;
    }

    /**
     * 修改订单状态
     * @param $orderId
     * @param $orderStatus
     * @return mixed
     */
    public static function updateThOrder($orderId,$orderStatus){
        $param = array(
            'key'=> self::createKey(self::$arrMethod['update']['method']),
            'orderId'=> GeneralFunc::substrOrderID($orderId),
            'orderStatus'=> $orderStatus,
        );
        $ret = self::MyHttpPost(self::ThUrl.self::$arrMethod['update']['methodUrl'],$param);
        return $ret;
    }

    /**
     * 更新取票码
     * @param $orderId
     * @param $code
     * @return mixed
     */
    public static function getCode($orderId,$code){
        $param = array(
            'key'=> self::createKey(self::$arrMethod['getCode']['method']),
            'orderId'=> GeneralFunc::substrOrderID($orderId),
            'code'=> $code,
        );
        $ret = self::MyHttpPost(self::ThUrl.self::$arrMethod['getCode']['methodUrl'],$param);
        return $ret;
    }

    /**
     * 获取太和用户信息
     * @param $param
     * @return mixed
     */
    public static function getUserInfo($param){
        return self::MyHttpPost(self::ThUrl.self::$arrMethod['getUserInfo'],$param);
    }

    /**
     * 太和支付
     * @param $orderId
     * @param $totalAmount
     * @return mixed
     */
    public static function orderPay($orderId,$totalAmount,$sPhone){
        $arrParam = array(
            'key'=>self::createKey(self::$arrMethod['orderpay']['method']),
            'orderNo'=>GeneralFunc::substrOrderID($orderId),
            'orderAmount'=>$totalAmount,
            'phoneNo'=>$sPhone,
            'callBackUrl'=>self::EPWPAYURL,
        );
        $value = array();
        foreach($arrParam as $key => $val){
            $value[] = $key.'='.$val;
        }
        $value =  implode('&',$value);
        GeneralFunc::writeLog('paySeatOnlineOrder1,'.self::ThUrl.self::$arrMethod['orderpay']['methodUrl'].'?'.$value, Yii::app()->getRuntimePath().'/H5yii/');
        $PayUrl = self::ThUrl.self::$arrMethod['orderpay']['methodUrl'].'?'.$value;
        header('Location: '.$PayUrl);
    }


    private static function createKey($method){
//        echo $method."@".Yii::app()->params['taihe_Partner'];die;
        return md5($method."@".Yii::app()->params['taihe_Partner']);
    }

    private static function MyHttpPost($url,$postDataStr){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
//        echo $url;die;
        curl_setopt($ch, CURLOPT_HEADER,0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postDataStr);
//        print_r($postDataStr);die;
        $response = curl_exec($ch);
//        print_r($response);die;
        curl_close($ch);
        GeneralFunc::writeLog($url.":".$response, Yii::app()->getRuntimePath().'/H5yii/');
//        echo $response;exit(0);
        return json_decode($response,true);
    }

    public static function MyHttpGet($url){
        //初始化
        $curl = curl_init();
        //设置抓取的url
        curl_setopt($curl, CURLOPT_URL, $url);
        //设置头文件的信息作为数据流输出
        curl_setopt($curl, CURLOPT_HEADER, 1);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //执行命令
        $data = curl_exec($curl);
        //关闭URL请求
        curl_close($curl);
        //显示获得的数据
        return $data;
    }
}