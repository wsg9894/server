<?php

/**
 * OOrderProcess - 订单操作类
 * @author ylp
 * @version V1.0
 */


class OOrderProcess
{
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

    public static function createSeatOrder($iUserID,$iRoommovieID,$sendPhone,$iSelectedCount,$seatInfo,$orderInfo,$fromClient,$returnUrl,$iHuoDongItemID,$type,$isWeChatapplet){
        //1.取消未支付的在线选座订单
        self::cancelSeatOnlineOrder($iUserID);
//		echo "ok";die;
        $outerOrderId=OrderProcess::createOuterOrderId();
        $roomMovieInfo=CinemaProcess::getRoomMovieListByiRoommovieID($iRoommovieID);
        $userOrderInfo=array('iUserID'=>$iUserID,
            'outerOrderId'=>$outerOrderId,
            'mPrice'=>$roomMovieInfo["huodongPrice"],
            'totalPrice'=>$iSelectedCount*$roomMovieInfo["huodongPrice"],
            'orderInfo'=>$orderInfo,
            'sendPhone'=>$sendPhone,
            'fromClient'=>$fromClient,
            'returnUrl'=>$returnUrl,
            'orderType'=>  $type,
            'orderStatus'=>ConfigParse::getPayStatusKey('orderNoPay'),
            'iownSeats'=> count(explode('@@', $seatInfo)),
            'iHuoDongItemID'=>$iHuoDongItemID,
        );
        $userOrderSeatInfo=array(
            'iUserId'=>$iUserID,
            'outerOrderId'=>$outerOrderId,
            'sCinemaInterfaceNo'=>$roomMovieInfo["sCinemaInterfaceNo"],
            'sMovieInterfaceNo'=>$roomMovieInfo["sMovieInterfaceNo"],
            'sRoomInterfaceNo'=>$roomMovieInfo["sRoomInterfaceNo"],
            'sRoomMovieInterfaceNo'=>$roomMovieInfo["sRoomMovieInterfaceNo"],
            'sSeatInfo'=>$seatInfo,
            'sInterfaceOrderNo'=>'',
            'sInterfaceValidCode'=>'',
            'mFee'=>$roomMovieInfo["mFee"],
            'sCinemaName'=>$roomMovieInfo["sCinemaName"],
            'iCinemaId'=>$roomMovieInfo["iEpiaoCinemaID"],
            'iRoomID'=>$roomMovieInfo["iRoomID"],
            'sRoomName'=>$roomMovieInfo["sRoomName"],
            'sMovieName'=>$roomMovieInfo["sMovieName"],
            'iMovieID'=>$roomMovieInfo["iMovieID"],
            'iInterfaceID'=>$roomMovieInfo["iInterfaceID"],
            'sIMax'=>$roomMovieInfo["sIMax"],
            'sDimensional'=>$roomMovieInfo["sDimensional"],
            'sLanguage'=>$roomMovieInfo["sLanguage"],
            'iRoommovieID'=>$iRoommovieID,
            'sPhone'=>$sendPhone,
            'mPrice'=>$roomMovieInfo["huodongPrice"],
            'status'=>ConfigParse::getPayStatusKey('orderNoPay'),
            'dPlayTime'=>$roomMovieInfo['dBeginTime'],
            'mSettingPrice' =>$roomMovieInfo['mSettlementPrice'],
            'isWeChatapplet' =>$isWeChatapplet,
        );
        //插入数据库orders表
        try{
            $orderId = self::insertUserOrderInfo($userOrderInfo);
            self::insertUserOrderSeatInfo($userOrderSeatInfo);
        }
        catch(Exception $e)
        {
            return array("ok"=>false,"data"=>$e);
        }
        $userOrderInfo['orderId'] = $orderId;
        return array("ok"=>true,"data"=> $userOrderInfo);
    }

    /**
     * 创建新的订单的时候把旧的订单改成取消状态
     * @param $iUserID
     * @return array
     */
    public static function cancelSeatOnlineOrder($iUserID){
        GeneralFunc::writeLog('cancelSeatOnlineOrder1'.$iUserID, Yii::app()->getRuntimePath().'/H5yii/');
        $arOrderList = OOrderProcess::getUserOrderList($iUserID);
        foreach ($arOrderList as $v)
        {
            if($v['orderStatus']==10101 && $v['orderType']==100001){
                $outerOrderId = $v['outerOrderId'];
                if(empty($outerOrderId)){
                    GeneralFunc::writeLog('cancelSeatOnlineOrder2'.$outerOrderId.$iUserID, Yii::app()->getRuntimePath().'/H5yii/');
                    return array("ok"=>false,'err_code'=>ERROR_TYPE_ORDER_NOORDERERROR,'error'=>'订单不存在');
                }
                $orderInfo = self::getOrderSeatInfoByOuterOrderId($outerOrderId);
                if (empty($orderInfo))
                {
                    GeneralFunc::writeLog('cancelSeatOnlineOrder2'.$outerOrderId.$iUserID, Yii::app()->getRuntimePath().'/H5yii/');
                    return array("ok"=>false,'err_code'=>ERROR_TYPE_ORDER_NOORDERERROR,"error"=>"该用户下的订单不存在");
                }
                if ($orderInfo['iUserId'] != $iUserID)
                {
                    GeneralFunc::writeLog('cancelSeatOnlineOrder2'.$outerOrderId.$iUserID, Yii::app()->getRuntimePath().'/H5yii/');
                    return array("ok"=>false,'err_code'=>ERROR_TYPE_ORDER_NOORDERERROR,'error'=>'订单信息与当前用户不符');
                }
                if ($orderInfo['orderStatus'] != 10101)
                {
                    GeneralFunc::writeLog('cancelSeatOnlineOrder2'.$outerOrderId.$iUserID, Yii::app()->getRuntimePath().'/H5yii/');
                    return array("ok"=>false,'err_code'=>ERROR_TYPE_ORDER_NOORDERERROR,'error'=>'订单状态不对，无法取消');
                }
                $updateOrderInfo['orderStatus']=  10206;
                $updateOrderInfo['closeTime']=  date('Y-m-d H:i:s');
                $updateOrderInfo['orderInfo']=$orderInfo['orderInfo']."订单已结束";
                $updateOrderInfo['outerOrderId']=$outerOrderId;
                self::updateUserOrderInfo($updateOrderInfo);
                GeneralFunc::writeLog('cancelSeatOnlineOrder,updateUserOrderInfo'.$outerOrderId, Yii::app()->getRuntimePath().'/H5yii/');
                $updateSeatOrder['iUserId']=$iUserID;
                $updateSeatOrder['outerOrderId']=$outerOrderId;
                $updateSeatOrder['status']=10206;
                self::updateOrderSeatByOrderInfo($iUserID,$updateSeatOrder);
                GeneralFunc::writeLog('cancelSeatOnlineOrder,updateOrderSeatByOrderInfo'.$outerOrderId, Yii::app()->getRuntimePath().'/H5yii/');
                if(!empty($orderInfo['sInterfaceOrderNo'])){
                    OCinemaInterfaceProcess::GetCancelOrderResult($outerOrderId);
                }
                GeneralFunc::writeLog('cancelSeatOnlineOrder,GetCancelOrderResult'.$outerOrderId, Yii::app()->getRuntimePath().'/H5yii/');
                $userPayOrderInfo['status'] = 10206;
                $userPayOrderInfo['outerOrderId'] = $outerOrderId;
                self::updateUserPayOrderInfo($userPayOrderInfo);
                GeneralFunc::writeLog('cancelSeatOnlineOrder,updateUserPayOrderInfo'.$outerOrderId, Yii::app()->getRuntimePath().'/H5yii/');
                //修改太和订单
                ThOrderProcess::updateThOrder($outerOrderId,ThOrderProcess::$arrOrderSratus['endOrder']);
                GeneralFunc::writeLog('cancelSeatOnlineOrder,updateThOrder'.$outerOrderId, Yii::app()->getRuntimePath().'/H5yii/');
                return array('ok'=>true);
            }
        }
    }

    /**
     * 获取用户订单
     * @param $iUserID
     * @return array
     */
    public static function getUserOrderList($iUserID){
        $SQL = sprintf("SELECT * FROM {{out_orders}} WHERE iUserId=%s and orderStatus=10101 order by orderId DESC ", $iUserID);
        return DbUtil::queryAll($SQL);
    }

    /**
     * 根据订单号获取到订单信息
     * @param $outerOrderId
     * @return array
     */
    public static function getOrderSeatInfoByOuterOrderId($outerOrderId)
    {
        $arSeatInfo = self::getOrderSeatInfo($outerOrderId);
        $arOrderInfo = self::getOrderInfo($outerOrderId);
        return array_merge($arSeatInfo,$arOrderInfo);
    }

    /**
     * 通过自增orderid获取订单信息
     * @param $orderId
     * @return array
     */
    public static function getThOrderInfo($orderId){
        $SQL = sprintf("select * from {{out_orders}} where orderId='%s'",$orderId);
        return DbUtil::queryRow($SQL);
    }

    /**
     * 通过截取之后的订单号获取订单信息
     * @param $orderId
     * @return array
     */
    public static function getThSubOrderInfo($orderId){
        $SQL = sprintf("select * from {{out_orders}} where right(outerOrderId,20)='%s'",$orderId);
        return DbUtil::queryRow($SQL);
    }

    /**
     * 获取order_seat表数据
     * @param $outerOrderId
     * @return array
     */
    public static function getOrderSeatInfo($outerOrderId)
    {
        $SQL = sprintf("select * from {{out_order_seat}} where outerOrderId='%s'",$outerOrderId);
        return DbUtil::queryRow($SQL);
    }

    /**
     * 获取orders表数据
     * @param $outerOrderId
     * @return array
     */
    public static function getOrderInfo($outerOrderId)
    {
        $SQL = sprintf("select * from {{out_orders}} where outerOrderId='%s' ",$outerOrderId);
        return DbUtil::queryRow($SQL);
    }

    /**
     * 修改orders表数据
     * @param $userOrderInfo
     * @return mixed
     */
    public static function updateUserOrderInfo($userOrderInfo)
    {
        $subSql = array();
        foreach($userOrderInfo as $key =>$v)
        {
            if(!empty($v)){
                $subSql[] = "$key='".mysql_escape_string($v)."'";
            }
        }
        $SQL = sprintf("Update {{out_orders}} set %s  where outerOrderId='%s'",implode(',', $subSql),$userOrderInfo["outerOrderId"]);
        return DbUtil::execute($SQL);
    }

    /**
     * 更新order_seat表数据
     * @param $iUserID
     * @param $orderSeatInfo
     * @return array
     */
    static function updateOrderSeatByOrderInfo($iUserID, $orderSeatInfo)
    {
        if($iUserID!=$orderSeatInfo[ConfigParse::getOrderSeatInfoKey('userId')])
        {
            return array("ok"=>false,'err_code'=>201);
        }
        $subSql = array();
        foreach($orderSeatInfo as $key =>$v)
        {
            if(!empty($v)){
                $subSql[] = "$key='".mysql_escape_string($v)."'";
            }
        }
        $SQL = sprintf("update {{out_order_seat}} set %s  where outerOrderId='%s'",implode(',', $subSql),$orderSeatInfo["outerOrderId"]);
        return array("ok"=>true,'err_code'=>DbUtil::execute($SQL));
    }

    /**
     * 更新paylog表
     * @param $userPayOrderInfo
     * @return mixed
     */
    public static function updateUserPayOrderInfo($userPayOrderInfo)
    {
        $subSql = array();
        foreach($userPayOrderInfo as $key =>$v)
        {
            if(!empty($v)){
                $subSql[] = "$key='".mysql_escape_string($v)."'";
            }
        }
        $SQL = sprintf("update {{out_paylog}} set %s where outerOrderId='%s'",implode(',', $subSql),$userPayOrderInfo["outerOrderId"]);
        return DbUtil::execute($SQL);
    }

    //插入用户订单 传入参数 订单数组
    public static function insertUserOrderInfo($userOrderInfo)
    {

        if(empty($userOrderInfo))
        {
            return array();
        }
        $subSql = array();
        $csubSql=array();
        foreach($userOrderInfo as $key =>$v)
        {
            $subSql[] = "'".mysql_escape_string($v)."'";
            $csubSql[]=$key;
        }
        $SQL = sprintf("insert into  {{out_orders}} (%s) VALUES(%s)",implode(',', $csubSql),implode(',', $subSql));
        DbUtil::execute($SQL);
        return DbUtil::lastInsertID();
    }

    //插入用户订单 传入参数 订单数组
    public static function insertUserOrderSeatInfo($OrderSeatInfo)
    {
        if(empty($OrderSeatInfo))
        {
            return array();
        }
        $subSql = array();
        $csubSql=array();
        foreach($OrderSeatInfo as $key =>$v)
        {
            $subSql[] = "'".mysql_escape_string($v)."'";
            $csubSql[]=$key;
        }
        $SQL = sprintf("insert into  {{out_order_seat}} (%s) VALUES(%s)",implode(',', $csubSql),implode(',', $subSql));
        return DbUtil::execute($SQL);
    }

    /**
     * h5支付逻辑
     * @param $outerOrderId
     * @param $payType
     * @return array
     */
    public static function paySeatOnlineOrder($outerOrderId, $payType){
        $outerOrderId = OOrderProcess::getThSubOrderInfo($outerOrderId)['outerOrderId'];
        $userOrderInfo = self::getOrderSeatInfoByOuterOrderId($outerOrderId);
        if(empty($userOrderInfo))
        {
            return array("ok"=>false,"flag"=>'orderStatus','err_code'=>401,'msg'=>'订单不存在');
        }
        if ($userOrderInfo['orderStatus'] != 10101)
        {
            return array("ok"=>false,"flag"=>'orderStatus','err_code'=>401,'msg'=>'订单状态错误');
        }
        GeneralFunc::writeLog('paySeatOnlineOrder1,'.$outerOrderId.",".$payType, Yii::app()->getRuntimePath().'/H5yii/');
        switch ($payType)
        {
            //余额支付
            case ConfigParse::getPayTypeKey('accountPay'):
                return self::confirmSeatOnlineOrderWithNoBankPay($userOrderInfo['iUserId'],$outerOrderId,$payType);
            case ConfigParse::getPayTypeKey('shanghaiBank'):
                return self::confirmSeatOnlineOrderWithSHBankPay($outerOrderId,$payType);
            default:
                return (array("ok"=>false,'msg'=>'请选择支付方式或验证电影卡'));
        }
    }

    /**
     * 余额支付
     * @param $iUserID
     * @param $outerOrderId
     */
    public static function confirmSeatOnlineOrderWithNoBankPay($iUserID,$outerOrderId,$payType){
        $orderInfo = self::getOrderSeatInfoByOuterOrderId($outerOrderId);
        if(empty($orderInfo))
        {
            return (array("ok"=>false,'err_code'=>401,'msg'=>'订单不存在'));
        }
        GeneralFunc::writeLog('confirmSeatOnlineOrderWithNoBankPay'.$outerOrderId.",".$iUserID.','.$orderInfo['totalPrice'], Yii::app()->getRuntimePath().'/H5yii/');
        $accountPrice = $orderInfo['totalPrice'];
        if (UserProcess::getmAccountMoney($iUserID)>=$accountPrice)
        {
            $retaccount = UserProcess::updateAccountPay($iUserID,$accountPrice);
            if ($retaccount <= 0)
            {
                return (array("ok"=>false,'err_code'=>401,'msg'=>'余额支付异常'));
            }
            $arPaylog['outerOrderId'] = $outerOrderId;
            $arPaylog['totalPrice'] = $accountPrice;
            $arPaylog['iUserID'] = $iUserID;
            $arPaylog['bankType'] = $payType;
            $arPaylog['status'] = ConfigParse::getPayStatusKey('orderPay');
            self::addPayForSeat($arPaylog);
        }
        else {
            return (array("ok"=>false,'err_code'=>401,'msg'=>'您的余额不足，请先充值'));
        }
        $updateorderInfo['orderPayType'] = ConfigParse::getPayTypeKey('accountPay');
        $updateorderInfo['outerOrderId'] = $outerOrderId;
        $updateorderInfo['orderStatus'] = ConfigParse::getPayStatusKey('orderPay');
        GeneralFunc::writeLog('confirmSeatOnlineOrderWithNoBankPay'.$outerOrderId.",".ConfigParse::getPayTypeKey('accountPay').','.ConfigParse::getPayStatusKey('orderPay'), Yii::app()->getRuntimePath().'/H5yii/');
        OOrderProcess::updateUserOrderInfo($updateorderInfo);
        GeneralFunc::writeLog('confirmSeatOnlineOrderWithNoBankPay,updateUserOrderInfo'.$outerOrderId.",".ConfigParse::getPayTypeKey('accountPay').','.ConfigParse::getPayStatusKey('orderPay'), Yii::app()->getRuntimePath().'/H5yii/');
        self::confirmSeatOnlineOrder($iUserID, $outerOrderId,$orderInfo['sendPhone']);
        GeneralFunc::gotoUrl('index.php?r=output/Site/Success&orderId='.$outerOrderId.'&userId='.$iUserID);
        return array('ok'=>true);
    }

    /**
     * 上海银行支付
     * @param $iUserID
     * @param $outerOrderId
     */
    public static function confirmSeatOnlineOrderWithSHBankPay($outerOrderId,$payType){
        $orderInfo = self::getOrderSeatInfoByOuterOrderId($outerOrderId);
        if(empty($orderInfo))
        {
            return (array("ok"=>false,'err_code'=>401,'msg'=>'订单不存在'));
        }

        //请求支付接口
        $totalAmount = $orderInfo['totalPrice'];
        GeneralFunc::writeLog('confirmSeatOnlineOrderWithSHBankPay1,'.$outerOrderId.",".$payType.','.$totalAmount, Yii::app()->getRuntimePath().'/H5yii/');
        ThOrderProcess::orderPay($outerOrderId,$totalAmount,$orderInfo['sPhone']);
        GeneralFunc::writeLog('confirmSeatOnlineOrderWithSHBankPay2,'.$outerOrderId.",".$payType, Yii::app()->getRuntimePath().'/H5yii/');
//        self::confirmSeatOnlineOrder($iUserID, $outerOrderId,$orderInfo['sendPhone']);
//        GeneralFunc::gotoUrl('index.php?r=output/Site/Success&orderId='.$outerOrderId.'&userId='.$iUserID);
        return array('ok'=>true);
    }

    /**
     * 增加paylog记录
     * @param $arPaylog
     * @return mixed
     */
    public static function addPayForSeat($arPaylog){
        $subSql = array();
        $csubSql=array();
        foreach($arPaylog as $key =>$v)
        {
            $subSql[] = "'".mysql_escape_string($v)."'";
            $csubSql[]=$key;
        }
        $SQL = sprintf("insert into  {{out_paylog}} (%s) VALUES(%s)",implode(',', $csubSql),implode(',', $subSql));
        return DbUtil::execute($SQL);
    }

    public static function confirmSeatOnlineOrder($iUserId,$outerOrderId,$sendPhone,$formId = "")
    {
        //费用已经扣完
        $ret = OCinemaInterfaceProcess::GetConfirmOrderResult($outerOrderId);

        //判断是格瓦拉还是网票网
        if($ret['ResultCode'] == ConfigParse::getOrderStatusKey('ORDER_STATUS_Successed')){
            //网票网同步获取取票码
            if(isset($ret['InterfaceValidCode']) && !empty($ret['InterfaceValidCode'])){
                //完成订单
                $orderInfo['orderStatus'] = ConfigParse::getPayStatusKey('orderSucess');
                $orderInfo['outerOrderId'] = $outerOrderId;
                self::updateUserOrderInfo( $orderInfo);

                $userPayLog=  array("iUserID"=>$iUserId,"outerOrderId"=>$outerOrderId,  "ticketCode"=>$ret['InterfaceValidCode'],"status"=>ConfigParse::getPayStatusKey('orderSucess'));
                self::updateUserPayOrderInfo($userPayLog);

                $orderSeatUpdate[ConfigParse::getOrderSeatInfoKey('sInterfaceValidCode')] = $ret['InterfaceValidCode'];
                $orderSeatUpdate[ConfigParse::getOrderSeatInfoKey('outerOrderId')] = $outerOrderId;
                $orderSeatUpdate[ConfigParse::getOrderSeatInfoKey('status')]=ConfigParse::getPayStatusKey('orderSucess');
                $orderSeatUpdate[ConfigParse::getOrderSeatInfoKey('userId')] = $iUserId;
                $orderSeatUpdate[ConfigParse::getOrderSeatInfoKey('lowValue')] = 0;
                $orderSeatUpdate['sPhone'] = $sendPhone;
                self::updateOrderSeatByOrderInfo($iUserId, $orderSeatUpdate);
                self::sendSeatOnlineMsg($outerOrderId,$formId);
            }elseif(isset($ret['InterfaceValidCode']) && empty($ret['InterfaceValidCode'])){
                //网票网异步获取取票码
                //开始异步订单
                $orderInfo['orderStatus'] = ConfigParse::getPayStatusKey('orderAsynSeatBegin');
                $orderInfo['outerOrderId'] = $outerOrderId;
                self::updateUserOrderInfo( $orderInfo);
                $orderSeatUpdate[ConfigParse::getOrderSeatInfoKey('outerOrderId')] = $outerOrderId;
                $orderSeatUpdate[ConfigParse::getOrderSeatInfoKey('userId')] = $iUserId;
                $orderSeatUpdate['sPhone'] = $sendPhone;
                $orderSeatUpdate['form_id'] = $formId;
                self::updateOrderSeatByOrderInfo($iUserId, $orderSeatUpdate);
            }else{
                //格瓦拉同步获取取票码
                $arResult = OCinemaInterfaceProcess::GetOrderInfoResult($outerOrderId);
                if($arResult['OrderStatus'] == ConfigParse::getOrderStatusKey('ORDER_STATUS_Successed')&&!empty($arResult['FetchNo'])){
                    //完成订单
                    $orderInfo['orderStatus'] = ConfigParse::getPayStatusKey('orderSucess');
                    $orderInfo['outerOrderId'] = $outerOrderId;
                    self::updateUserOrderInfo( $orderInfo);

                    $userPayLog=  array("iUserID"=>$iUserId,"outerOrderId"=>$outerOrderId,  "ticketCode"=>$arResult['FetchNo'],"status"=>ConfigParse::getPayStatusKey('orderSucess'));
                    self::updateUserPayOrderInfo($userPayLog);

                    $orderSeatUpdate[ConfigParse::getOrderSeatInfoKey('sInterfaceValidCode')] = $arResult['FetchNo'];
                    $orderSeatUpdate[ConfigParse::getOrderSeatInfoKey('outerOrderId')] = $outerOrderId;
                    $orderSeatUpdate[ConfigParse::getOrderSeatInfoKey('status')]=ConfigParse::getPayStatusKey('orderSucess');
                    $orderSeatUpdate[ConfigParse::getOrderSeatInfoKey('userId')] = $iUserId;
                    $orderSeatUpdate[ConfigParse::getOrderSeatInfoKey('lowValue')] = 0;
                    $orderSeatUpdate['sPhone'] = $sendPhone;
                    self::updateOrderSeatByOrderInfo($iUserId, $orderSeatUpdate);
                    self::sendSeatOnlineMsg($outerOrderId,$formId);
                }else{
                    //异步开始
                    $orderInfo['orderStatus'] = ConfigParse::getPayStatusKey('orderAsynSeatBegin');
                    $orderInfo['outerOrderId'] = $outerOrderId;
                    self::updateUserOrderInfo( $orderInfo);
                    $orderSeatUpdate[ConfigParse::getOrderSeatInfoKey('outerOrderId')] = $outerOrderId;
                    $orderSeatUpdate[ConfigParse::getOrderSeatInfoKey('userId')] = $iUserId;
                    $orderSeatUpdate['sPhone'] = $sendPhone;
                    $orderSeatUpdate['form_id'] = $formId;
                    self::updateOrderSeatByOrderInfo($iUserId, $orderSeatUpdate);
                }
            }
        }else{
            if($ret['ResultCode'] == ConfigParse::getOrderStatusKey('PROCESSED_STATUS_NotProcessed')){
                //格瓦拉
                $orderInfo['orderStatus'] = ConfigParse::getPayStatusKey('orderPayNoticeFail');
            }else{
                $orderInfo['orderStatus'] = ConfigParse::getPayStatusKey('orderAsynSeatBegin');
            }
            $orderInfo['outerOrderId'] = $outerOrderId;
            self::updateUserOrderInfo( $orderInfo);
            $orderSeatUpdate[ConfigParse::getOrderSeatInfoKey('outerOrderId')] = $outerOrderId;
            $orderSeatUpdate[ConfigParse::getOrderSeatInfoKey('userId')] = $iUserId;
            $orderSeatUpdate['sPhone'] = $sendPhone;
            $orderSeatUpdate['form_id'] = $formId;
            self::updateOrderSeatByOrderInfo($iUserId, $orderSeatUpdate);
        }
        return $outerOrderId;

    }

    /**
     * 太和出票
     * @param $iUserId
     * @param $outerOrderId
     * @param $sendPhone
     * @param string $formId
     * @return mixed
     */
    public static function confirmThSeatOnlineOrder($iUserId,$outerOrderId,$sendPhone,$formId = "")
    {
        //费用已经扣完
        $ret = OCinemaInterfaceProcess::GetConfirmOrderResult($outerOrderId);
//        $ret = array('ResultCode'=>ConfigParse::getOrderStatusKey('ORDER_STATUS_Successed'),'InterfaceValidCode'=>'');
        GeneralFunc::writeLog('confirmThSeatOnlineOrder'.$outerOrderId.print_r($ret,true), Yii::app()->getRuntimePath().'/H5yii/');
        if($ret['ResultCode'] == ConfigParse::getOrderStatusKey('ORDER_STATUS_Successed') && !empty($ret['InterfaceValidCode'])){
            //完成订单
            $orderInfo['orderStatus'] = ConfigParse::getPayStatusKey('orderSucess');
            $orderInfo['outerOrderId'] = $outerOrderId;
            self::updateUserOrderInfo($orderInfo);
            GeneralFunc::writeLog('GetConfirmOrderResult1,updateUserOrderInfo，修改成功', Yii::app()->getRuntimePath().'/H5yii/');

            $userPayLog = array(
                "iUserID" => $iUserId,
                "outerOrderId" => $outerOrderId,
                "ticketCode" => $ret['InterfaceValidCode'],
                "status" => ConfigParse::getPayStatusKey('orderSucess')
            );
            self::updateUserPayOrderInfo($userPayLog);
            GeneralFunc::writeLog('GetConfirmOrderResult1,updateUserPayOrderInfo，修改成功', Yii::app()->getRuntimePath().'/H5yii/');

            $orderSeatUpdate[ConfigParse::getOrderSeatInfoKey('sInterfaceValidCode')] = $ret['InterfaceValidCode'];
            $orderSeatUpdate[ConfigParse::getOrderSeatInfoKey('outerOrderId')] = $outerOrderId;
            $orderSeatUpdate[ConfigParse::getOrderSeatInfoKey('status')] = ConfigParse::getPayStatusKey('orderSucess');
            $orderSeatUpdate[ConfigParse::getOrderSeatInfoKey('userId')] = $iUserId;
            $orderSeatUpdate[ConfigParse::getOrderSeatInfoKey('lowValue')] = 0;
            $orderSeatUpdate['sPhone'] = $sendPhone;
            self::updateOrderSeatByOrderInfo($iUserId, $orderSeatUpdate);
            GeneralFunc::writeLog('GetConfirmOrderResult1,updateOrderSeatByOrderInfo，修改成功', Yii::app()->getRuntimePath().'/H5yii/');

            //修改太和获取取票码
            ThOrderProcess::getCode($outerOrderId,$ret['InterfaceValidCode']);
            GeneralFunc::writeLog('GetConfirmOrderResult1,getCode，修改成功', Yii::app()->getRuntimePath().'/H5yii/');
        }else if($ret['ResultCode'] == ConfigParse::getOrderStatusKey('PROCESSED_STATUS_NotProcessed')){
            //这块是处理格瓦拉订单支付通知不成功的逻辑，修改订单状态后进行异步通知请求
            $orderInfo['orderStatus']= ConfigParse::getPayStatusKey('orderPayNoticeFail');
            $orderInfo['outerOrderId']= $outerOrderId;
            self::updateUserOrderInfo($orderInfo);
            GeneralFunc::writeLog('GetConfirmOrderResult2,updateUserOrderInfo，修改成功', Yii::app()->getRuntimePath().'/H5yii/');
        }else{
            //开始异步订单
            $orderInfo['orderStatus'] = ConfigParse::getPayStatusKey('orderAsynSeatBegin');
            $orderInfo['outerOrderId'] = $outerOrderId;
            self::updateUserOrderInfo( $orderInfo);
            GeneralFunc::writeLog('GetConfirmOrderResult3,updateUserOrderInfo，修改成功', Yii::app()->getRuntimePath().'/H5yii/');
        }
    }

    //购票成功下发短信
    public static function sendSeatOnlineMsg($outerOrderId,$formId = "")
    {

        $OrderInfo = self::getOrderSeatInfoByOuterOrderId($outerOrderId);
        if($OrderInfo['iInterfaceID'] == ConfigParse::getInterfaceKey('InterfaceType_Wangpiao')){
            $arResult = OCinemaInterfaceProcess::GetOrderInfoResult($outerOrderId);
            if($arResult['OrderStatus'] == ConfigParse::getOrderStatusKey('ORDER_STATUS_Failed')){
                $arResult['data'] = -1;
            }
        }else{
            $arResult['data'] = -1;
        }
        $Stype = array(0,1,13);
        if(!empty($OrderInfo['sInterfaceValidCode']))
        {
            $arrCode0 = '';
            $arrCode1 = '';
            $errorCode0 = '';
            $errorCode1 = '';
            $reg = '/^[a-zA-Z0-9\|\*]*$/';                    // 正则句
            $proArr = str_split( $OrderInfo['sInterfaceValidCode'] );           // 将字符串切割成数组
            $proLen = count( $proArr );               // 计算该数组的长度
            $ValidCodeStr = '';

// 通过循环去核对数组中每个数据是否满足正则句
            for( $iCount = 0; $iCount < $proLen; $iCount++ )
            {
                if( preg_match( $reg, $proArr[$iCount] ) )    // 满足正则句表示该数据是字符
                {
                    $ValidCodeStr .= $proArr[$iCount];                    // 字符存放的数组
                }
            }
            $ValidCodeStr = ltrim($ValidCodeStr,'|');
            $sInterfaceValidCode = explode('*',$ValidCodeStr);

            if(isset($sInterfaceValidCode[1]) && !empty($sInterfaceValidCode[1]))
            {
                if(count(explode('|',$sInterfaceValidCode[1])) == 2){
                    $errorCode0 = explode('|',$sInterfaceValidCode[1])[0];
                    $errorCode1 = explode('|',$sInterfaceValidCode[1])[1];
                    if ($OrderInfo['iInterfaceID'] == ConfigParse::getInterfaceKey('InterfaceType_Wangpiao') && $arResult['data']==12){
                        $sValidCodeErrMsg = '可凭取票码到网票网取票机取票'.'（如遇机器故障：故障码：'.$errorCode0.'，验证码：'.$errorCode1.'）。';
                    }else{
                        $sValidCodeErrMsg = '可凭取票码到自助取票机取票（如遇机器故障：故障码：'.$errorCode0.'  '.'验证码：'.$errorCode1.'）。';
                    }
                }else{
                    $errorCode0 = explode('|',$sInterfaceValidCode[1])[0];
                    if ($OrderInfo['iInterfaceID'] == ConfigParse::getInterfaceKey('InterfaceType_Wangpiao') && $arResult['data']==12){
                        $sValidCodeErrMsg = '可凭取票码到网票网取票机取票'.'（如遇机器故障：故障码：'.$errorCode0.'）。';
                    }else{
                        $sValidCodeErrMsg = '可凭取票码到自助取票机取票（如遇机器故障：故障码：'.$errorCode0.'）。';
                    }

                }
            }else{
                if($OrderInfo['iInterfaceID'] == ConfigParse::getInterfaceKey('InterfaceType_Wangpiao') && in_array($arResult['data'],$Stype)){
                    $sValidCodeErrMsg = '可凭取票码到网票网取票机取票。';
                }else{
                    $sValidCodeErrMsg = '可凭取票码到自助取票机取票。';
                }
            }

            if(count(explode('|',$sInterfaceValidCode[0])) == 2){
                $arrCode0 = explode('|',$sInterfaceValidCode[0])[0];
                $arrCode1 = explode('|',$sInterfaceValidCode[0])[1];
                $errorCode0 = explode('|',$sInterfaceValidCode[0])[0];
                $errorCode1 = explode('|',$sInterfaceValidCode[0])[1];
                $sValidCodeMsg = $arrCode0.'  '.'验证码：'.$arrCode1;
            }else{
                $arrCode0 = explode('|',$sInterfaceValidCode[0])[0];
                $errorCode0 = explode('|',$sInterfaceValidCode[0])[0];
                $sValidCodeMsg = $arrCode0;
            }
        }
        $day = date("Y-m-d",strtotime($OrderInfo['dPlayTime']));
        $dateDay = strtotime(date("Y-m-d"));
        $days=round((strtotime($day)-$dateDay)/86400);
        $week = "周".mb_substr("日一二三四五六",date("w",strtotime($OrderInfo['dPlayTime'])),1,"utf-8");
        $date = explode('-',$day);
        $time = date("H:i",strtotime($OrderInfo['dPlayTime']));
        switch($days){
            case 0:
                $date = '今天'.($date[1]<=9 ? str_replace('0','',$date[1]) : $date[1]).'月'.($date[2]<=9 ? str_replace('0','',$date[2]) : $date[2]).'日'." ".$time;
                break;
            case 1:
                $date = '明天'.($date[1]<=9 ? str_replace('0','',$date[1]) : $date[1]).'月'.($date[2]<=9 ? str_replace('0','',$date[2]) : $date[2]).'日'." ".$time;
                break;
            case 2:
                $date = '后天'.($date[1]<=9 ? str_replace('0','',$date[1]) : $date[1]).'月'.($date[2]<=9 ? str_replace('0','',$date[2]) : $date[2]).'日'." ".$time;
                break;
            case 3:
                $date = $week.($date[1]<=9 ? str_replace('0','',$date[1]) : $date[1]).'月'.($date[2]<=9 ? str_replace('0','',$date[2]) : $date[2]).'日'." ".$time;
                break;
            case 4:
                $date = $week.($date[1]<=9 ? str_replace('0','',$date[1]) : $date[1]).'月'.($date[2]<=9 ? str_replace('0','',$date[2]) : $date[2]).'日'." ".$time;
                break;
            case 5:
                $date = $week.($date[1]<=9 ? str_replace('0','',$date[1]) : $date[1]).'月'.($date[2]<=9 ? str_replace('0','',$date[2]) : $date[2]).'日'." ".$time;
                break;
            case 6:
                $date = $week.($date[1]<=9 ? str_replace('0','',$date[1]) : $date[1]).'月'.($date[2]<=9 ? str_replace('0','',$date[2]) : $date[2]).'日'." ".$time;
                break;
            default:
                break;
        }
        $seatInfo = array();
        foreach(explode(',',$OrderInfo['orderInfo']) as $k => $v){
            $seatInfo[$k] = $v.'座';
        }

        if((!empty($arrCode0) && !empty($arrCode1) &&!empty($errorCode0) &&!empty($errorCode1)) && (($arrCode0 != $errorCode0) && ($arrCode0 != $errorCode1) &&($arrCode1 != $errorCode0)&&($arrCode1 != $errorCode1))){
            $type = 'SMS_7776215';
            //双码不一致
            $ValidCode = array(
                'arrCode0' => $arrCode0,
                'arrCode1' => $arrCode1,
                'errorCode0' => $errorCode0,
                'errorCode1' => $errorCode1,
            );
        }
        if((!empty($arrCode0) && !empty($arrCode1) &&!empty($errorCode0) &&!empty($errorCode1)) && (($arrCode0 == $errorCode0) && ($arrCode0 != $errorCode1) &&($arrCode1 != $errorCode0)&&($arrCode1 == $errorCode1))){
            $type = 'SMS_7806238';
            //双码一致
            $ValidCode = array(
                'arrCode0' => $arrCode0,
                'arrCode1' => $arrCode1,
            );
        }
        if((!empty($arrCode0) && !empty($arrCode1) &&!empty($errorCode0) && empty($errorCode1)) && (($arrCode0 != $errorCode0) &&($arrCode1 != $errorCode0))){
            $type = 'SMS_7741432';
            //双单码
            $ValidCode = array(
                'arrCode0' => $arrCode0,
                'arrCode1' => $arrCode1,
                'errorCode' => $errorCode0,
            );
        }
        if((!empty($arrCode0) && empty($arrCode1) &&!empty($errorCode0) && !empty($errorCode1)) && (($arrCode0 != $errorCode0) &&($arrCode0 != $errorCode1))){
            $type = 'SMS_7761503';
            //单双码
            $ValidCode = array(
                'arrCode' => $arrCode0,
                'errorCode0' => $errorCode0,
                'errorCode1' => $errorCode1
            );
        }
        if((!empty($arrCode0) && empty($arrCode1) &&!empty($errorCode0) && empty($errorCode1)) && (($arrCode0 != $errorCode0))){
            $type = 'SMS_7771407';
            //单码不一致
            $ValidCode = array(
                'validCode' => $arrCode0,
                'errorCode' => $errorCode0,
            );
        }
        if((!empty($arrCode0) && empty($arrCode1) &&!empty($errorCode0) && empty($errorCode1)) && (($arrCode0 == $errorCode0))){
            $type = 'SMS_7811111';
            //单码一致
            $ValidCode = array(
                'validCode' => $arrCode0
            );
        }

//${dateTime}${sCinemaName}的${sMovieName}，${sRoomName}${strSeatInfo}。

        $arPara = array(
            'dateTime'=>$date,
            'sCinemaName'=>$OrderInfo['sCinemaName'],
            'sMovieName'=>$OrderInfo['sMovieName'],
            'sRoomName'=>$OrderInfo['sMovieName'],
            'strSeatInfo'=>implode(',',$seatInfo)
        );

        $arPara = array_merge($ValidCode,$arPara);
        $ret = SMSProcess::sendDayuSMS($OrderInfo['sendPhone'], $arPara, $type);
        if($formId != ''){
            if($ret){
                $openid = UserProcess::getOpenid(UserProcess::getUInfo($OrderInfo['iUserId'])['sPhone'])['openid'];
                //微信通知
                $wx = new Weixin();
                $template = array(
                    'touser' => "$openid",
                    'template_id' => 'oIiIM1ePKylRqvpIj63R6L2JrjKWLEZU_XwL_tAiSTk',
                    'page' =>"pages/movie/index",
                    'form_id'=>"$formId",
                    'data'=> array(
                        'keyword1' => array('value'=>$OrderInfo['sMovieName'], 'color'=>'#173177'),
                        'keyword2' => array('value'=>$date, 'color'=>'#173177'),
                        'keyword3' => array('value'=>$OrderInfo['sCinemaName'].'　'.$OrderInfo['sRoomName'], 'color'=>'#173177'),
                        'keyword4' => array('value'=>implode(',',$seatInfo), 'color'=>'#173177'),
                        'keyword5' => array('value'=>$sValidCodeMsg, 'color'=>'#173177'),
                        'keyword6' => array('value'=>$sValidCodeErrMsg, 'color'=>'#173177'),
                    )
                );
                $wx->wxsendTemplateMsg($openid,$template);
            }
        }
        return $ret;
        //exit(0);
    }
}