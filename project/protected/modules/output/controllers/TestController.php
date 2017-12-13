<?php

/**
 * TestController - 专用于测试
 * @author luzhizhong
 * @version V1.0
 */

Yii::import('application.models.data.db.*');
Yii::import('application.modules.output.models.data.*');
Yii::import('application.modules.output.models.process.*');

class TestController extends Controller
{
	/**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array(
		);
	}
		
	public function actionIndex()
	{
		$iRoomMovieID = $_REQUEST['iRoomMovieID'];
		$roomMovieInfo= CinemaProcess::getRoomMovieListByiRoommovieID($iRoomMovieID);
		$MovieMatchInfo = MovieProcess::getMovieMatchInfo($roomMovieInfo['iMovieID']);
		//调用太和创建订单
		$ThRet = ThOrderProcess::createThOrder(1,'测试','DD-0914-7D24125D021984DC082589D0',$roomMovieInfo['iEpiaoCinemaID'],$roomMovieInfo['iMovieID'],$roomMovieInfo['mPrice'],$iRoomMovieID,13261993774,$roomMovieInfo['sRoomName'],$roomMovieInfo['sDimensional'],$roomMovieInfo['sLanguage'],'',$roomMovieInfo['dBeginTime'],'1排1座',1);
		print_r($ThRet);die;
	}

	public function actionPayOrder(){
		$ret = ThOrderProcess::orderPay('DD-0914-7D24125D021984DC082589D8','1.00','13261993774');
		print_r($ret);die;
		GeneralFunc::writeLog('actionPayOrder'.print_r($ret,true), Yii::app()->getRuntimePath().'/H5yii/');
	}

	public function actionUpdateOrder(){
		$outerOrderId = $_REQUEST['DD-0908-2CA649DDBFA8061395FD9B31'];
		$orderInfo = self::getOrderSeatInfoByOuterOrderId($outerOrderId);
		$ret = ThOrderProcess::updateThOrder($orderInfo['orderId'],ThOrderProcess::$arrOrderSratus['endOrder']);
	}

	public function actionTest(){
		$orderId = $_REQUEST['orderId'];
		$orderInfo = OOrderProcess::getThOrderInfo($orderId);
		$outerOrderId = $orderInfo['outerOrderId'];
		return OOrderProcess::confirmThSeatOnlineOrder($orderInfo['iUserId'], $outerOrderId,$orderInfo['sendPhone']);
	}
}