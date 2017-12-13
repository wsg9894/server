<?php
class GWLProviderProcess
{
    private static $instance;

    public function __construct()
    {

    }
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new GWLProviderProcess();
        }

        return self::$instance;
    }


    public function GetBookSeatingArranges($bookSeatingCinema)
    {
        $arRetArrange = array();
        for ($j = 0; $j < 7; $j++)
        {
            $cinemaId =$bookSeatingCinema[BookSeatingCinema::cinemaId];
            $date =  date('Y-m-d',  time()+$j*24*3600);
            $rawData = GWLRetrieverProcess::Get_Base_FilmShow($cinemaId,$date);
            $arRetArrange = array_merge($arRetArrange,GWLDataAdapterProcess::FillArranges($rawData));
        }
        return $arRetArrange;
    }

    public function  GetBookSeatingLockSeats($bookSeatingArrange)
    {

        $seq_no = $bookSeatingArrange['sRoomMovieInterfaceNo'];
        $CinemaID = $bookSeatingArrange['sCinemaInterfaceNo'];
        $HallID = $bookSeatingArrange['sRoomInterfaceNo'];
        $rawData = GWLRetrieverProcess::Get_Base_SellSeat($seq_no);
        return GWLDataAdapterProcess::FillBookSeatingLockSeats($rawData,$CinemaID,$HallID);
    }


    public function GetCreateOrderResult($seat,$roomMovieNo,$mobile,$epiaoConsumeNo,$epiaoConsumeId,$cinemaNo, $roomNo,$movieNo,$price,$Fee,$settlePrice,$sDimensional,$sLanguage,$iUserID)
    {
    	$arSeatInfo = implode(',',explode('@@',$seat));
        $rawData = GWLRetrieverProcess::Get_Sell_LockSeat($roomMovieNo,$mobile,$sLanguage,$sDimensional,$arSeatInfo,$iUserID);
        return GWLDataAdapterProcess::FillAddOrderResult($rawData);
    }

    /**
     * 通知格瓦拉支付结果
     * @param $interfaceOrderNo
     * @param $price
     * @param $fee
     * @param $mobile
     * @param $consumeNo
     * @param $seat
     * @param $roomMovieId
     * @return array|void
     */
    public function GetConfirmOrderResult($interfaceOrderNo,$price,$fee,$mobile,$consumeNo,$seat,$roomMovieId)
    {
        $arResult  = array();
        $arArrangeInfo = CinemaProcess::getRoomMovieListByiRoommovieID($roomMovieId);
        $mSettlementPrice = $arArrangeInfo['mSettlementPrice'];
        $arSeat = explode('@@',$seat);
        $seatCount = count($arSeat);
        $Amount = $seatCount * $mSettlementPrice;
        $rawData = GWLRetrieverProcess::Get_Sell_PayNotify($interfaceOrderNo,$Amount,$consumeNo);
        $payInfo =  GWLDataAdapterProcess::FillPayNotify($rawData);
        if($payInfo['ResultCode'] == ConfigParse::getOrderStatusKey('ORDER_STATUS_Successed')){
            //这块格瓦拉会订单订单推送
            $arResult['ResultCode'] = ConfigParse::getOrderStatusKey('ORDER_STATUS_Successed');
        }else{
            //一次订单支付通知不成功，异步
            $arResult['ResultCode'] = ConfigParse::getOrderStatusKey('PROCESSED_STATUS_NotProcessed');
        }
        return $arResult;
    }

    /**
     * 取消接口方的订单
     * @param $interfaceOrderNo
     * @param $roomMovieNo
     * @param $cinemaNo
     * @param $mobile
     * @return array
     */
    public function GetCancelOrderResult($interfaceOrderNo,$roomMovieNo,$cinemaNo,$mobile,$iUserId)
    {
        $rawData = GWLRetrieverProcess::Get_Sell_UnLockSeat($interfaceOrderNo, $iUserId);
        return GWLDataAdapterProcess::FillCancelOrderResult($rawData);
    }

    /**
     * 获取取票码
     * @param $orderNo
     * @param $mobile
     * @param $outOrderId
     * @return array
     */
    public function GetBookSeatingOrder($orderNo,$mobile,$outOrderId)
    {
        $rawData = GWLRetrieverProcess::Get_Sell_BuyTicket($orderNo);
        return GWLDataAdapterProcess::FillConfirmOrderResult($rawData);
    }

    /**
     * 查询格瓦拉订单详情
     * @param $orderNo
     */
    public function GetInterFaceOrderInfo($orderNo){
        $rawData = GWLRetrieverProcess::Get_Sell_SearchOrderInfoBySID($orderNo);
        print_r($rawData);die;
    }
}
