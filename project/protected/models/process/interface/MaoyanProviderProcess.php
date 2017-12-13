<?php
class MaoyanProviderProcess
{
    private static $instance;

    public function __construct()
    {

    }
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new MaoyanProviderProcess();
        }

        return self::$instance;
    }

    public function  GetBookSeatingLockSeats($bookSeatingArrange)
    {
        $seq_no = $bookSeatingArrange['sRoomMovieInterfaceNo'];
        $CinemaID = $bookSeatingArrange['sCinemaInterfaceNo'];
        $HallID = $bookSeatingArrange['sRoomInterfaceNo'];
        $rawData = MaoyanRetrieverProcess::Get_Base_SellSeat($seq_no);
        return MaoyanDataAdapterProcess::FillBookSeatingLockSeats($rawData,$CinemaID,$HallID);
    }


    public function GetCreateOrderResult($seat,$roomMovieNo,$mobile,$epiaoConsumeNo,$epiaoConsumeId,$cinemaNo, $roomNo,$movieNo,$price,$Fee,$settlePrice,$sDimensional,$sLanguage,$iUserID)
    {

        $arSeat = explode('@@',$seat);
        $arrSeat = array();
        foreach($arSeat as $v){
            $str1 = explode('|',$v);
            $str2 = explode('*',$str1[1]);
            $str3 = explode(':',$str2[1]);
            $arrSeat[] = array(
                'sectionId'=>$str1[0],
                'seatNo'=>$str2[0],
                'columnId'=>$str3[1],
                'rowId'=>$str3[0],
            );
        }
        $jsonSeat = json_encode(array('count'=>count($arSeat),'list'=>$arrSeat));
        $price = count($arSeat)*$price;
        $settlePrice = count($arSeat)*$settlePrice;
        $rawData = MaoyanRetrieverProcess::Get_Sell_LockSeat($cinemaNo,$roomMovieNo,$jsonSeat,$epiaoConsumeNo,$mobile,$settlePrice,$price);
        return MaoyanDataAdapterProcess::FillAddOrderResult($rawData);
    }

    /**
     * 确认订单-同步
     * @param $interfaceOrderNo
     * @param $price
     * @param $fee
     * @param $mobile
     * @param $consumeNo
     * @param $seat
     * @param $roomMovieId
     * @return mixed
     */
    public function  GetConfirmOrderResult($interfaceOrderNo,$price,$fee,$mobile,$consumeNo,$seat,$roomMovieId)
    {
        $rawData = MaoyanRetrieverProcess::Get_Sell_ApplyTicket($interfaceOrderNo,$consumeNo);
        return MaoyanDataAdapterProcess::FillApplyTicketResult($rawData);
    }

    /**
     * 取消接口方的订单
     * @param $interfaceOrderNo
     * @param $roomMovieNo
     * @param $cinemaNo
     * @param $mobile
     * @return array
     */
    public function GetCancelOrderResult($interfaceOrderNo,$roomMovieNo,$cinemaNo,$mobile,$iUserID)
    {
        $OrderInfo = OrderProcess::getOrderSeatInfoByInFOrderID($interfaceOrderNo);
        $rawData = MaoyanRetrieverProcess::Get_Sell_UnLockSeat($interfaceOrderNo,$OrderInfo['outerOrderId']);
        return MaoyanDataAdapterProcess::FillCancelOrderResult($rawData);
    }


    /**
     * 确认订单-异步
     * @param $orderNo
     * @param $mobile
     * @param $outOrderId
     * @return mixed
     */
    public function GetBookSeatingOrder($orderNo,$mobile,$outOrderId)
    {
        $rawData = MaoyanRetrieverProcess::Get_Sell_ApplyTicket($orderNo,$outOrderId);
        return MaoyanDataAdapterProcess::FillOrderTicketResult($rawData);
    }
}
