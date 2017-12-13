<?php
class WangpiaoDataAdapterProcess
{
    const interfaceId = 8;
    const endBuytime = 3600;
    public static function FillBookSeatingLockSeats($jsonData,$CinemaID=0,$HallID=0)
    {
        $arRetSeat = array();
        $jsonData =json_decode($jsonData,true);
        $bookSeatingRoom['sCinemaInterfaceNo'] = $CinemaID;
        $bookSeatingRoom['sRoomInterfaceNo'] = $HallID;
        if ($jsonData["ErrNo"] == 0)
        {
            $data = $jsonData["Data"];

            foreach ($data as $v)
            {
                $arTemp['SeatStatus'] = 1;
                $arTemp['SeatId'] = $v['SeatID'];
                $arTemp['ColumnId'] = 0;
                $arTemp['RowId'] = 0;
                $arRetSeat[] = $arTemp;

            }
        }
        return $arRetSeat;
    }

    public static function FillAddOrderResult($jsonData)
    {
        $arOrderResult  = array();
        try {
            $jsonData = json_decode($jsonData, true);
            if ($jsonData["ErrNo"] == 0)
            {
                $data = $jsonData["Data"][0];
                $arOrderResult['ResultCode'] = ConfigParse::getOrderStatusKey('ORDER_STATUS_Successed');
                $arOrderResult['InterfaceOrderNo'] = $data["SID"];
                $arOrderResult['ResultMessage'] = "OrderNo=" . $data["SID"];
            }
            else
            {
                $arOrderResult['ResultCode'] = ConfigParse::getOrderStatusKey('ORDER_STATUS_Failed');
                $arOrderResult['ResultMessage'] =  "errMsg=" .$jsonData["Msg"];
            }
        } catch (Exception $ex) {
            $arOrderResult['ResultCode'] = ConfigParse::getOrderStatusKey('ORDER_STATUS_Failed');;
            $arOrderResult['ResultMessage'] =  "获取错误";
        }

        return $arOrderResult;
    }

    public static function FillApplyTicketResult($jsonData)
    {
        $arOrderResult  = array();
        try {
            $jsonData = json_decode($jsonData, true);
            if ($jsonData["ErrNo"] == 0)
            {
                $data = $jsonData["Data"][0];
                $arOrderResult['ResultCode'] = ConfigParse::getOrderStatusKey('ORDER_STATUS_Successed');
                $arOrderResult['PayNo'] = $data['PayNo'];
                $arOrderResult['SID'] = $data["SID"];
            }
            else
            {
                $arOrderResult['ResultCode'] = ConfigParse::getOrderStatusKey('ORDER_STATUS_Failed');
                $arOrderResult['ResultMessage'] =  "errMsg=" .$jsonData["Msg"];
            }
        } catch (Exception $ex) {
            $arOrderResult['ResultCode'] = ConfigParse::getOrderStatusKey('ORDER_STATUS_Failed');
            $arOrderResult['ResultMessage'] =  "获取错误";
        }

        return $arOrderResult;

    }

    public static function FillConfirmOrderResult($jsonData)
    {
        $arPayOrderResult  = array();
        try{
            $jsonData = json_decode($jsonData, true);
            if ($jsonData["ErrNo"] == 0)
            {
                $data = $jsonData["Data"][0];
                $result = $data["Result"];
                if($result == 'true'){
                    $arPayOrderResult['ResultCode'] = ConfigParse::getOrderStatusKey('ORDER_STATUS_Successed');
                }else{
                    $arPayOrderResult['ResultCode'] = ConfigParse::getOrderStatusKey('ORDER_STATUS_Failed');
                }
            }
            else
            {
                $arPayOrderResult['ResultCode'] = ConfigParse::getOrderStatusKey('ORDER_STATUS_Failed');
                //$arPayOrderResult['ResultMessage'] =  "errMsg=" . $jsonData["Message"];
            }
        } catch (Exception $ex) {
            $arPayOrderResult['ResultCode'] = ConfigParse::getOrderStatusKey('ORDER_STATUS_Failed');
            $arPayOrderResult['ResultMessage'] = "获取验证码失败";
        }

        return $arPayOrderResult;
    }

    public static function FillCancelOrderResult($jsonData)
    {
        $arResult  = array();
        try{
            $jsonData = json_decode($jsonData, true);
            if ($jsonData["ErrNo"] == 0)
            {
                $data = $jsonData["Data"][0];
                $result = $data["Result"];
                if($result == 'true'){
                    $arResult['ResultCode'] = ConfigParse::getOrderStatusKey('ORDER_STATUS_Successed');
                }else{
                    $arResult['ResultCode'] = ConfigParse::getOrderStatusKey('ORDER_STATUS_Failed');
                }
            }
            else
            {
            	if(FALSE==isset($jsonData["Message"]) or empty($jsonData["Message"]))
            	{
            		$jsonData["Message"] = "未知";
            	}
                $arResult['ResultCode'] = ConfigParse::getOrderStatusKey('ORDER_STATUS_Failed');
                $arResult['ResultMessage'] =  "errMsg=" . $jsonData["Message"];
            }
        } catch (Exception $ex) {
            $arResult['ResultCode'] = ConfigParse::getOrderStatusKey('ORDER_STATUS_Failed');
            $arResult['ResultMessage'] = "获取验证码失败";
        }

        return $arResult;
    }

    public static function FillOrder($jsonData)
    {
        $arResult  = array();
        $arResult['OrderStatus'] = ConfigParse::getOrderStatusKey('ORDER_STATUS_Failed');
        try{
            $jsonData = json_decode($jsonData, true);;
            if ($jsonData["ErrNo"] == "0")
            {
                if(!empty($jsonData["Data"])){
                    $data = $jsonData["Data"][0];
                    $PayFlag = $data["PayFlag"];
                    $TicketID = $data["TicketID"];
                    $Pwd = $data["Pwd"];
                    $Stype= $data['Stype'];
                    if($PayFlag == 3){

                        $arResult['OrderStatus'] = ConfigParse::getOrderStatusKey('ORDER_STATUS_Successed');
                        $arResult['data'] = $Stype;
                        //$arResult[BookSeatingOrder::fetchNo] = $TicketID;
                        switch ($Stype)
                        {
                            case 0:
                            case 1:
                            case 4:
                            case 5:
                            case 7:
                            case 10:
                            case 11:
                            case 13:
                                //$arResult[BookSeatingOrder::fetchNo] = $Stype.'|'.$TicketID.'|'.$Pwd;
                                $arResult['FetchNo'] = $TicketID.'|'.$Pwd;
                                break;
                            case 12:
                                //Stype是12，双单码
                                $pwd = substr($Pwd,-6);
                                $arResult['FetchNo'] = $TicketID.'|'.$pwd.'*'.$Pwd;
                                break;
                            case 2:
                            case 6:
                            case 8:
                                $arResult['FetchNo'] = $Pwd;
                                break;
                            case 3:
                                $arResult['FetchNo'] = '14006782005|'.$Pwd;
                                break;
                            case 14:
                            {
                                $arCode = explode(',', $TicketID);
                                if ($arCode[0] == 3)
                                {
                                    $arResult['FetchNo'] = $arCode[2];
                                }
                                else
                                {
                                    $arResult['FetchNo'] = $arCode[1].'|'.$arCode[2];
                                }
                            }
                                break;
                            case 15:
                                $sid = substr($data['SID'],-6);
                                $arResult['FetchNo'] = $sid.'|'.$Pwd;
                                break;
                            case 16:
                                if (empty($TicketID))
                                {
                                    $arResult['FetchNo'] = $Pwd;
                                }
                                else {
                                    $arResult['FetchNo'] = $TicketID.'|'.$Pwd;
                                }
                                break;
                            case 18:
                            {
                                $sPhone = $data["Mobile"];
                                $arResult['FetchNo'] = $sPhone.'|'.$Pwd.'*'.$TicketID.'|'.$Pwd;
                            }
                                break;

                            case 19:		//猫眼验票，add at 20170216 by lzz
                            {
//                                 $tmpFetchArr = explode('验证码', $TicketID);
//                                 $tmpFetchArr[0] = str_replace('序列号', '', $tmpFetchArr[0]);
//                                 $arResult['FetchNo'] = $tmpFetchArr[0].'|'.$tmpFetchArr[1];
                                
                                //update at 20170717 by lzz
                                $tmpFetchArr = explode('验证码', $TicketID);
                                $tmpFetchArr[0] = str_replace('序列号', '', $tmpFetchArr[0]);
                                $tmpFetchArr[0] = str_replace('订单号', '', $tmpFetchArr[0]);
                                $tmpFetchArr[0] = str_replace('取票号', '', $tmpFetchArr[0]);
                                
                                if(empty($tmpFetchArr[0]))
                                {
                                	$arResult['FetchNo'] = $tmpFetchArr[1];
                                }else{
                                	$arResult['FetchNo'] = $tmpFetchArr[0].'|'.$tmpFetchArr[1];
                                }
                                
                            }
                                break;
                        }

                    }else{

                    }
                }

            }
            else {
                $arResult['OrderStatus'] = ConfigParse::getOrderStatusKey('ORDER_STATUS_Failed');
                $arResult['OrderDesc'] = $jsonData["result"];
            }
        } catch (Exception $ex) {
            $arResult['OrderStatus'] = ConfigParse::getOrderStatusKey('ORDER_STATUS_Failed');
            $arResult['OrderDesc'] ="获取验证码失败";
        }

        return $arResult;
    }
}
