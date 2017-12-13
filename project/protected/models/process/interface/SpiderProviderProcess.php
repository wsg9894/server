<?php
class SpiderProviderProcess implements BaseProviderProcess
{
    private static $instance = null;
    private $interfaceId= 5;

    public function __construct()
    {

    }


    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new SpiderProviderProcess();
        }

        return self::$instance;
    }
    public function GetBookSeatingLockSeats($bookSeatingArrange)
    {
        $rawData = SpiderRetrieverProcess::Get_Spider_ShowSeatList_Data($bookSeatingArrange['sRoomMovieInterfaceNo']);
        return SpiderDataAdapterProcess::FillBookSeatingLockSeats($rawData);
    }
    //��������ѡ��
    public function GetCreateOrderResult($seat,$roomMovieNo,$mobile,$epiaoConsumeNo,$epiaoConsumeId,
                                         $cinemaNo, $roomNo,$movieNo,$price,$Fee,$settlePrice)
    {
        $sbFees = array();

        $arSeat = explode('@@',$seat);
        $sbSeats = implode('|', $arSeat);
        foreach ($arSeat as $key=>$v)
        {
            $sbFees[] = $Fee;
        }
        $rawData = SpiderRetrieverProcess::Get_Spider_BookSeatingLockSeatList_Data($roomMovieNo,$cinemaNo,
            $roomNo, $movieNo,$mobile, $sbSeats,implode("|", $sbFees), $epiaoConsumeNo,$settlePrice);
        return SpiderDataAdapterProcess::FillAddOrderResult($rawData);
    }
    //����ӿڷ�������Ϣ
    public function GetConfirmOrderResult($interfaceOrderNo,$price,$fee,$mobile,$consumeNo,$seat,$roomMovieId)
    {
        $rawData = SpiderRetrieverProcess::Get_Spider_ConfirmOrder_Data($interfaceOrderNo, $mobile);
        return SpiderDataAdapterProcess::FillConfirmOrderResult($rawData);
    }

    public function GetCancelOrderResult($interfaceOrderNo,$roomMovieNo,$cinemaNo,$mobile)
    {
        $rawData = SpiderRetrieverProcess::Get_Spider_UnLockSeatingLockSeat_Data($interfaceOrderNo, $roomMovieNo, $cinemaNo);
        return SpiderDataAdapterProcess::FillCancelOrderResult($rawData);
    }
    
    public function GetBookSeatingOrder($orderId, $mobile,$outOrderId)
    {
    	$rawData = SpiderRetrieverProcess::Get_Spider_QryOrderStatus_Data($orderId);
    	return SpiderDataAdapterProcess::FillOrder($rawData);
    }
    
}
