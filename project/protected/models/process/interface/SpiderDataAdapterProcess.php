<?php

class SpiderDataAdapterProcess
{
    const interfaceId = 5;
    const endBuytime = 3600;
    public static function FillBookSeatingLockSeats($jsonData)
    {
        $arRetSeat = array();
        $jsonData = json_decode($jsonData, true);
        if ($jsonData["result"] == 0)
        {
            $data = $jsonData["data"];
            return self::getBookSeatingLockSeatList($data);
        }

        return array();
    }
    private static function getBookSeatingLockSeatList($arLockSeat)
    {
        $arRetLockList = array();

        if (empty($arLockSeat["seats"]))
        {
            return $arRetLockList;
        }

        foreach ($arLockSeat["seats"] as $key=>$v)
        {
            $rowId = $v["rowId"];
            $lockColumnIDs = $v["columnIds"];
            $arColumnIDS = explode("|",$lockColumnIDs);
            foreach ($arColumnIDS as $ColumnId)
            {
                $arRetLockList[] = array('ColumnId'=>$ColumnId,
                    'RowId'=>$rowId,
                    'SeatId' => $rowId . ":" . $ColumnId,
                    'SeatStatus'=> 1,
                );
            }

        }

        return $arRetLockList;

    }

    public static  function FillAddOrderResult($jsonData)
    {
        $arOrderResult  = array();
        $jsonData = json_decode($jsonData, true);
        if ($jsonData["result"] == 0)
        {
            $data = $jsonData["data"];
            $arOrderResult['ResultCode'] = 7;
            $arOrderResult['InterfaceOrderNo'] = $data["orderId"];
            $arOrderResult['ResultMessage'] = "OrderNo=" . $data["orderId"];
        }
        else
        {
            $arOrderResult['ResultCode'] = 2;
            $arOrderResult['ResultMessage'] =  "errMsg=" .$jsonData["message"];
        }
        return $arOrderResult;
    }

    public static function  FillConfirmOrderResult($jsonData)
    {
        $arPayOrderResult  = array();
        $jsonData = json_decode($jsonData, true);
        if ($jsonData["result"] == 0)
        {
            $data = $jsonData["data"];
            $arPayOrderResult['ResultCode'] = ConfigParse::getOrderStatusKey('ORDER_STATUS_Successed');
            $arPayOrderResult['InterfaceValidCode'] = $data["confirmationId"];
            if (!empty($data["partnerbookingid"]) && $data["partnerbookingid"] !=$data["confirmationId"])
            {
                $arPayOrderResult['InterfaceValidCode'] = $data["confirmationId"]."*".$data["partnerbookingid"];
            }
            $arPayOrderResult['ResultMessage'] = "ValidCode=" . $data["confirmationId"];
        }
        else
        {
            $arPayOrderResult['ResultCode'] = ConfigParse::getOrderStatusKey('ORDER_STATUS_Failed');
            $arPayOrderResult['ResultMessage'] =  "get valid code wrong";
        }
        return $arPayOrderResult;
    }

    public static function FillCancelOrderResult($jsonData)
    {
        $arResult  = array();
        $jsonData = json_decode($jsonData, true);
        if ($jsonData["result"] == 0)
        {
            $arResult['ResultCode'] = ConfigParse::getOrderStatusKey('ORDER_STATUS_Successed');
        }
        else
        {
            $arResult['ResultCode'] = ConfigParse::getOrderStatusKey('ORDER_STATUS_Failed');
        }
        $arResult['ResultMessage'] = "";
        return $arResult;
    }
    
    public static function  FillOrder($jsonData)
    {
    
    	$arResult  = array();
    	//$arResult[BookSeatingOrder::orderDesc] = '获取取票码失败';
    	//$jsonData = Model_Util::parseContent($jsonData);
    	$jsonData = json_decode($jsonData, true);
    	if ($jsonData["result"] == 0)
    	{
    		$data = $jsonData["data"];
    		$arResult["OrderDesc"] = $data["content"];
    		$arResult["OrderId"] = $data["orderId"];
    		$status = $data['status'];
        
    		if ($status === 'true')
    		{
	    		$arResult["OrderStatus"] = ConfigParse::getOrderStatusKey('ORDER_STATUS_Successed');
	    		$arResult["FetchNo"] = $data["confirmationId"];
	    		if (!empty($data["partnerbookingid"]) && $data["partnerbookingid"]!=$data["confirmationId"])
	    		{
	    			$arResult["FetchNo"] = $data["confirmationId"]."*".$data["partnerbookingid"];
	    		}
    		}
    		else if ($status === 'false')
    		{
    			$arResult["OrderStatus"] = ConfigParse::getOrderStatusKey('ORDER_STATUS_Failed');
    		}
    		else
    		{
    			$arResult["OrderStatus"] = ConfigParse::getOrderStatusKey('ORDER_STATUS_Unknown');
    		}
    
    		$arResult["InterfaceId"] = self::interfaceId ;
    
	    }
	    else
	    {
		    $arResult["OrderStatus"] = ConfigParse::getOrderStatusKey('ORDER_STATUS_Failed');
		    $arResult["OrderDesc"] ="获取验证码失败";
	    }
     
    return $arResult;
    }
}