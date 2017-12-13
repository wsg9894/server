<?php
class WangpiaoProviderProcess
{
    private static $instance = null;

    private $interfaceId= 8;

    public function __construct()
    {

    }


    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new WangpiaoProviderProcess();
        }

        return self::$instance;
    }
    public function  GetBookSeatingLockSeats($bookSeatingArrange)
    {
        $seq_no = $bookSeatingArrange['sRoomMovieInterfaceNo'];
        $CinemaID = $bookSeatingArrange['sCinemaInterfaceNo'];
        $HallID = $bookSeatingArrange['sRoomInterfaceNo'];
        $rawData = WangpiaoRetrieverProcess::Get_Base_SellSeat($CinemaID, $seq_no);
        return WangpiaoDataAdapterProcess::FillBookSeatingLockSeats($rawData,$CinemaID,$HallID);
    }

    public function GetCreateOrderResult($seat,$roomMovieNo,$mobile,$epiaoConsumeNo,$epiaoConsumeId,$cinemaNo, $roomNo
    		,$movieNo,$price,$Fee,$settlePrice,$sDimensional,$sLanguage,$iUserID=0)
    {
        $arSeat = explode('@@',$seat);
        $seatArray = $this->translateSeats($cinemaNo,$roomNo,$arSeat);
        $seats = implode('|', $seatArray);
        $rawData =WangpiaoRetrieverProcess::Get_Sell_LockSeat($cinemaNo,$roomMovieNo,$seats,$mobile);
        return WangpiaoDataAdapterProcess::FillAddOrderResult($rawData);
    }

    public function translateSeats($iCinemaNo,$iRoomNo,$seatsId)
    {
        $roomInfo = CinemaProcess::getRoomInfobyInterfaceId(ConfigParse::getInterfaceKey('InterfaceType_Wangpiao'), $iCinemaNo, $iRoomNo);
        $sSeatInfo =  json_decode($roomInfo[0]['sSeatInfo'],true);
        $seatInfo = $sSeatInfo['seatinfo'];
        $seatsIndex =array();

        foreach ($seatsId as $seatId) {
            $key = array_search($seatId, $this->array_column($seatInfo, 'SeatNo'));
            $seatsIndex[] =$seatInfo[$key]['SeatIndex'];
        }
        return $seatsIndex;
    }

    public function  GetConfirmOrderResult($interfaceOrderNo,$price,$fee,$mobile,$consumeNo,$seat,$roomMovieId,$outOrderId='')
    {
        $arArrangeInfo = CinemaProcess::getRoomMovieListByiRoommovieID($roomMovieId);
        $mSettlementPrice = $arArrangeInfo['mSettlementPrice'];
        $arSeat = explode('@@',$seat);
        $seatCount = count($arSeat);
        $Amount = $seatCount * $mSettlementPrice;
        $UserAmount = $seatCount * $price;

        $rawData = WangpiaoRetrieverProcess::Get_Sell_ApplyTicket($interfaceOrderNo, 9998, 0, $mobile, 2, $Amount, $UserAmount, 1);
        $payInfo = WangpiaoDataAdapterProcess::FillApplyTicketResult($rawData);

        if($payInfo['ResultCode'] == ConfigParse::getOrderStatusKey('ORDER_STATUS_Successed')){
            $rawData = WangpiaoRetrieverProcess::Get_Sell_BuyTicket($interfaceOrderNo, $payInfo['PayNo'], $consumeNo);
            return WangpiaoDataAdapterProcess::FillConfirmOrderResult($rawData);
        }else{
            return;
        }
    }

    public function GetCancelOrderResult($interfaceOrderNo,$roomMovieNo,$cinemaNo,$mobile,$iUserId)
    {
        $rawData = WangpiaoRetrieverProcess::Get_Sell_UnLockSeat($interfaceOrderNo);
        return WangpiaoDataAdapterProcess::FillCancelOrderResult($rawData);
    }

    function array_column($input, $column_key, $index_key = null) {
        $arr = array_map(function($d) use ($column_key, $index_key) {
            if (!isset($d[$column_key])) {
                return null;
            }
            if ($index_key !== null) {
                return array($d[$index_key] => $d[$column_key]);
            }
            return $d[$column_key];
        }, $input);

        if ($index_key !== null) {
            $tmp = array();
            foreach ($arr as $ar) {
                $tmp[key($ar)] = current($ar);
            }
            $arr = $tmp;
        }
        return $arr;
    }

    public function GetBookSeatingOrder($orderNo,$mobile,$outOrderId)
    {
        $rawData = WangpiaoRetrieverProcess::Get_Sell_SearchOrderInfoBySID($orderNo);
        return WangpiaoDataAdapterProcess::FillOrder($rawData);
    }
}
