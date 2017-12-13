<?php

class MaoyanDataAdapterProcess
{
    const interfaceId = 10;
    const endBuytime = 3600;
//xml 结构转换成键值对的dictionary结构
    public static function XmlToDictionary($memberNode)
    {
        $arNode = array();

        foreach ($memberNode as $data)
        {

            try {
                $name = (array)$data->name;
                $value = (array)($data->value->string);
                if (isset($value[0]))
                    $arNode[$name[0]] = $value[0];
            } catch (Exception $ex) {
                continue;
            }

        }

        return $arNode;
    }
//获取选中座位信息
    public static function FillBookSeatingLockSeats($jsonData,$CinemaID=0,$HallID=0){
        $arRetSeat = array();
        $jsonData = json_decode($jsonData, true);
        if($jsonData['code']==0) {
            $dataArray = $jsonData['data'];
            if ($dataArray['bizCode'] == 0) {
                $data = $dataArray['data']['sections'];
                foreach ($data as $v)
                {
                    foreach($v['seats'] as $seat){
                        if($seat['status']=='LK'){
                            $arTemp['SeatStatus'] = 1;
                            $arTemp['SeatId'] = $seat['seatNo'];
                            $arTemp['ColumnId'] = $seat['columnId'];
                            $arTemp['RowId'] = $seat['rowId'];
                            $arRetSeat[] = $arTemp;
                        }
                    }
                }
            }
        }

        return $arRetSeat;
    }
//锁坐
    public static function FillAddOrderResult($jsonData){
        $arOrderResult  = array();
        $arOrderResult['ResultCode'] = ConfigParse::getOrderStatusKey('ORDER_STATUS_Failed');
        try {
            $jsonData = json_decode($jsonData, true);
            if($jsonData['code']==0) {
                $dataArray = $jsonData['data'];
                if ($dataArray['bizCode'] == 0) {
                    $data = $dataArray['data'];
                    if($data['orderStatus']==1){
                        $arOrderResult['ResultCode'] = ConfigParse::getOrderStatusKey('ORDER_STATUS_Successed');
                        $arOrderResult['InterfaceOrderNo'] = $data["orderId"];
                        $arOrderResult['ResultMessage'] = "OrderNo=" . $data["orderId"];
                    }
                }
            }
        } catch (Exception $ex) {
            $arOrderResult['ResultCode'] = ConfigParse::getOrderStatusKey('ORDER_STATUS_Failed');
            $arOrderResult['ResultMessage'] =  "获取错误";
        }
        return $arOrderResult;
    }
//取消接口方订单
    public static function FillCancelOrderResult($jsonData)
    {
        $arResult  = array();
        $arResult['ResultCode'] = ConfigParse::getOrderStatusKey('ORDER_STATUS_Failed');
        try{
            $jsonData = json_decode($jsonData, true);
            if($jsonData['code']==0) {
                $dataArray = $jsonData['data'];
                if ($dataArray['bizCode'] == 0) {
                    $data = $dataArray['data'];
                    //释放座位的状态
                    if($data['orderStatus']==7){
                        $arResult['ResultCode'] = ConfigParse::getOrderStatusKey('ORDER_STATUS_Successed');
                    }
                }
            }
        } catch (Exception $ex) {
            $arResult['ResultCode'] = ConfigParse::getOrderStatusKey('ORDER_STATUS_Failed');
            $arResult['ResultMessage'] = "释放座位失败";
        }
        return $arResult;
    }

    public static function FillOrderTicketResult($jsonData)
    {
        $arOrderResult['OrderStatus'] = ConfigParse::getOrderStatusKey('ORDER_STATUS_Successed');
        try {
            $jsonData = json_decode($jsonData, true);
            if($jsonData['code']==0) {
                $dataArray = $jsonData['data'];
                if ($dataArray['bizCode'] == 0) {
                    $data = $dataArray['data'];
                    //4正在出票中， 5出票成功， 6失败
                    if($data['orderStatus']==5){
                        $arOrderResult['OrderStatus'] = ConfigParse::getOrderStatusKey('ORDER_STATUS_Successed');
                        $arOrderResult['FetchNo'] = $data["ticketCode"];
                    }else{
                        $arOrderResult['OrderStatus'] = ConfigParse::getOrderStatusKey('ORDER_STATUS_Failed');
                    }
                }
            }
        } catch (Exception $ex) {
            $arOrderResult['OrderStatus'] = ConfigParse::getOrderStatusKey('ORDER_STATUS_Failed');
        }

        return $arOrderResult;

    }

    public static function FillApplyTicketResult($jsonData)
    {
        $arOrderResult['OrderStatus'] = ConfigParse::getOrderStatusKey('ORDER_STATUS_Failed');
        try {
            $jsonData = json_decode($jsonData, true);
            if($jsonData['code']==0) {
                $dataArray = $jsonData['data'];
                if ($dataArray['bizCode'] == 0) {
                    $data = $dataArray['data'];
                    //4正在出票中， 5出票成功， 6失败
                    if($data['orderStatus']==5){
                        $arOrderResult['OrderStatus'] = ConfigParse::getOrderStatusKey('ORDER_STATUS_Successed');
                        $arOrderResult['InterfaceValidCode'] = $data["ticketCode"];
                    }else{
                        $arOrderResult['OrderStatus'] = ConfigParse::getOrderStatusKey('ORDER_STATUS_Failed');
                        $arOrderResult['ResultMessage'] =  "出票失败";
                    }
                }
            }
        } catch (Exception $ex) {
            $arOrderResult['OrderStatus'] = ConfigParse::getOrderStatusKey('ORDER_STATUS_Failed');
            $arOrderResult['ResultMessage'] =  "获取错误";
        }

        return $arOrderResult;

    }
}