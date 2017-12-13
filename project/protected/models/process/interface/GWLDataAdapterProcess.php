<?php

class GWLDataAdapterProcess
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
    public static function FillBookSeatingLockSeats($xmlData,$CinemaID=0,$HallID=0){
        $arRetSeat = array();
        try{
//            $xml = simplexml_load_string($xmlData);
            $xml = simplexml_load_string($xmlData, 'SimpleXMLElement', LIBXML_NOCDATA);
//            print_r($xml);die;
            $bookSeatingRoom['sCinemaInterfaceNo'] = $CinemaID;
            $bookSeatingRoom['sRoomInterfaceNo'] = $HallID;
            if(isset($xml->opiLockSeatInfo->lockedseat)){
                //1:1,2,3,4,5,6,7,8,9@2:1,2,3,4,5,6,7,8,9@4:7
                $xmlStruct = $xml->opiLockSeatInfo->lockedseat;
                $arRet = (array)($xmlStruct);
                if(!empty($arRet)){
                    $arLockSeat = explode("@",$arRet[0]);
                    foreach($arLockSeat as $val){
                        $strRet = (string)$val;
                        $arData = explode(':',$strRet);
                        $rowId = $arData[0];
                        $arColumn = explode(",",$arData[1]);
                        foreach($arColumn as $ColumnId){
                            $arTemp['SeatStatus'] = 1;
                            $arTemp['SeatId'] = $rowId . ":" . $ColumnId;
                            $arTemp['ColumnId'] = $ColumnId;
                            $arTemp['RowId'] = $rowId;
                            $arRetSeat[] = $arTemp;
                        }

                    }
                }
            }
        }catch (Exception $e){

        }
        return $arRetSeat;
    }
//锁坐
    public static function FillAddOrderResult($rawData){
        $arOrderResult  = array();
        try {
            $xml = simplexml_load_string($rawData, 'SimpleXMLElement', LIBXML_NOCDATA);
//            print_r($xml);
            if(isset($xml->ticketOrder)){
                $data = (array)$xml->ticketOrder;
                $arOrderResult['ResultCode'] = ConfigParse::getOrderStatusKey('ORDER_STATUS_Successed');
                $arOrderResult['InterfaceOrderNo'] = $data["tradeno"];
                $arOrderResult['ResultMessage'] = "OrderNo=" . $data["tradeno"];
            }else{
                $data1 = (array)$xml;
                $arOrderResult['ResultCode'] = ConfigParse::getOrderStatusKey('ORDER_STATUS_Failed');
                $arOrderResult['ResultMessage'] =  "errMsg=" .$data1["error"].":".$data1["error"];
            }
        } catch (Exception $ex) {
            $arOrderResult['ResultCode'] = ConfigParse::getOrderStatusKey('ORDER_STATUS_Failed');
            $arOrderResult['ResultMessage'] =  "获取错误";
        }
        return $arOrderResult;
    }
//取消接口方订单
    public static function FillCancelOrderResult($xmlData)
    {
        $arResult  = array();
        try{
            $xml = simplexml_load_string($xmlData, 'SimpleXMLElement', LIBXML_NOCDATA);
//            print_r($xml);die;
            $data = (array)$xml;
            if(isset($data['result'])){
                if($data['result']=='success'){
                    $arResult['ResultCode'] = ConfigParse::getOrderStatusKey('ORDER_STATUS_Successed');
                }else{
                    $arResult['ResultCode'] = ConfigParse::getOrderStatusKey('ORDER_STATUS_Failed');
                }
            }else{
                $arResult['ResultCode'] = ConfigParse::getOrderStatusKey('ORDER_STATUS_Failed');
                $arResult['ResultMessage'] =  "errMsg=" . $data["code"].":".$data["error"];
            }
        } catch (Exception $ex) {
            $arResult['ResultCode'] = ConfigParse::getOrderStatusKey('ORDER_STATUS_Failed');
            $arResult['ResultMessage'] = "获取验证码失败";
        }

        return $arResult;
    }
    //支付订单
    public static function FillPayNotify($xmlData){
        $arResult  = array();
        try{
            $xml = simplexml_load_string($xmlData, 'SimpleXMLElement', LIBXML_NOCDATA);
//            print_r($xml);die;
            $data = (array)$xml;
//            print_r($data);
            if(isset($data['result'])){
                if((string)$data['result']=='success'){
//                    echo $data['result'];
                    $arResult['ResultCode'] = ConfigParse::getOrderStatusKey('ORDER_STATUS_Successed');
                }else{
                    $arResult['ResultCode'] = ConfigParse::getOrderStatusKey('ORDER_STATUS_Failed');
                }
            }else{
//                echo 21;die;
                $arResult['ResultCode'] = ConfigParse::getOrderStatusKey('ORDER_STATUS_Failed');
                $arResult['ResultMessage'] =  "errMsg=" . $data["code"].":".$data["error"];
            }
        }catch (Exception $e){

        }
        return $arResult;

    }
//获取取票码
    public static function FillConfirmOrderResult($xmlData){
        $arPayOrderResult  = array();
        try{
            $xml = simplexml_load_string($xmlData, 'SimpleXMLElement', LIBXML_NOCDATA);
            if(isset($xml->takeTicketCodeList)){
                foreach($xml->takeTicketCodeList as $v){
                    foreach($v->takeTicketCode as $val){
                        $arr[] = $val->takeTicketValue;
                    }
                }
                $fetchNo = implode('|',$arr);
                $arPayOrderResult['OrderStatus'] = ConfigParse::getOrderStatusKey('ORDER_STATUS_Successed');
                $arPayOrderResult['FetchNo'] = $fetchNo;
            }else{
                $arPayOrderResult['OrderStatus'] = ConfigParse::getOrderStatusKey('ORDER_STATUS_Failed');
                $arPayOrderResult['OrderDesc'] =  "获取验证码失败";
            }
        } catch (Exception $ex) {
            $arPayOrderResult['OrderStatus'] = ConfigParse::getOrderStatusKey('ORDER_STATUS_Failed');
            $arPayOrderResult['OrderDesc'] = "获取验证码失败";
        }

        return $arPayOrderResult;
    }

}