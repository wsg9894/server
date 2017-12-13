<?php

/**
 * TestController - 测试控制器
 * @author luzhizhong
 * @version V1.0
 */

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
		$type = $_REQUEST['type'];
		$key = md5($type.'@'.Yii::app()->params['interfPW_WXAPP'].'@'.GeneralFunc::getCurDate());

		define("REQUEST_URL",Yii::app()->params['testUrl']."/index.php?r=wxAPP/Interf&type=%s&key=%s&param=%s");

		switch($type)
		{
			case 'get_splash':
				$param = array();
				break;
			case 'get_movielist':
				$param = array('cityID'=>1);
				break;
			case 'get_movieinfo':
				$param = array('movieID'=>2012);
				break;
			case 'get_cinemalist_by_movie':
				$param = array('cityID'=>1,'movieID'=>2012,'latitude'=>'39.923522','longitude'=>'116.539645');
				break;
			case 'get_arrangelist':
				$param = array('cityID'=>33,'cinemaID'=>1272,'movieID'=>2012,'date'=>'2017-04-26');
				break;
			case 'get_seatlist':
				$param = array('roommovieID'=>'10407830038');
				break;
			case 'sub_seatorder':
				$param = array('roommovieID'=>'10407830038','seatNo'=>'3:2','seatId'=>'','seatInfo'=>'3排2','iUserID' =>1281057);
				break;
			case 'sub_noPayorder':
				$param = array('outerOrderId'=>'DD-0120-4D95DFB2BF8F063082B32550','iUserID' =>1313661);
				break;
			case 'sub_noPayorderseat':
				$param = array('outerOrderId'=>'DD-0120-4D95DFB2BF8F063082B32550','payType'=>'0|3','sCheckNo'=>'3214827939','count'=>'1','cardCount'=>'1','validcount'=>'24','couponId'=>'246',"sPhone"=>"18830728379","iUserID"=>"1313661",'code'=>'013Yww7v0jmibe1Eq36v0EEv7v0Yww7q','sVoucherPassWord'=>'B0UHE1PZ29WC');
				break;
			case 'sub_success':
				$param = array('outerOrderId'=>'DD-0113-1D2444F7CF7F68B717493658');
				break;
			case 'check_login':
				$param = array('phone'=>'18830728379','password'=>'anqing4098860');
				break;
			case 'check_register':
				$param = array('sPhone'=>'18618494733');
				break;
			case 'check_getVoucher':
				$param = array('iUserID' =>1281057,'getCashFlag' => 1);
				break;
			case 'get_citylist':
				$param = array();
				break;
			case 'get_regionlist':
				$param = array('cityID'=>1);
				break;
			case 'get_cinemalist':
				$param = array('cityID'=>1,'latitude'=>'39.923522','longitude'=>'116.539645');
				break;
			case 'get_cinemaListBySearch':
				$param = array('cityID'=>1,'latitude'=>'39.923522','longitude'=>'116.539645','search'=>'d','iMovieID'=>0);
				break;
			case 'set_cinemaSearchLog':
				$param = array('search'=>'123','code'=>'013Yww7v0jmibe1Eq36v0EEv7v0Yww7q','iUserID'=>1281057);
				break;
			case 'get_cinemaSearchLog':
				$param = array('code'=>'013Yww7v0jmibe1Eq36v0EEv7v0Yww7q','iUserID'=>1281057);
				break;
			case 'del_cinemaSearchLog':
				$param = array('code'=>'013Yww7v0jmibe1Eq36v0EEv7v0Yww7q','searchid'=>'','iUserID'=>1281057);
				break;
			case 'sub_weixinsuccess':
				$param = array('outerOrderId'=>'DD-0107-DA03948D33F0C9D27BF0081B','iUserID' =>1309050,'sPhone'=>15830291856);
				break;
			case 'get_userInfo':
				$param = array('iUserID' =>1281057);
				break;
			case 'get_userOrderInfo':
				$param = array('iUserID' =>1281057);
				break;
			case 'get_userCarInfo':
				$param = array('iUserID' =>1309050);
				break;
			case 'get_userCashInfo':
				$param = array('iUserID' =>1281057);
				break;
			case 'add_CarToUser':
				$param = array('iUserID' =>1281057,'sCheckNo'=>'6091654037','sPassWord'=>'052314');
				break;
			case 'add_CashToUser':
				$param = array('iUserID' =>1281057,'sVoucherPassWord'=>'YGAZ2J61CC4W');
				break;
			case 'sub_weixinfail':
				$param = array('outerOrderId'=>'DD-0107-DA03948D33F0C9D27BF0081B');
				break;
			case 'mess_authentication':
				$param = array('sPhone' =>'18618494733');
				break;
			case 'set_password':
				$param = array('sPhone' =>'18618494733','sPassWord'=>'4098860');
				break;
			case 'up_pwdbycode':
				$param = array('sPhone'=>'18618494733','oldPwd'=>'123456','newPwd'=>'4098860','verifyPwd'=>'4098860');
				break;
			case 'set_recharge':
				$param = array('iUserID'=>'1281057','money'=>10,'code'=>'','type'=>1);
				break;
			case 'sub_weixinRechargeOk':
				$param = array('iUserID'=>'1281057','outerOrderId' =>'DD-0308-FB95B43EE57A8F46F135BC8F');
				break;
			case 'sub_weixinRechargeFail':
				$param = array('outerOrderId' =>'DD-0308-3D78C85A02FD37DD5113D029');
				break;
			case 'get_rechargelog':
				$param = array('iUserID'=>'1281057');
				break;
			case 'get_huodong':
				$param = array();
				break;
			case 'sub_huodongById':
				$param = array('iUserID'=>'1281057','iHuoDongID'=>190);
				break;
			case 'weixin_tuisong':
				$param = array('outerOrderId' =>'DD-0106-B6F1EB1F10DC7020C1D2D288');
				break;
			default:
				$param = array();
				break;
		}

		$url = sprintf(REQUEST_URL, $type, $key, json_encode($param));

		$result = file_get_contents($url);

		$result = json_decode($result, true);
		print_r($result);
	}

	public static function actionDingwei(){
//		$lat1 = '39.923522';
//		$lng1 = '116.539645';//31.1822558850,121.3776992217
//		$lat2 = '38.3644400000';
//		$lng2 = '117.3503500000';//31.1822558850,121.3776992217
//		//ROUND(SQRT((iCartesianX-%d)*(iCartesianX-%d)+(iCartesianY-%d)*(iCartesianY-%d)))
//		$info2 = LBS::geodeticToCartesian($lat2,$lng2);
//		$info1 = LBS::geodeticToCartesian($lat1,$lng1);
//		$X = $info1['x'];
//		$Y = $info1['y'];
//		$iCartesianX = $info2['x'];
//		$iCartesianY = $info2['y'];
//		$res = ROUND(SQRT(($iCartesianX-$X)*($iCartesianX-$X)+($iCartesianY-$Y)*($iCartesianY-$Y)));
//		echo $res/1000;
		$res = CinemaProcess::getCinemaListByCity(1,array('sCoordinates','sCinemaName'));
		foreach($res as $k =>$v){
			$lat1 = explode(',',$v['sCoordinates'])[1];
			$lng1 = explode(',',$v['sCoordinates'])[0];
			$limit[$k]['limit'] = LBS::getDistance($lat1,$lng1,"39.923522","116.539645")/1000;
			$limit[$k]['sCinemaName'] = $v['sCinemaName'];
		}
		usort($limit,function($a, $b){
			if ($a['limit'] < $b['limit'])
				return -1;
			else if ($a['limit'] == $b['limit'])
				return 0;
			else
				return 1;
		});
		var_dump($limit);
	}

	public static function actionTuisong(){
		$formId = 'bfa4b2212597a1fa4fbd6a532d81075f';

		$reqUrl = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wxa12cb24eb59eabb0&secret=93b4bc2174d24cebc6dd5a3d021cdabb';
		$curl = curl_init();

		curl_setopt($curl,CURLOPT_URL, $reqUrl);
		curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		$output = curl_exec($curl);
		curl_close($curl);
		$result = json_decode($output, TRUE);

		$reqUrl = 'https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token='.$result['access_token'];
		$template = array(
			'touser' => 'o2ZYI0VRKTTX2beBuE8Z1dNrpZVw',
			'template_id' => 'oIiIM1ePKylRqvpIj63R6L2JrjKWLEZU_XwL_tAiSTk',
			'page' => Yii::app()->params['baseUrl'].'cinema/movieselect.php',
			'form_id'=>"$formId",
			'data' => array(
				'keyword1' => array('value'=>'功夫瑜伽', 'color'=>'#173177'),
				'keyword2' => array('value'=>date("Y-m-d H:i:s"), 'color'=>'#173177'),
				'keyword3' => array('value'=>'北京银兴乐天影城 vip厅', 'color'=>'#173177'),
				'keyword4' => array('value'=>'1排2', 'color'=>'#173177'),
				'keyword5' => array('value'=>'123', 'color'=>'#173177'),
				'keyword6' => array('value'=>'456', 'color'=>'#173177'),
			),
		);
		$template = json_encode($template, TRUE);
		$curl = curl_init();

		curl_setopt($curl,CURLOPT_URL, $reqUrl);
		curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $template);
		$output = curl_exec($curl);
		curl_close($curl);
		var_dump($output);
	}

	public function actionGWLtest(){
		/*
		 * 查看座位信息
		 * $bookSeatingArrange['sRoomMovieInterfaceNo'] = '408338984';
		$bookSeatingArrange['iInterfaceID'] = 10;
		$bookSeatingArrange['sCinemaInterfaceNo'] = '67671611';
		$bookSeatingArrange['sRoomInterfaceNo'] = '123899102';
		$arLockSeatInfo = CinemaInterfaceProcess::GetSelectedSeat($bookSeatingArrange);
		print_r($arLockSeatInfo);
		 * */
		$rawData = GWLRetrieverProcess::Get_Sell_UnLockSeat('1170706104022342');
		print_r($rawData);


	}
}