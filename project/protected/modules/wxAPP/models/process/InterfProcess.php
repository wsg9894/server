<?php
date_default_timezone_set('PRC');
/**
 * InterfProcess - 接口操作类
 * @author luzhizhong
 * @version V1.0
 */
class InterfProcess
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

	/**
	 * 有效字段过滤（过滤有效的字段列表）
	 *
	 * @param array[] $inputFields	输入的字段列表
	 * @param array[] $defFields 数据库字段列表
	 * @return array[] 过滤后的字段列表
	 */
	private function veliSession($res=array())
	{
		if(!isset($_SESSION)){
			session_start();
		}
		if(empty($_SESSION['iUserID'])&&!empty($res)){
			$_SESSION['iUserID'] = $res['iUserID'];
		}
	}

	/*
	 * 获取首页闪屏地址
	 * */
	public static function getSplash($param){
		return array('nErrCode'=>0, 'nResult'=>array(
			'splash'=>Yii::app()->params['baseUrl'].'project/images/splash_poster14.jpg',
			'width'=>'1000',
			'height'=>'1499',
			'top'=>'400rpx',
			'left'=>'23.9333%',
			'time'=>'3000'));
	}

	/**
	 * 获取影片列表
	 *
	 * @param array[] $param 参数列表
	 * @return string 影片列表串（形如：id1,id2,id3）
	 */
	public static function getMovieList($param)
	{
		$cityID = empty($param['cityID']) ? 1 : $param['cityID'];

		//获取影院列表串
		$cinemas = CinemaProcess::getCinemasByCity($cityID);

		//获取影片列表
		$movieList = MovieProcess::getCinemaListByCity($cinemas);

		foreach ($movieList as $index => &$movieInfo)
		{
			if(FALSE==empty($movieInfo['sImageUrl']))
			{
				//$movieInfo['sImageUrl'] = Yii::app()->params['baseUrl'].$movieInfo['sImageUrl'];
				//@todo 后续需改为配置
				$movieInfo['sImageUrl'] = Yii::app()->params['baseUrl'].$movieInfo['sImageUrl'];
			}
			$movieInfo['sActor'] = str_replace(' / ','/',$movieInfo['sActor']);
			if(mb_strrpos($movieInfo['sActor'],'/',0,'utf-8') > 28)
			{
				$movieInfo['sActor'] = mb_substr($movieInfo['sActor'],0,mb_strpos($movieInfo['sActor'],'/',28,'utf-8'),'utf-8').'...';
			}

			if(explode('.',$movieInfo['minPrice'])[1] == 0){
				$movieInfo['minPrice'] = explode('.',$movieInfo['minPrice'])[0];
			}else{
				$movieInfo['minPrice'] = round($movieInfo['minPrice'],1);
			}

			if($movieInfo['iMovieID'] == 1913){
				$iUserID="";
				if(isset($param["iUserID"]) && empty($param["iUserID"]))
				{
					$iUserID=$param["iUserID"];
				}
				$hour = date('H');
				$OrderSeatInfo = OrderProcess::getOrderSeatInfoByMovieId($movieInfo['iMovieID']);
				if($OrderSeatInfo['OrderNum']){
					if($OrderSeatInfo['OrderNum'] < 50){
						if($iUserID!=""){
							$UserOrder = OrderProcess::getUserOrderByMovieID($iUserID,$movieInfo['iMovieID']);
							if(!$UserOrder['UserNum']||$UserOrder['UserNum']<1){
								if (($hour>=0 && $hour<8) || $hour>=23)
								{
									$movieInfo['minPrice'] = '19.9';
								}else{
									$movieInfo['minPrice'] = '9.9';
								}
							}
						}else{
							if (($hour>=0 && $hour<8) || $hour>=23)
							{
								$movieInfo['minPrice'] = '19.9';
							}else{
								$movieInfo['minPrice'] = '9.9';
							}
						}
					}
					$movieInfo['count'] = $OrderSeatInfo['OrderNum'];
				}else{
					if (($hour>=0 && $hour<8) || $hour>=23)
					{
						$movieInfo['minPrice'] = '19.9';
					}else{
						$movieInfo['minPrice'] = '9.9';
					}
				}
			}

			$sDirector = "";
			foreach(explode('/',$movieInfo['sDirector']) as $k => $v){
				if($k == 1){
					$sDirector .= $v;
					if(mb_strlen($sDirector,'UTF8')>=10){
						$sDirector = explode('/',$sDirector)[0];
					}
					break;
				}
				$sDirector .= $v.'/';
			}
			$movieInfo['sDirector'] = rtrim($sDirector,'/');

			$tempMovieTypeStr = '';
			if(FALSE==empty($movieInfo['iFavorMoiveID']))
			{
				$movieTypeConf = Yii::app()->params['movieType'];
				$movieTypeArr = explode('、', $movieInfo['iFavorMoiveID']);
				foreach ($movieTypeArr as $index => $movieTypeId)
				{
					$movieType = empty($movieTypeConf[$movieTypeId]) ? '' : $movieTypeConf[$movieTypeId];
					if(empty($movieType))
					{
						continue;
					}
					$tempMovieTypeStr .= empty($tempMovieTypeStr) ? $movieType : '、'.$movieType;
				}
			}
			$movieInfo['sMovieType'] = $tempMovieTypeStr;
		}

		return array('nErrCode'=>0, 'nResult'=>$movieList,'nActivity'=>array('flag'=>1,'picBg0'=>Yii::app()->params['baseUrl'].'project/images/packets0.png','picBg1'=>Yii::app()->params['baseUrl'].'project/images/packets1.png','icon'=>Yii::app()->params['baseUrl'].'project/images/fIcon.png','title'=>'新人专享，点击领取观影红包'));
	}

	/**
	 * 获取影片详情
	 *
	 * @param array[] $param 参数列表
	 * @return array[] 影片详情信息
	 */
	public static function getMovieInfo($param)
	{
		if(empty($param['movieID']))
		{
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('param_error'), ErrorParse::getErrorDesc('param_error'));
		}

		$movieID = $param['movieID'];

		//获取影片信息
		$movieInfo = MovieProcess::getMovieInfoByMovieID($movieID);

		if(FALSE==empty($movieInfo['sImageUrl']))
		{
			//@todo 后续需改为配置
			$movieInfo['sImageUrl'] = Yii::app()->params['baseUrl'].$movieInfo['sImageUrl'];
		}

		//上映时间
		$dPlayTime = date('Y-m-d',strtotime($movieInfo['dPlayTime']));
		$movieInfo['dPlayTime'] = explode('-',$dPlayTime)[0].'年'.explode('-',$dPlayTime)[1].'月'.explode('-',$dPlayTime)[2].'日';
		//影片类型解析
		$tempMovieTypeStr = '';
		if(FALSE==empty($movieInfo['iFavorMoiveID']))
		{
			$movieTypeConf = Yii::app()->params['movieType'];
			$movieTypeArr = explode('、', $movieInfo['iFavorMoiveID']);
			foreach ($movieTypeArr as $index => $movieTypeId)
			{
				$movieType = empty($movieTypeConf[$movieTypeId]) ? '' : $movieTypeConf[$movieTypeId];
				if(empty($movieType))
				{
					continue;
				}
				$tempMovieTypeStr .= empty($tempMovieTypeStr) ? $movieType : '、'.$movieType;
			}
		}
		$sDirector = "";
		foreach(explode('/',$movieInfo['sDirector']) as $k => $v){
			if($k == 1){
				$sDirector .= $v;
				if(mb_strlen($sDirector,'UTF8')>=10){
					$sDirector = explode('/',$sDirector)[0];
				}
				break;
			}
			$sDirector .= $v.'/';
		}
		$movieInfo['sDirector'] = rtrim($sDirector,'/');

		$movieInfo['sMovieType'] = $tempMovieTypeStr;
		if($movieID == 2122){
			$movieInfo['sMovieInfo'] = "　　影片提示：小学生及学龄前儿童应在家长陪同下观看    ".$movieInfo['sMovieInfo'];
		}

		return array('nErrCode'=>0, 'nResult'=>$movieInfo);

	}

	/**
	 * 获取影片上映影院
	 *
	 * @param array[] $param 参数列表
	 * @return array[] 影片详情信息
	 */
	public static function getMovieCinemaList($param)
	{
		if(empty($param['movieID']))
		{
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('param_error'), ErrorParse::getErrorDesc('param_error').'该影片id为空！');
		}
		$movieId = $param['movieID'];
		$cityId = empty($param['cityID']) ? 1 : $param['cityID'];
		//$cityInfo = Model_City_Info::getCityInfo($cityId);
		$arMovieInfo = MovieProcess::getMovieInfoByMovieID($movieId);
		if (empty($arMovieInfo))
		{
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('param_error'), ErrorParse::getErrorDesc('param_error').'该影片不存在！');
		}
		if(empty($param['longitude']) || empty($param['latitude'])){
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('param_error'), ErrorParse::getErrorDesc('param_error').'经纬度不得为空！');
		}
		$lat1 = $param['latitude'];    //纬度
		$lng1 = $param['longitude'];    //经度

		//计时开始
		self::runtime();
		$arCinemaInfo = CinemaProcess::getCinemaListByCity($cityId,array('iCinemaID','sCoordinates','sCinemaName','sRegion','sAddress','bIsCarPark','bIs4D','bIs4K','bIsIMAX','bIsJUMU','bIsDUBI','bIsEAT','iHotCinema'));
		if (empty($arCinemaInfo))
		{
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('param_error'), ErrorParse::getErrorDesc('param_error').'该城市没有任何影院！');
		}
		$moviecinemaInfo = array();
		$dateDay = strtotime(date("Y-m-d"));
		foreach ($arCinemaInfo as $v)
		{
			$cinemaId[] = $v['iCinemaID'];
		}
		$iUserID=0;
		if(isset($param["iUserID"]) && empty($param["iUserID"]))
		{
			$iUserID=$param["iUserID"];
		}
		$arRoomMovieList = CinemaProcess::getRoomMovieListByMoveID($cinemaId, $movieId,$iUserID);
		foreach ($arCinemaInfo as $k=>&$row)
		{
			$i = 0;
			if(!empty($arRoomMovieList)){
				foreach ($arRoomMovieList as $key =>$v)
				{
					$key = date("Y-m-d",strtotime($v['dBeginTime']));
					if ($key<date("Y-m-d"))
					{
						continue;
					}
					if($row['iCinemaID'] != $v['iEpiaoCinemaID']){
						continue;
					}
					$days=round((strtotime($key)-$dateDay)/86400);
					if ($days>6)
					{
						continue;
					}

					if($i == 0){
						$row['dist'] = round(LBS::getDistance(explode(',',$row['sCoordinates'])[1],explode(',',$row['sCoordinates'])[0],$lat1,$lng1)/1000,1);
					}
					if(mb_strlen($row['sAddress'],'utf-8') >= 18){
						$row['sAddress'] = mb_substr($row['sAddress'],0,18,'utf-8').'...';
						if(mb_strlen($row['sAddress']) >= 60){
							$row['sAddress'] = mb_substr($row['sAddress'],0,15,'utf-8').'...';
						}
					}
					$row['bIsFeature'] = array('bIsCarPark'=> $row['bIsCarPark'],
						'bIs4D'=> $row['bIs4D'],
						'bIs4K'=> $row['bIs4K'],
						'bIsIMAX'=> $row['bIsIMAX'],
						'bIsJUMU'=> $row['bIsJUMU'],
						'bIsDUBI'=> $row['bIsDUBI'],
						'bIsEAT'=> $row['bIsEAT'],
						'iHotCinema'=> $row['iHotCinema']
					);
					if(explode('.',$v['minPrice'])[1] == 0){
						$row['iMinPrice'] = explode('.',$v['minPrice'])[0];
					}else{
						$row['iMinPrice'] = round($v['minPrice'],1);
					}
					$moviecinemaInfo['aDateList'][$days]['sDate'] = PublicProcess::dateTime($days,$key);
					$moviecinemaInfo['aDateList'][$days]['aCinemaList'][] = $row;
					$i++;
				}
			}
		}
		//计时结束.
		//return self::runtime(1);
		ksort($moviecinemaInfo['aDateList']);
		$moviecinemaInfo['aDateList'] = array_values($moviecinemaInfo['aDateList']);
		foreach($moviecinemaInfo['aDateList'] as &$v){
			usort($v['aCinemaList'],function($a, $b){
				if ($a['dist'] < $b['dist'])
					return -1;
				else if ($a['dist'] == $b['dist'])
					return 0;
				else
					return 1;
			});
		}

		return array('nErrCode'=>0, 'nResult'=>$moviecinemaInfo,'time'=>self::runtime(1));
	}

	/**
	 * 获取影片排期
	 *
	 * @param array[] $param 参数列表
	 * @return array[] 影片详情信息
	 */
	public static function getMovieArrangeList($param)
	{
		if(isset($param['movieID']) && !empty($param['movieID'])){
			$movieInfo = MovieProcess::getMovieInfoByMovieID($param['movieID']);
			if (empty($movieInfo))
			{
				return GeneralFunc::returnErr(ErrorParse::getErrorNo('param_error'), ErrorParse::getErrorDesc('param_error').'该影片不存在！');
			}
		}
		$iUserID=0;
		if(isset($param["iUserID"])&&!empty($param["iUserID"]))
		{
			$iUserID=$param["iUserID"];
		}
		$cityInfo = CityProcess::getCityInfoByCityId($param['cityID']);
		if (empty($cityInfo))
		{
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('param_error'), ErrorParse::getErrorDesc('param_error').'该城市不存在！');
		}
		$arCinemaInfo = CinemaProcess::getCinemaListByCinemaID($param['cinemaID'],array('iCinemaID','sCinemaName','sRegion','sAddress','sTel'));

		if (empty($arCinemaInfo))
		{
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('param_error'), ErrorParse::getErrorDesc('param_error').'该影院不存在！');
		}
		$movieArrangeInfo = array();
		$dateDay = strtotime(date("Y-m-d"));
		//计时开始
		self::runtime();
		foreach ($arCinemaInfo as $key=>&$row)
		{
			$arRoomMovieList = CinemaProcess::getRoomMovieListByCinemaId($row['iCinemaID'],$iUserID);
			if (!empty($arRoomMovieList)) {
				foreach ($arRoomMovieList as $key => &$v) {
					$movieArrangeInfo['iMovieID'] = $param['movieID'];
					$movieArrangeInfo['sCinemaName'] = $row['sCinemaName'];
					$movieArrangeInfo['sTel'] = $row['sTel'];
					$movieArrangeInfo['sAddress'] = $row['sAddress'];
					if (FALSE == empty($v['sImageUrl'])) {
						//@todo 后续需改为配置
						$v['sImageUrl'] = Yii::app()->params['baseUrl']. $v['sImageUrl'];
						$movieArrangeInfo['aMovieList'][] = $v;
					}
				}
			}
		}
		foreach($movieArrangeInfo['aMovieList'] as $k => $v){
			uksort($movieArrangeInfo['aMovieList'][$k]['aDateList'],function($a, $b){
				if ($a < $b)
					return -1;
				else if ($a == $b)
					return 0;
				else
					return 1;
			});
			$movieArrangeInfo['aMovieList'][$k]['aDateList'] = array_values($movieArrangeInfo['aMovieList'][$k]['aDateList']);
		}
		return array('nErrCode'=>0, 'nResult'=>$movieArrangeInfo,'time'=>self::runtime(1));
	}


	/**
	 * 获取影厅座位图
	 *
	 * @param array[] $param 参数列表
	 * @return array[] 影片详情信息
	 */
	public static function getMovieSeatList($param)
	{
		$aSeatList = array();

		if(empty($param['roommovieID']))
		{
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('param_error'), ErrorParse::getErrorDesc('param_error').'该影厅id为空！');
		}
		$arArrangeInfo = CinemaProcess::getRoomMovieListByiRoommovieID($param['roommovieID']);

		if (empty($arArrangeInfo))
		{
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('param_error'), ErrorParse::getErrorDesc('param_error').'该影厅没有排期！');
		}
		//获取影厅座位图状态
		$arRoomInfo = CinemaProcess::getRoomInfo($arArrangeInfo['iRoomID']);
		if (empty($arRoomInfo['sSeatInfo']))
		{
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('param_error'), ErrorParse::getErrorDesc('param_error').'该影厅没有没有相关座位图！');
		}
		$aSeatList['sCinemaName'] = $arArrangeInfo['sCinemaName'];
		$aSeatList['sMovieName'] = $arArrangeInfo['sMovieName'];
		$aSeatList['sRoomName'] = $arArrangeInfo['sRoomName'];
		$aSeatList['sDimensional'] = $arArrangeInfo['sDimensional'];
		$aSeatList['sLanguage'] = $arArrangeInfo['sLanguage'];
		$day = date("Y-m-d",strtotime($arArrangeInfo['dBeginTime']));
		$dateDay = strtotime(date("Y-m-d"));
		$days=round((strtotime($day)-$dateDay)/86400);
		$time = date("H:i",strtotime($arArrangeInfo['dBeginTime']));
		$aSeatList['dBeginTime'] = PublicProcess::dateTime($days,$day,$time);
		$arSeatInfo =json_decode($arRoomInfo['sSeatInfo'], true);
		$bookSeatingArrange['sRoomMovieInterfaceNo'] = $arArrangeInfo['sRoomMovieInterfaceNo'];
		$bookSeatingArrange['iInterfaceID'] = $arArrangeInfo['iInterfaceID'];
		$bookSeatingArrange['sCinemaInterfaceNo'] = $arArrangeInfo['sCinemaInterfaceNo'];
		$bookSeatingArrange['sRoomInterfaceNo'] = $arArrangeInfo['sRoomInterfaceNo'];
		$arLockSeatInfo = CinemaInterfaceProcess::GetSelectedSeat($bookSeatingArrange);
		if(empty($arLockSeatInfo)){
			foreach($arSeatInfo['seatinfo'] as $k => &$seatInfo)
			{
				$aSeatList['aSeatList'][$k]['seatRow'] = $seatInfo['seatRow'];
				$aSeatList['aSeatList'][$k]['seatCol'] = $seatInfo['seatCol'];
				$aSeatList['aSeatList'][$k]['graphRow'] = $seatInfo['graphRow'];
				$aSeatList['aSeatList'][$k]['graphCol'] = $seatInfo['graphCol'];
				$aSeatList['aSeatList'][$k]['seatNo'] = $seatInfo['SeatNo'];
				$aSeatList['aSeatList'][$k]['seatState'] = $seatInfo['seatState'];
				$aSeatList['aSeatList'][$k]['SeatId'] = 0;
				if($bookSeatingArrange['iInterfaceID'] == 11){
					$aSeatList['aSeatList'][$k]['SeatId'] = $aSeatList['aSeatList'][$k]['seatNo'];
					$aSeatList['aSeatList'][$k]['seatNo'] = $seatInfo['seatRow'] .':'.$seatInfo['seatCol'];
				}else{
					if(count(explode(':',$aSeatList['aSeatList'][$k]['seatNo']))==1){
						$aSeatList['aSeatList'][$k]['SeatId'] = $aSeatList['aSeatList'][$k]['seatNo'];
						$aSeatList['aSeatList'][$k]['seatNo'] = $seatInfo['seatRow'] .':'.$seatInfo['seatCol'];
					}
				}
			}
		}else{
			foreach ($arLockSeatInfo as $lockSeatInfo)
			{
				foreach($arSeatInfo['seatinfo'] as $k => &$seatInfo)
				{
					$aSeatList['aSeatList'][$k]['seatRow'] = $seatInfo['seatRow'];
					$aSeatList['aSeatList'][$k]['seatCol'] = $seatInfo['seatCol'];
					$aSeatList['aSeatList'][$k]['graphRow'] = $seatInfo['graphRow'];
					$aSeatList['aSeatList'][$k]['graphCol'] = $seatInfo['graphCol'];
					$aSeatList['aSeatList'][$k]['seatNo'] = $seatInfo['SeatNo'];
					$aSeatList['aSeatList'][$k]['SeatId'] = 0;
					if (($lockSeatInfo['ColumnId']  == $seatInfo['seatCol'] && $lockSeatInfo['RowId']  == $seatInfo['seatRow']) || $lockSeatInfo['SeatId'] == $seatInfo['SeatNo'])
					{
						$seatInfo['seatState'] = $lockSeatInfo['SeatStatus'];
					}
					if($bookSeatingArrange['iInterfaceID'] == 11){
						$aSeatList['aSeatList'][$k]['SeatId'] = $aSeatList['aSeatList'][$k]['seatNo'];
						$aSeatList['aSeatList'][$k]['seatNo'] = $seatInfo['seatRow'] .':'.$seatInfo['seatCol'];
					}else{
						if(count(explode(':',$aSeatList['aSeatList'][$k]['seatNo']))==1){
							$aSeatList['aSeatList'][$k]['SeatId'] = $aSeatList['aSeatList'][$k]['seatNo'];
							$aSeatList['aSeatList'][$k]['seatNo'] = $seatInfo['seatRow'] .':'.$seatInfo['seatCol'];
						}
					}

					$aSeatList['aSeatList'][$k]['seatState'] = $seatInfo['seatState'];
				}
			}
		}
		return array('nErrCode'=>0, 'nResult'=>$aSeatList);
	}

	//提交选座订单
	public static function subSeatorder($param){

		$iRoomMovieID=$param['roommovieID'];
		$seat_no=  $param['seatNo'];
		$seat_info=  $param['seatInfo'];
		if(!isset($param["iUserID"]) || empty($param["iUserID"]))
		{
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('user_not_login'), ErrorParse::getErrorDesc('user_not_login').'用户登陆失败，请重新登录！');
		}
		if(isset($param["seatId"]) && !empty($param["seatId"])){
			$seat_no=  $param['seatId'];
		}
		$iUserID=$param["iUserID"];

		$sendPhone = UserProcess::getUInfo($iUserID)['sPhone'];

		$type=ConfigParse::getOrdersKey('onlineSeatOrder');
		$returnUrl="";
		$iSelectedCount=count(explode("@@",$seat_no));
		if ($iSelectedCount<=0)
		{
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('user_not_login'), ErrorParse::getErrorDesc('user_not_login').'请至少选择一个座位。');
		}

		$hour = date("H");
		if (($hour>=0 && $hour<9) || $hour>=22){
			return array("nErrCode"=>-504,'nDescription'=>'亲爱的用户，22点至次日9点进行系统维护，暂停在线选座功能，如有问题可留言至微信公众号（epiaowang），工作人员会第一时间联系您。');
		}

		$roomMovieInfo= CinemaProcess::getRoomMovieListByiRoommovieID($iRoomMovieID);
		if (!empty($roomMovieInfo['iMovieID']))
		{
			$roomMovieInfo['movieImg'] = '';
			$movieInfo = MovieProcess::getMovieInfoByMovieID($roomMovieInfo['iMovieID']);
			if (!empty($movieInfo))
			{
				$roomMovieInfo['movieImg'] = $movieInfo['sSmallImageUrl'];
			}
		}else{
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('param_error'), ErrorParse::getErrorDesc('param_error').'该影厅没有排期！');
		}

		$dEndBuyTime = strtotime($roomMovieInfo['dEndBuyDate']);

		if ($dEndBuyTime<time())
		{
			return array("nErrCode"=>-503,'nDescription'=>'场次已过期');
		}

		$iHuoDongItemID=33;

		$isByOrder = OrderProcess::getOrderByiRoommovieID($iRoomMovieID,$seat_no);
		if($isByOrder){
			return array("nErrCode"=>-502,'nDescription'=>'座位已被锁定');
		}

		$orderId = OrderProcess::getUserIunfinishOrder($iUserID);
		if($orderId){
			foreach($orderId as $v){
				if (!empty($v['sInterfaceOrderNo']) && (900-(time()- strtotime($v["dCreateTime"])))>0 && $v['isWeChatapplet'] == 1) {
					$arResult = CinemaInterfaceProcess::GetCancelOrderResult($v['outerOrderId']);
					if($arResult['ResultCode'] == ConfigParse::getOrderStatusKey('ORDER_STATUS_Successed')){
						if(!OrderProcess::upUserIunfinishOrder($v['outerOrderId'])){
							return GeneralFunc::returnErr(ErrorParse::getErrorNo('order_nofind'), ErrorParse::getErrorDesc('order_nofind').'取消订单失败。');
						}else{
							//取消订单退点退券
							$payloginfo = OrderProcess::getOrderPaylogForOuterOrderId($v['outerOrderId']);
							if($payloginfo){
								foreach($payloginfo as $val){
									if($val['bankType'] == ConfigParse::getPayTypeKey('cardPay')){
										CouponProcess::updateCouponiUsedunFlag($val['sCheckNo'],$val['cardCount']);
									}
									if($val['bankType'] == ConfigParse::getPayTypeKey('cashPay')){
										VoucherProcess::updateVoucheriUsedunFlag($val['sVoucherPassWord']);
									}
								}
								OrderProcess::delUserPayOrderInfo($v['outerOrderId']);
							}
						}
					}else{
						return GeneralFunc::returnErr(ErrorParse::getErrorNo('order_nofind'), ErrorParse::getErrorDesc('order_nofind').'取消订单失败。');
					}
				}
			}
		}
		$rel=OrderProcess::createSeatOnlinOrder($iUserID, $iRoomMovieID, $sendPhone, $iSelectedCount, $seat_no, $seat_info, 'epiaowang', $returnUrl, $iHuoDongItemID, $type);
		$rel2= CinemaInterfaceProcess::GetCreateOrderResult($rel["data"]["outerOrderId"]);
		if($rel2["ResultCode"]==ConfigParse::getOrderStatusKey('ORDER_STATUS_Successed'))
		{
			$orderSeatInfo=array(ConfigParse::getOrderSeatInfoKey('userId')=>$iUserID, ConfigParse::getOrderSeatInfoKey('outerOrderId')=>$rel["data"]["outerOrderId"],ConfigParse::getOrderSeatInfoKey('sInterfaceOrderNo')=>$rel2["InterfaceOrderNo"]);
			$result = OrderProcess::updateOrderSeatByOrderInfo($iUserID, $orderSeatInfo);
			if(!$result['ok']){
				if($result['err_code'] == 201){
					return GeneralFunc::returnErr(ErrorParse::getErrorNo('user_not_exist'), ErrorParse::getErrorDesc('user_not_exist'));
				}
			}
		}
		else
		{
			OrderProcess::delUserOrdersInfo($rel["data"]["outerOrderId"]);
			OrderProcess::delUserOrderSeatInfo($rel["data"]["outerOrderId"]);
			return array("nErrCode"=>-502,'nDescription'=>'座位已被锁定');
		}
		if($rel["ok"])
		{
			return array("nErrCode"=>0,"nResult"=>$rel["data"]["outerOrderId"]);
		}
		else {
			return array("nErrCode"=>-1,"nResult"=>$rel["data"]);
		}
	}
	//获取订单信息
	public static function getOrderInfo($param)
	{
		$outerOrderId = $param['outerOrderId'];
		if(!isset($outerOrderId) || empty($outerOrderId)){
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('param_error'), ErrorParse::getErrorDesc('param_error').'该订单不存在！');
		}
		if(!isset($param["iUserID"]) || empty($param["iUserID"]))
		{
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('user_not_login'), ErrorParse::getErrorDesc('user_not_login').'用户登陆失败，请重新登录！');
		}
		$iUserID=$param["iUserID"];
		$userOrderInfo = OrderProcess::getOrderSeatInfoByOuterOrderId($outerOrderId);
		$sendPhone = UserProcess::getUInfo($iUserID,array('sPhone'));
		if(empty($userOrderInfo))
		{
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('order_nofind'), ErrorParse::getErrorDesc('order_nofind').'该订单不存在');
		}

		if ($userOrderInfo['iUserId'] != $iUserID)
		{
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('order_nofind'), ErrorParse::getErrorDesc('order_nofind').'该订单不属于您');
		}

		$userOrderInfo["createTime"] = 900-(time()- strtotime($userOrderInfo["createTime"]));  //判断该订单是否超过15分钟

		if ($userOrderInfo["createTime"]<=0)
		{
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('order_nofind'), ErrorParse::getErrorDesc('order_nofind').'订单已过期');
		}

		if ($userOrderInfo['orderStatus'] != ConfigParse::getPayStatusKey('orderNoPay'))
		{
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('order_nofind'), ErrorParse::getErrorDesc('order_nofind').'该笔订单不是待支付订单');
		}
		$arArrangeInfo= CinemaProcess::getRoomMovieListByiRoommovieID($userOrderInfo['iRoomMovieID']);
		if (empty($arArrangeInfo))
		{
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('order_nofind'), ErrorParse::getErrorDesc('order_nofind').'该场次已过期，请重新选择场次');
		}

		if (empty($userOrderInfo['sInterfaceOrderNo']))
		{
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('order_nofind'), ErrorParse::getErrorDesc('order_nofind').'锁座失败，请重新尝试锁座');
		}

		$couponNum = ceil($arArrangeInfo['mPrice']);
		$roomId = $arArrangeInfo['iRoomID'];
		if($arArrangeInfo['sDimensional'] == 'IMAX')
		{
			$mPriceIMAX = explode(".",$userOrderInfo['mPrice'])[0]/2;
		}else{
			$mPriceIMAX = explode(".",$userOrderInfo['mPrice'])[0];
		}

		$arCouponList = CouponProcess::getUserCouponForCinemaIdAndMovieId($iUserID,$mPriceIMAX,$userOrderInfo['iCinemaID'],$userOrderInfo['iMovieID'],$couponNum,$arArrangeInfo['sDimensional'],$roomId);
		$arVoucherList = VoucherProcess::getUserVoucherListForBookSeat($iUserID,$userOrderInfo['mPrice'],$userOrderInfo['iownSeats'],$userOrderInfo['iMovieID']);

		$arRetCouponList = array();
		$arRetVoucherList = array();
		foreach ($arCouponList as $v)
		{
			$arRetCouponList[$v['sCheckNo']]['sCheckNo'] = $v['sCheckNo']; //卡号
			$arRetCouponList[$v['sCheckNo']]['couponId'] = $v['iCouponID']; //卡类型
			$arRetCouponList[$v['sCheckNo']]['sCouponName'] = $v['sCouponName']; //卡名称
			$arRetCouponList[$v['sCheckNo']]['validcount']=$v['validcount']; //剩余总点数
			$arRetCouponList[$v['sCheckNo']]['couponpay']=$v['couponpay'];  //单价
			$arRetCouponList[$v['sCheckNo']]['dEndTime'] =$v['dEndTime'];    //有效期
		}
		foreach ($arVoucherList as $v)
		{
			$arRetVoucherList[$v['sVoucherPassWord']]['sVoucherPassWord'] = $v['sVoucherPassWord']; //券号
			$arRetVoucherList[$v['sVoucherPassWord']]['iVoucherUseCount'] = $v['iVoucherUseCount']; //券限制
			$arRetVoucherList[$v['sVoucherPassWord']]['sVoucherName'] = $v['sVoucherName']; //券名称
			$arRetVoucherList[$v['sVoucherPassWord']]['dVaildEndTime']=$v['dVaildEndTime']; //有效期
			$arRetVoucherList[$v['sVoucherPassWord']]['mVoucherMoney']=$v['mVoucherMoney'];  //券单价
		}

		$day = date("Y-m-d",strtotime($userOrderInfo['dPlayTime']));
		$dateDay = strtotime(date("Y-m-d"));
		$days=round((strtotime($day)-$dateDay)/86400);
		$time = date("H:i",strtotime($userOrderInfo['dPlayTime']));
		$seatInfo = array();
		foreach(explode(',',$userOrderInfo['orderInfo']) as $k => $v){
			$seatInfo[$k] = $v.'座';
		}
		//从待支付订单进来如果存在已经paylog则删除重新生成订单并且退点退券
		$payloginfo = OrderProcess::getOrderPaylogForOuterOrderId($outerOrderId);
		if($payloginfo){
			foreach($payloginfo as $val){
				if($val['bankType'] == ConfigParse::getPayTypeKey('cardPay')){
					CouponProcess::updateCouponiUsedunFlag($val['sCheckNo'],$val['cardCount']);
				}
				if($val['bankType'] == ConfigParse::getPayTypeKey('cashPay')){
					VoucherProcess::updateVoucheriUsedunFlag($val['sVoucherPassWord']);
				}
			}
			OrderProcess::delUserPayOrderInfo($outerOrderId);
		}

		return array("nErrCode"=>0,"nResult"=>array('orderInfo'=>array('outerOrderId' => $userOrderInfo['outerOrderId'],  //订单ID
			'sCinemaName' =>$userOrderInfo['sCinemaName'],   //影院名
			'sMovieName' =>$userOrderInfo['sMovieName'],    //影片名
			'sRoomName' =>$userOrderInfo['sRoomName'],    //影厅名
			'sDimensional'=>$userOrderInfo['sDimensional'],   //影片类型2D/3D/IMAX
			'orderInfo' => $seatInfo,   //座位信息
			'dPlayTime' =>PublicProcess::dateTime($days,$day,$time), //开始时间
			'totalPrice' =>$userOrderInfo['totalPrice'], //总价
			'sPhone' =>$sendPhone['sPhone'], //默认手机号
			'mPrice' =>$userOrderInfo['mPrice'], //单价
			'countdown' =>$userOrderInfo["createTime"],    //倒计时
		),
			'couponInfo'=>array_values($arRetCouponList),        //被返回的数组将使用数值键，从 0 开始并以 1 递增。
			'voucherInfo'=>array_values($arRetVoucherList),        //被返回的数组将使用数值键，从 0 开始并以 1 递增。
			'usermoney'=>  UserProcess::getmAccountMoney($iUserID)));
	}
	//完成订单
	public static function subnoPayorder($param)
	{
		if(!isset($param["iUserID"]) || empty($param["iUserID"]))
		{
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('user_not_login'), ErrorParse::getErrorDesc('user_not_login').'用户登陆失败，请重新登录！');
		}
		if(!isset($param["sPhone"]) || empty($param["sPhone"]))
		{
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('param_error'), ErrorParse::getErrorDesc('param_error').'手机不能为空！');
		}
		$iUserID=$param["iUserID"];
		$sendPhone = $param["sPhone"];
		if(!preg_match("/^1[34578]\d{9}$/", $sendPhone)){
			return array("nErrCode"=>-601,'nDescription'=>'请填写正确手机号');
		}
		if(!isset($param['outerOrderId']) || empty($param['outerOrderId'])){
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('param_error'), ErrorParse::getErrorDesc('param_error').'该订单不存在！');
		}
		$outerOrderId = $param['outerOrderId'];

		$userOrderInfo = OrderProcess::getOrderSeatInfoByOuterOrderId($outerOrderId);
		if(empty($userOrderInfo))
		{
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('order_nofind'), ErrorParse::getErrorDesc('order_nofind').'该订单不存在');
		}

		if ($userOrderInfo['iUserId'] != $iUserID)
		{
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('order_nofind'), ErrorParse::getErrorDesc('order_nofind').'该订单不属于您');
		}

		$userOrderInfo["createTime"] = 900-(time()- strtotime($userOrderInfo["createTime"]));  //判断该订单是否超过15分钟

		if ($userOrderInfo["createTime"]<=0)
		{
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('order_nofind'), ErrorParse::getErrorDesc('order_nofind').'订单已过期');
		}

		if ($userOrderInfo['orderStatus'] != ConfigParse::getPayStatusKey('orderNoPay'))
		{
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('order_nofind'), ErrorParse::getErrorDesc('order_nofind').'该笔订单不是待支付订单');
		}

		if (empty($userOrderInfo['sInterfaceOrderNo']))
		{
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('order_nofind'), ErrorParse::getErrorDesc('order_nofind').'锁座失败，请重新尝试锁座');
		}
		//先删除该订单号下的所有待支付订单-仅是payLog表，在重新生成订单
		$payloginfo = OrderProcess::getOrderPaylogForOuterOrderId($outerOrderId);
		if($payloginfo){
			foreach($payloginfo as $val){
				if($val['bankType'] == ConfigParse::getPayTypeKey('cardPay')){
					CouponProcess::updateCouponiUsedunFlag($val['sCheckNo'],$val['cardCount']);
				}
				if($val['bankType'] == ConfigParse::getPayTypeKey('cashPay')){
					VoucherProcess::updateVoucheriUsedunFlag($val['sVoucherPassWord']);
				}
			}
			OrderProcess::delUserPayOrderInfo($outerOrderId);
		}
		if(!isset($param['code']) || empty($param['code'])){
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('param_error'), ErrorParse::getErrorDesc('param_error').'用户code为空！');
		}
		$code = $param['code'];
		$openid = PublicProcess::code($code);
		UserProcess::getOpenid(UserProcess::getUInfo($iUserID)['sPhone'],$openid);
		$payType1 = (!isset($param['payType']) || empty($param['payType'])) ? 0 : $param['payType'];
		if($payType1 == "1|2"){
			$payType1 = "2|1";
		}
		$payLogType = explode('|',$payType1);
		foreach($payLogType as $payType){

			$flag = 0;
			if($payType == 0){   //电影卡支付
				if(!isset($param['sCheckNo']) || empty($param['sCheckNo'])){
					return GeneralFunc::returnErr(ErrorParse::getErrorNo('param_error'), ErrorParse::getErrorDesc('param_error').'请选择电影卡！');
				}
				if(!isset($param['count']) || empty($param['count'])){
					return GeneralFunc::returnErr(ErrorParse::getErrorNo('param_error'), ErrorParse::getErrorDesc('param_error').'请选择座位！');
				}
				if(!isset($param['cardCount']) || empty($param['cardCount'])){
					return GeneralFunc::returnErr(ErrorParse::getErrorNo('param_error'), ErrorParse::getErrorDesc('param_error').'单价不得为空！');
				}
				if(!isset($param['couponId']) || empty($param['couponId'])){
					return GeneralFunc::returnErr(ErrorParse::getErrorNo('param_error'), ErrorParse::getErrorDesc('param_error').'卡类型不得为空！');
				}
				$sCheckNo =  $param['sCheckNo'];
				$count = $param['count'];   //张数
				$userOrderInfoalidcount = $param['validcount'];   //剩余的点数
				$cardCount = $param['cardCount'];    //单价（每张票消耗的点数）
				$couponId = $param['couponId'];
				$minCount = floor($userOrderInfoalidcount/$cardCount);   //电影卡最少支付的张数
				if($minCount < $count){
					$count = $minCount;
				}
				$totPrice = $userOrderInfo['mPrice'] * $count;    //支付的总价
				$payloginfo = OrderProcess::getOrderPaylogForOuterOrderId($outerOrderId);
				if($payloginfo){
					foreach($payloginfo as $val){
						if($outerOrderId == $val['outerOrderId'] && $sCheckNo == $val['sCheckNo']){
							break;
						}else{
							$flag = OrderProcess::addCardPayForSeatBycardPay($outerOrderId, $sCheckNo,$iUserID, $count, $cardCount,$couponId,$count*$cardCount,$payType,'',$totPrice,'');
							if(!$flag){
								return GeneralFunc::returnErr(ErrorParse::getErrorNo('order_nofind'), ErrorParse::getErrorDesc('order_nofind').'paylog插入失败！');
							}
							break;
						}
					}
				}else{
					$flag = OrderProcess::addCardPayForSeatBycardPay($outerOrderId, $sCheckNo,$iUserID, $count, $cardCount,$couponId,$count*$cardCount,$payType,'',$totPrice,'');
					if(!$flag){
						return GeneralFunc::returnErr(ErrorParse::getErrorNo('order_nofind'), ErrorParse::getErrorDesc('order_nofind').'paylog插入失败！');
					}
				}
			}elseif($payType == 1) { //余额支付
				$sign = 1;
				if(!isset($param['count']) || empty($param['count'])){
					return GeneralFunc::returnErr(ErrorParse::getErrorNo('param_error'), ErrorParse::getErrorDesc('param_error').'请选择座位！');
				}
				$count = $param['count'];
				$mAccountMoney = UserProcess::getmAccountMoney($iUserID);
				$payloginfo = OrderProcess::getOrderPaylogForOuterOrderId($outerOrderId);
				if($payloginfo){
					foreach($payloginfo  as $k => $v){
						if($v['sCheckNo']){
							if($userOrderInfo['totalPrice'] - $v['totalPrice']<=0){
								$sign = 0;
								break;
							}else{
								if($mAccountMoney<($userOrderInfo['totalPrice'] - $v['totalPrice'])){
									CouponProcess::updateCouponiUsedunFlag($v['sCheckNo'],$v['cardCount']);
									OrderProcess::delUserPayOrderInfo($v['outerOrderId']);
									return array("nErrCode"=>-602,'nDescription'=>'余额不足');
								}else{
									$count = $count - $v['count'];
									$mAccountMoney = ($userOrderInfo['totalPrice'] - $v['totalPrice']);
								}
							}
						}elseif($v['sVoucherPassWord']){
							if($userOrderInfo['totalPrice'] - $v['totalPrice']<=0){
								$sign = 0;
								break;
							}else{
								if($mAccountMoney<($userOrderInfo['totalPrice'] - $v['totalPrice'])){
									VoucherProcess::updateVoucheriUsedunFlag($v['sVoucherPassWord']);
									OrderProcess::delUserPayOrderInfo($v['outerOrderId']);
									return array("nErrCode"=>-602,'nDescription'=>'余额不足');
								}else{
									$count = $count - $v['count'];
									$mAccountMoney = ($userOrderInfo['totalPrice'] - $v['totalPrice']);
								}
							}
						}else{
							if($mAccountMoney < $userOrderInfo['totalPrice']){
								return array("nErrCode"=>-602,'nDescription'=>'余额不足');
							}else{
								$mAccountMoney = $userOrderInfo['totalPrice'];
							}
							break;
						}
					}

				}else{
					if($mAccountMoney < $userOrderInfo['totalPrice']){
						return array("nErrCode"=>-602,'nDescription'=>'余额不足');
					}else{
						$mAccountMoney = $userOrderInfo['totalPrice'];
					}
				}
				if($sign == 0){
					continue;
				}
				$flag = OrderProcess::addCardPayForSeatBycardPay($outerOrderId, '',$iUserID, $count, '','','',$payType,'',$mAccountMoney,'');
				if(empty($flag)){
					return GeneralFunc::returnErr(ErrorParse::getErrorNo('order_nofind'), ErrorParse::getErrorDesc('order_nofind').'paylog插入失败！');
				}
			} elseif($payType == 2) { //现金券低值
				if (!isset($param['count']) || empty($param['count'])) {
					return GeneralFunc::returnErr(ErrorParse::getErrorNo('param_error'), ErrorParse::getErrorDesc('param_error') . '请选择座位！');
				}
				if (!isset($param['sVoucherPassWord']) || empty($param['sVoucherPassWord'])) {
					return GeneralFunc::returnErr(ErrorParse::getErrorNo('param_error'), ErrorParse::getErrorDesc('param_error') . '请选择一种现金券！');
				}
				$sVoucherPassWord = $param['sVoucherPassWord'];
				$voucherInfo = VoucherProcess::getVoucherBaseInfoBysVoucherPassWord($sVoucherPassWord,array('dVaildEndTime','mVoucherMoney'));  //现金券
				if (strtotime($voucherInfo['dVaildEndTime']-time()<0))
				{
					return array("nErrCode"=>-804,"nDescription"=>"您输入的现金券已过期。");
				}
				$payloginfo = OrderProcess::getOrderPaylogForOuterOrderId($outerOrderId);

				if($payloginfo){
					foreach($payloginfo as $val){
						if($outerOrderId == $val['outerOrderId'] && $sVoucherPassWord == $val['sVoucherPassWord']){
							break;
						}else{
							$flag = OrderProcess::addCardPayForSeatBycardPay($outerOrderId, '',$iUserID, 0, 0,'','',$payType,'',$voucherInfo['mVoucherMoney'],$sVoucherPassWord);
							if(!$flag){
								return GeneralFunc::returnErr(ErrorParse::getErrorNo('order_nofind'), ErrorParse::getErrorDesc('order_nofind').'paylog插入失败！');
							}
							break;
						}
					}
				}else{
					$flag = OrderProcess::addCardPayForSeatBycardPay($outerOrderId, '',$iUserID, 0, 0,'','',$payType,'',$voucherInfo['mVoucherMoney'],$sVoucherPassWord);
					if(!$flag){
						return GeneralFunc::returnErr(ErrorParse::getErrorNo('order_nofind'), ErrorParse::getErrorDesc('order_nofind').'paylog插入失败！');
					}
				}
			}elseif($payType == 3){    //微信支付
				$mAccountMoney = $userOrderInfo['totalPrice'];
				if(!isset($param['count']) || empty($param['count'])){
					return GeneralFunc::returnErr(ErrorParse::getErrorNo('param_error'), ErrorParse::getErrorDesc('param_error').'请选择座位！');
				}
				$count = $param['count'];
				$payloginfo = OrderProcess::getOrderPaylogForOuterOrderId($outerOrderId);
				if($payloginfo){
					foreach($payloginfo  as $k => $v){
						if($v['sCheckNo']){
							if($userOrderInfo['totalPrice'] - $v['totalPrice']<=0){
								$mAccountMoney = 0;
								break;
							}else{
								$count = $count - $v['count'];
								$mAccountMoney = ($userOrderInfo['totalPrice'] - $v['totalPrice']);
							}
						}elseif($v['sVoucherPassWord']){
							if($userOrderInfo['totalPrice'] - $v['totalPrice']<=0){
								$mAccountMoney = 0;
								break;
							}else{
								$count = $count - $v['count'];
								$mAccountMoney = ($userOrderInfo['totalPrice'] - $v['totalPrice']);
							}
						}else{
							break;
						}
					}
				}
				if($mAccountMoney == 0){
					continue;
				}
				$flag = OrderProcess::addCardPayForSeatBycardPay($outerOrderId, '',$iUserID, $count, '','','',$payType,$openid,$mAccountMoney,'');

				if(empty($flag)){
					continue;
				}
				return array("nErrCode"=>0,"nResult"=>array('weixinPay'=>$flag,'outerOrderId'=>$outerOrderId));
			}
		}
		$formId = "";
		if(isset($param['formId']) && !empty($param['formId'])){
			$formId = $param['formId'];
		}
		$res = OrderProcess::confirmSeatOnlineOrderCardPay($iUserID,$outerOrderId,$sendPhone,$formId);
		if($res['ok']){
			return array("nErrCode"=>0,"nResult"=>array('orderInfo'=>array('outerOrderId'=>$res['outerOrderId'])));
		}
		return array("nErrCode"=>-603,"nResult"=>$res['msg']);
	}

	public static function subsuccessOrder($param){

		if(!isset($param['outerOrderId']) || empty($param['outerOrderId'])){
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('param_error'), ErrorParse::getErrorDesc('param_error').'该订单不存在！');
		}
		$outerOrderId = $param['outerOrderId'];

		$userOrderInfo = OrderProcess::getOrderSeatInfoByOuterOrderId($outerOrderId);

		if(empty($userOrderInfo))
		{
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('order_nofind'), ErrorParse::getErrorDesc('order_nofind').'该订单不存在');
		}

		$orderInfo = array();
		$dPlayDate = explode('-',explode(' ',$userOrderInfo['dPlayTime'])[0])[1].'月'.explode('-',explode(' ',$userOrderInfo['dPlayTime'])[0])[2].'日';
		$dPlayTime = substr(explode(' ',$userOrderInfo['dPlayTime'])[1],0,5);
		$orderInfo['sCinemaName'] = $userOrderInfo['sCinemaName'];
		$orderInfo['sRoomName'] = $userOrderInfo['sRoomName'];
		$seatInfo = array();
		foreach(explode(',',$userOrderInfo['orderInfo']) as $k => $v){
			$seatInfo[$k] = $v.'座';
		}
		$orderInfo['seatInfo'] = $seatInfo;
		$orderInfo['sMovieName'] = $userOrderInfo['sMovieName'];
		$orderInfo['dPlayDate'] = $dPlayDate;
		$orderInfo['dPlayTime'] = $dPlayTime;
		$orderInfo['dTimeLen'] = MovieProcess::getMovieInfoByMovieID($userOrderInfo['iMovieID'])['iRunTime'];
		$orderInfo['mPrice'] = $userOrderInfo['totalPrice'];
		if(!empty($userOrderInfo['sInterfaceValidCode'])){
			$arrCode0 = '';
			$arrCode1 = '';
			$errorCode0 = '';
			$errorCode1 = '';
			$reg = '/^[a-zA-Z0-9\|\*]*$/';                    // 正则句
			$proArr = str_split( $userOrderInfo['sInterfaceValidCode'] );           // 将字符串切割成数组
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
				}else{
					$errorCode0 = explode('|',$sInterfaceValidCode[1])[0];
				}
			}else{
				if(count(explode('|',$sInterfaceValidCode[0])) == 2){
					$errorCode0 = explode('|',$sInterfaceValidCode[0])[0];
					$errorCode1 = explode('|',$sInterfaceValidCode[0])[1];
				}else{
					$errorCode0 = explode('|',$sInterfaceValidCode[0])[0];
				}
			}
			if(count(explode('|',$sInterfaceValidCode[0])) == 2){
				$arrCode0 = explode('|',$sInterfaceValidCode[0])[0];
				$arrCode1 = explode('|',$sInterfaceValidCode[0])[1];
			}else{
				$arrCode0 = explode('|',$sInterfaceValidCode[0])[0];
			}
		}else{
			$orderInfo['sInterfaceValidCode'] = array(
				'type' =>0,
				'ValidCode' =>array(
					'arrCode0' => '正在出票中...'
				)
			);
		}
		if((!empty($arrCode0) && !empty($arrCode1) &&!empty($errorCode0) &&!empty($errorCode1)) && (($arrCode0 != $errorCode0) && ($arrCode0 != $errorCode1) &&($arrCode1 != $errorCode0)&&($arrCode1 != $errorCode1))){
			$type = '1';
			//双码不一致
			$orderInfo['sInterfaceValidCode'] = array(
				'type' =>$type,
				'ValidCode' =>array(
					'arrCode0' => $arrCode0,
					'arrCode1' => $arrCode1,
					'errorCode0' => $errorCode0,
					'errorCode1' => $errorCode1,
				)
			);
		}
		if((!empty($arrCode0) && !empty($arrCode1) &&!empty($errorCode0) &&!empty($errorCode1)) && (($arrCode0 == $errorCode0) && ($arrCode0 != $errorCode1) &&($arrCode1 != $errorCode0)&&($arrCode1 == $errorCode1))){
			$type = '2';
			//双码一致
			$orderInfo['sInterfaceValidCode'] = array(
				'type' =>$type,
				'ValidCode' =>array(
					'arrCode0' => $arrCode0,
					'arrCode1' => $arrCode1,
				)
			);
		}
		if((!empty($arrCode0) && !empty($arrCode1) &&!empty($errorCode0) && empty($errorCode1)) && (($arrCode0 != $errorCode0) &&($arrCode1 != $errorCode0))){
			$type = '3';
			//双单码
			$orderInfo['sInterfaceValidCode'] = array(
				'type' =>$type,
				'ValidCode' =>array(
					'arrCode0' => $arrCode0,
					'arrCode1' => $arrCode1,
					'errorCode0' => $errorCode0,
				)
			);
		}
		if((!empty($arrCode0) && empty($arrCode1) &&!empty($errorCode0) && !empty($errorCode1)) && (($arrCode0 != $errorCode0) &&($arrCode0 != $errorCode1))){
			$type = '4';
			//单双码
			$orderInfo['sInterfaceValidCode'] = array(
				'type' =>$type,
				'ValidCode' =>array('arrCode0' => $arrCode0,
					'errorCode0' => $errorCode0,
					'errorCode1' => $errorCode1)
			);
		}
		if((!empty($arrCode0) && empty($arrCode1) &&!empty($errorCode0) && empty($errorCode1)) && (($arrCode0 != $errorCode0))){
			$type = '5';
			//单码不一致
			$orderInfo['sInterfaceValidCode'] = array(
				'type' =>$type,
				'ValidCode' =>array(
					'arrCode0' => $arrCode0,
					'errorCode0' => $errorCode0,
				)
			);
		}
		if((!empty($arrCode0) && empty($arrCode1) &&!empty($errorCode0) && empty($errorCode1)) && (($arrCode0 == $errorCode0))){
			$type = '6';
			//单码一致
			$orderInfo['sInterfaceValidCode'] = array(
				'type' =>$type,
				'ValidCode' =>array(
					'arrCode0' => $arrCode0
				),
			);
		}



		if(empty($orderInfo)){
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('param_error'), ErrorParse::getErrorDesc('param_error').'订单号不存在！');
		}else{
			return array("nErrCode"=>0,"nResult"=>array('orderInfo'=>$orderInfo));
		}
	}

	//用户登录
	public static function getLoginInfo($param){
		$username = $param['phone'];
		$password = $param['password'];
		if((!isset($username) || empty($username)) || (!isset($password) || empty($password))){
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('param_error'), ErrorParse::getErrorDesc('param_error').'账户或密码不能为空！');
		}
		//epiaowang.com
		$password = strtolower(md5($password.'epiaowang.com'));
		$res = UserProcess::getLoginInfo($username,$password);
		if($res){
			return array("nErrCode"=>0,"nResult"=>array('iUserID'=>$res['iUserID'],'sNick'=>$res['sNick']));
		}else{
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('param_error'), ErrorParse::getErrorDesc('param_error').'账户或密码错误！');
		}
	}

	//用户注册
	public static function setRegister($param){
		if(!isset($param["sPhone"]) || empty($param["sPhone"]))
		{
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('param_error'), ErrorParse::getErrorDesc('param_error').'手机号不能为空！');
		}
		$sPhone = $param["sPhone"];
		if(!preg_match("/^1[34578]\d{9}$/", $sPhone)){
			return array("nErrCode"=>-601,'nDescription'=>'请填写正确手机号');
		}
		$res = UserProcess::getRegisterInfo($sPhone);
		if($res && is_array($res)){
			if(!empty($res['sPassWord'])){
				return array("nErrCode"=>0,"nResult"=>array('iUserID'=>$res['iUserID'],'sPassWord'=>$res['sPassWord']));
			}else{
				return array("nErrCode"=>0,"nResult"=>array('sPhone'=>$res['sPhone'],'sPassWord'=>''));
			}

		}else{
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('param_error'), ErrorParse::getErrorDesc('param_error').'账户或密码错误！');
		}
	}

	//设置密码
	public static function setPassWord($param){
		if(!isset($param["sPhone"]) || empty($param["sPhone"]))
		{
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('param_error'), ErrorParse::getErrorDesc('param_error').'手机号不能为空！');
		}
		$sPhone = $param["sPhone"];
		if(!preg_match("/^1[34578]\d{9}$/", $sPhone)){
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('param_error'), ErrorParse::getErrorDesc('param_error').'请填写正确手机号！');
		}

		if(!isset($param["sPassWord"]) || empty($param["sPassWord"]))
		{
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('param_error'), ErrorParse::getErrorDesc('param_error').'密码不能为空！');
		}
		$sPassWord = $param["sPassWord"];
		if(!preg_match('/^[a-zA-Z0-9]{6,15}$/', $sPassWord)){
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('param_error'), ErrorParse::getErrorDesc('param_error').'密码格式不正确！');
		}
		$sPassWord = strtolower(md5($sPassWord.'epiaowang.com'));
		$res = UserProcess::getRegisterInfo($sPhone,$sPassWord);
		if($res && is_array($res)){
			return array("nErrCode"=>0,"nResult"=>array('iUserID'=>$res['iUserID']));
		}else{
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('param_error'), ErrorParse::getErrorDesc('param_error').'设置密码失败！');
		}
	}

	//修改密码-输入密码方式
	public static function upPwdByCode($param)
	{
		if(!isset($param["sPhone"]) || empty($param["sPhone"]))
		{
			return array("nErrCode"=>-1101,'nDescription'=>'手机号不能为空！');
		}

		if(!isset($param["oldPwd"]) || empty($param["oldPwd"]))
		{
			return array("nErrCode"=>-1102,'nDescription'=>'旧密码不能为空！');
		}

		if(!isset($param["newPwd"]) || empty($param["newPwd"]))
		{
			return array("nErrCode"=>-1103,'nDescription'=>'新密码不能为空！');
		}

		if(!isset($param["verifyPwd"]) || empty($param["verifyPwd"]))
		{
			return array("nErrCode"=>-1104,'nDescription'=>'确认密码不能为空！');
		}

		$sPhone = $param["sPhone"];
		$oldPwd = $param["oldPwd"];
		$newPwd = $param["newPwd"];
		$verifyPwd = $param["verifyPwd"];

		if(!preg_match("/^1[34578]\d{9}$/", $sPhone)){
			return array("nErrCode"=>-1105,'nDescription'=>'请填写正确手机号！');
		}

		if(!preg_match('/^[a-zA-Z0-9]{6,15}$/', $newPwd)){
			return array("nErrCode"=>-1106,'nDescription'=>'新密码格式不正确！');
		}

		if($newPwd != $verifyPwd){
			return array("nErrCode"=>-1107,'nDescription'=>'确认密码不正确！');
		}
		$oldPwd = strtolower(md5($oldPwd.'epiaowang.com'));
		$res = UserProcess::getLoginInfo($sPhone,$oldPwd);
		if(!$res){
			return array("nErrCode"=>-1108,'nDescription'=>'账户或密码不匹配！');
		}
		$newPwd = strtolower(md5($newPwd.'epiaowang.com'));
		$res = UserProcess::getRegisterInfo($sPhone,$newPwd);
		if($res && is_array($res)){
			return array("nErrCode"=>0,"nResult"=>array('iUserID'=>$res['iUserID']));
		}else{
			return array("nErrCode"=>-1109,'nDescription'=>'修改密码失败！');
		}
	}

	//获取城市列表
	public static function getCityInfo($param){
		$CityList = CityProcess::getCityInfo();
		$cityInfo = array();
		$sCityPY = 'A';
		$i = 0;
		//计时开始
		self::runtime();
		$cityids = CinemaProcess::getRoomMovieListByCity();
		foreach($CityList as $k => $v){
			foreach($cityids as $val){
				if($v['iCityID'] != $val['iCityID']){
					continue;
				}
				if($sCityPY != $v['sCityPY']){
					$sCityPY = $v['sCityPY'];
					$i = 0;
				}
				$cityInfo[$v['sCityPY']]['KEY'] = $v['sCityPY'];
				$cityInfo[$v['sCityPY']]['cityInfo'][$i]['iCityID'] = $v['iCityID'];
				$cityInfo[$v['sCityPY']]['cityInfo'][$i]['sCityName'] = $v['sCityName'];
				$cityInfo[$v['sCityPY']]['cityInfo'][$i]['sCityPY'] = $v['sCityPY'];
				$cityInfo[$v['sCityPY']]['cityInfo'][$i]['iHotCity'] = $v['iHotCity'];
				$i++;
				break;
			}
		}
		$cityInfo = array_values($cityInfo);
		return array("nErrCode"=>0,"nResult"=>array('cityList'=>$cityInfo),'time'=>self::runtime(1));
	}

	//获取城区列表
	public static function getRegionInfo($param){
		$city = 1;
		if(isset($param['cityID']) && !empty($param['cityID'])){
			$city = $param['cityID'];
		}
		$RegionList = CityProcess::getRegionList($city);
		return array("nErrCode"=>0,"nResult"=>array('regionList'=>$RegionList));
	}

	//获取影院列表
	public static function getCinemaInfo($param){
		$city = 1;
		if(isset($param['cityID']) && !empty($param['cityID'])){
			$city = $param['cityID'];
		}
		if(empty($param['longitude']) || empty($param['latitude'])){
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('param_error'), ErrorParse::getErrorDesc('param_error').'经纬度不得为空！');
		}
		$lat1 = $param['latitude'];    //纬度
		$lng1 = $param['longitude'];    //经度
		//计时开始
		self::runtime();
		$CinemaList = CinemaProcess::getCinemaListByCity($city,array('iCinemaID','minPrice','sCoordinates','sCinemaName','iCityID','sRegion','sAddress','bIsCarPark','bIs4D','bIs4K','bIsIMAX','bIsJUMU','bIsDUBI','bIsEAT','iHotCinema'));
		$cinemaInfo = array();
		$cinemaId = array();
		foreach ($CinemaList as $v)
		{
			$cinemaId[] = $v['iCinemaID'];
		}
		$arArrange = CinemaProcess::getRoomMovieList($cinemaId,0,array('iEpiaoCinemaID','iMovieID'));
		$cinemaInfo = array();
		$iUserID=0;
		if(isset($param['iUserID']) && !empty($param['iUserID'])) {
			$iUserID = $param['iUserID'];
		}
		foreach($arArrange as $key => $v1){
			foreach($CinemaList as $k => &$row){
				if($v1['iEpiaoCinemaID'] == $row['iCinemaID']){
					$row['dist'] = round(LBS::getDistance(explode(',',$row['sCoordinates'])[1],explode(',',$row['sCoordinates'])[0],$lat1,$lng1)/1000,1);
					if(mb_strlen($row['sAddress'],'utf-8') >= 18){
						$row['sAddress'] = mb_substr($row['sAddress'],0,18,'utf-8').'...';
						if(mb_strlen($row['sAddress']) >= 60){
							$row['sAddress'] = mb_substr($row['sAddress'],0,14,'utf-8').'...';
						}
					}
					$row['bIsFeature'] = array('bIsCarPark'=> $row['bIsCarPark'],
						'bIs4D'=> $row['bIs4D'],
						'bIs4K'=> $row['bIs4K'],
						'bIsIMAX'=> $row['bIsIMAX'],
						'bIsJUMU'=> $row['bIsJUMU'],
						'bIsDUBI'=> $row['bIsDUBI'],
						'bIsEAT'=> $row['bIsEAT'],
						'iHotCinema'=> $row['iHotCinema']
					);
					$cinemaInfo[$k]['cinemaInfo'] = $row;
					if($row['minPrice'] != '19.9'){
						$cinemaInfo[$k]['minPrice'] = explode('.',$row['minPrice'])[0];
					}else{
						$cinemaInfo[$k]['minPrice'] = $row['minPrice'];
					}
					if($v1['iMovieID'] == 1913){
						$hour = date('H');
						$OrderSeatInfo = OrderProcess::getOrderSeatInfoByMovieId($v1['iMovieID']);
						if($OrderSeatInfo['OrderNum']){
							if($OrderSeatInfo['OrderNum'] < 50){
								if($iUserID!=0){
									$UserOrder = OrderProcess::getUserOrderByMovieID($iUserID,$v1['iMovieID']);
									if(!$UserOrder['UserNum']||$UserOrder['UserNum']<1){
										if (($hour>=0 && $hour<8) || $hour>=23)
										{
											$cinemaInfo[$k]['minPrice'] = '19.9';
										}else{
											$cinemaInfo[$k]['minPrice'] = '9.9';
										}
									}
								}else{
									if (($hour>=0 && $hour<8) || $hour>=23)
									{
										$cinemaInfo[$k]['minPrice'] = '19.9';
									}else{
										$cinemaInfo[$k]['minPrice'] = '9.9';
									}
								}
							}
							$cinemaInfo[$k]['count'] = $OrderSeatInfo['OrderNum'];
						}else{
							if (($hour>=0 && $hour<8) || $hour>=23)
							{
								$cinemaInfo[$k]['minPrice'] = '19.9';
							}else{
								$cinemaInfo[$k]['minPrice'] = '9.9';
							}
						}
					}

				}
			}
		}

		usort($cinemaInfo,function($a, $b){
			if ($a['cinemaInfo']['dist'] < $b['cinemaInfo']['dist'])
				return -1;
			else if ($a['cinemaInfo']['dist'] == $b['cinemaInfo']['dist'])
				return 0;
			else
				return 1;
		});
		//计时结束.
		return array("nErrCode"=>0,"nResult"=>array('cinemaList'=>$cinemaInfo),'time'=>self::runtime(1));
	}

	//获取搜索影院列表
	public static function getSearchCinemaInfo($param){
		$city = 1;
		if(isset($param['cityID']) && !empty($param['cityID'])){
			$city = $param['cityID'];
		}

		if(!isset($param['search']) || empty($param['search'])){
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('param_error'), ErrorParse::getErrorDesc('param_error').'搜索内容不能为空！');
		}
		if(empty($param['longitude']) || empty($param['latitude'])){
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('param_error'), ErrorParse::getErrorDesc('param_error').'经纬度不得为空！');
		}
		$lat1 = $param['latitude'];    //纬度
		$lng1 = $param['longitude'];    //经度
		$search = $param['search'];
		$iMovieID = isset($param['iMovieID']) ? $param['iMovieID']:0;
		//计时开始
		self::runtime();
		$CinemaList = CinemaProcess::getCinemaListByCity($city,array('iCinemaID','minPrice','sCoordinates','sCinemaName','iCityID','sRegion','sAddress','bIsCarPark','bIs4D','bIs4K','bIsIMAX','bIsJUMU','bIsDUBI','bIsEAT','iHotCinema','toFirstPinyin(sCinemaName) as FirstPinyin'),$search);
		$cinemaId = array();
		foreach ($CinemaList as $v)
		{
			$cinemaId[] = $v['iCinemaID'];
		}
		if(empty($cinemaId)){
			return array("nErrCode"=>-1401,"nDescription"=>"您搜索的影院不存在！");
		}
		$arArrange = CinemaProcess::getRoomMovieList($cinemaId,$iMovieID,array('iEpiaoCinemaID','iMovieID'));
		$iUserID=0;
		if(isset($param['iUserID']) && !empty($param['iUserID'])) {
			$iUserID = $param['iUserID'];
		}
		$cinemaInfo = array();
		foreach($arArrange as $k => $v1){
			foreach($CinemaList as $k => &$row){
				if($v1['iEpiaoCinemaID'] == $row['iCinemaID']){
					$row['dist'] = round(LBS::getDistance(explode(',',$row['sCoordinates'])[1],explode(',',$row['sCoordinates'])[0],$lat1,$lng1)/1000,1);
					if(mb_strlen($row['sAddress'],'utf-8') >= 18){
						$row['sAddress'] = mb_substr($row['sAddress'],0,18,'utf-8').'...';
						if(mb_strlen($row['sAddress']) >= 60){
							$row['sAddress'] = mb_substr($row['sAddress'],0,14,'utf-8').'...';
						}
					}
					$row['bIsFeature'] = array('bIsCarPark'=> $row['bIsCarPark'],
						'bIs4D'=> $row['bIs4D'],
						'bIs4K'=> $row['bIs4K'],
						'bIsIMAX'=> $row['bIsIMAX'],
						'bIsJUMU'=> $row['bIsJUMU'],
						'bIsDUBI'=> $row['bIsDUBI'],
						'bIsEAT'=> $row['bIsEAT'],
						'iHotCinema'=> $row['iHotCinema']
					);
					$cinemaInfo[$k]['cinemaInfo'] = $row;
					if($row['minPrice'] != '19.9'){
						$cinemaInfo[$k]['minPrice'] = explode('.',$row['minPrice'])[0];
					}else{
						$cinemaInfo[$k]['minPrice'] = $row['minPrice'];
					}

					if($v1['iMovieID'] == 1913){
						$hour = date('H');
						$OrderSeatInfo = OrderProcess::getOrderSeatInfoByMovieId($v1['iMovieID']);
						if($OrderSeatInfo['OrderNum']){
							if($OrderSeatInfo['OrderNum'] < 50){
								if($iUserID!=0){
									$UserOrder = OrderProcess::getUserOrderByMovieID($iUserID,$v1['iMovieID']);
									if(!$UserOrder['UserNum']||$UserOrder['UserNum']<1){
										if (($hour>=0 && $hour<8) || $hour>=23)
										{
											$cinemaInfo[$k]['minPrice'] = '19.9';
										}else{
											$cinemaInfo[$k]['minPrice'] = '9.9';
										}
									}
								}else{
									if (($hour>=0 && $hour<8) || $hour>=23)
									{
										$cinemaInfo[$k]['minPrice'] = '19.9';
									}else{
										$cinemaInfo[$k]['minPrice'] = '9.9';
									}
								}
							}
							$cinemaInfo[$k]['count'] = $OrderSeatInfo['OrderNum'];
						}else{
							if (($hour>=0 && $hour<8) || $hour>=23)
							{
								$cinemaInfo[$k]['minPrice'] = '19.9';
							}else{
								$cinemaInfo[$k]['minPrice'] = '9.9';
							}
						}
					}
					break;
				}
			}
		}
		if(empty($cinemaInfo)){
			return array("nErrCode"=>-1401,"nDescription"=>"您搜索的影院不存在！");
		}
		usort($cinemaInfo,function($a, $b){
			if ($a['cinemaInfo']['dist'] < $b['cinemaInfo']['dist'])
				return -1;
			else if ($a['cinemaInfo']['dist'] == $b['cinemaInfo']['dist'])
				return 0;
			else
				return 1;
		});
		//计时结束.
		return array("nErrCode"=>0,"nResult"=>array('cinemaList'=>$cinemaInfo),'time'=>self::runtime(1));
	}

	//设置影院搜索记录
	public static function setCinemaSearchLog($param){
		if(!isset($param['search']) || empty($param['search'])){
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('param_error'), ErrorParse::getErrorDesc('param_error').'搜索内容不能为空！');
		}
		if(!isset($param['code']) || empty($param['code'])){
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('param_error'), ErrorParse::getErrorDesc('param_error').'用户code为空！');
		}
		$code = $param['code'];
		$openid = PublicProcess::code($code);
		$ret = 0;
		if(isset($param['iUserID']) && !empty($param['iUserID'])){
			$iUserID = $param['iUserID'];
			if(empty($openid)){
				$res = UserProcess::getOpenid(UserProcess::getUInfo($iUserID)['sPhone']);
				if(isset($res['openid'])){
					$openid = $res['openid'];
				}
			}else{
				$res = UserProcess::getOpenid(UserProcess::getUInfo($iUserID)['sPhone'],$openid);
			}
		}
		$search = $param['search'];
		if(!empty($openid)){
			$retSearchLog = CinemaProcess::getCinemaSearchLog($openid,$search);
			if(!$retSearchLog){
				$ret = CinemaProcess::setCinemaSearchLog($openid,$search);
			}
		}
		return array("nErrCode"=>0,"nResult"=>array('ret'=>$ret));
	}

	//获取影院搜索记录
	public static function getCinemaSearchLog($param){
		if(!isset($param['code']) || empty($param['code'])){
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('param_error'), ErrorParse::getErrorDesc('param_error').'用户code为空！');
		}
		$code = $param['code'];
		$openid = PublicProcess::code($code);
		//获取openid如果不存在则设置openid
		if(isset($param['iUserID']) && !empty($param['iUserID'])){
			$iUserID = $param['iUserID'];
			if(empty($openid)){
				$res = UserProcess::getOpenid(UserProcess::getUInfo($iUserID)['sPhone']);
				if(isset($res['openid'])){
					$openid = $res['openid'];
				}
			}else{
				$res = UserProcess::getOpenid(UserProcess::getUInfo($iUserID)['sPhone'],$openid);
			}
		}
		if(!empty($openid)){
			$retSearchLog = CinemaProcess::getCinemaSearchLog($openid,"",array('searchid','search'));
		}
		return array("nErrCode"=>0,"nResult"=>array('ret'=>$retSearchLog));
	}

	//删除影院搜索记录bysearchid
	public static function delCinemaSearchLog($param){
		if(!isset($param['code']) || empty($param['code'])){
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('param_error'), ErrorParse::getErrorDesc('param_error').'用户code为空！');
		}

		if(!isset($param['searchid']) || empty($param['searchid'])){
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('param_error'), ErrorParse::getErrorDesc('param_error').'searchid为空！');
		}
		$searchid = "";
		if(isset($param['searchid']) && !empty($param['searchid'])){
			$searchid = $param['searchid'];
		}
		$code = $param['code'];
		$openid = PublicProcess::code($code);
		$ret = 0;
		//获取openid如果不存在则设置openid
		if(isset($param['iUserID']) && !empty($param['iUserID'])){
			$iUserID = $param['iUserID'];
			if(empty($openid)){
				$res = UserProcess::getOpenid(UserProcess::getUInfo($iUserID)['sPhone']);
				if(isset($res['openid'])){
					$openid = $res['openid'];
				}
			}else{
				$res = UserProcess::getOpenid(UserProcess::getUInfo($iUserID)['sPhone'],$openid);
			}
		}

		if(!empty($openid)){
			$ret = CinemaProcess::delCinemaSearchLog($openid,$searchid);
		}
		return array("nErrCode"=>0,"nResult"=>array('ret'=>$ret));
	}

	//微信支付成功
	public static function weixinOk($param){

		if(!isset($param["iUserID"]) || empty($param["iUserID"]))
		{
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('user_not_login'), ErrorParse::getErrorDesc('user_not_login').'用户登陆失败，请重新登录！');
		}
		if(!isset($param["sPhone"]) || empty($param["sPhone"]))
		{
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('param_error'), ErrorParse::getErrorDesc('param_error').'手机不能为空！');
		}
		$iUserID=$param["iUserID"];
		$sendPhone = $param["sPhone"];
		if(!preg_match("/^1[34578]\d{9}$/", $sendPhone)){
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('param_error'), ErrorParse::getErrorDesc('param_error').'请填写正确手机号！');
		}

		if(!isset($param['outerOrderId']) || empty($param['outerOrderId'])){
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('param_error'), ErrorParse::getErrorDesc('param_error').'该订单不存在！');
		}

		$formId = "";
		if(isset($param['formId']) && !empty($param['formId'])){
			$formId = $param['formId'];
		}
		$outerOrderId = $param['outerOrderId'];

		$userOrderInfo = OrderProcess::getOrderSeatInfoByOuterOrderId($outerOrderId);
		if(empty($userOrderInfo))
		{
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('order_nofind'), ErrorParse::getErrorDesc('order_nofind').'该订单不存在');
		}

		if ($userOrderInfo['iUserId'] != $iUserID)
		{
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('order_nofind'), ErrorParse::getErrorDesc('order_nofind').'该订单不属于您');
		}

		$res = OrderProcess::confirmSeatOnlineOrderCardPay($iUserID,$outerOrderId,$sendPhone,$formId);
		if($res['ok']){
			return array("nErrCode"=>0,"nResult"=>array('orderInfo'=>array('outerOrderId'=>$res['outerOrderId'])));
		}
		return array("nErrCode"=>-603,"nResult"=>$res['msg']);
	}

	//微信支付失败
	public static function weixinFail($param){
		$payloginfo = OrderProcess::getOrderPaylogForOuterOrderId($param['outerOrderId']);
		if($payloginfo){
			foreach($payloginfo as $v){
				if(!empty($v['sCheckNo']) && !empty($v['cardCount'])){
					CouponProcess::updateCouponiUsedunFlag($v['sCheckNo'],$v['cardCount']);
				}
				if($v['bankType'] == ConfigParse::getPayTypeKey('cashPay')){
					VoucherProcess::updateVoucheriUsedunFlag($v['sVoucherPassWord']);
				}
			}
			OrderProcess::delUserPayOrderInfo($param['outerOrderId']);
		}
		return GeneralFunc::returnErr(ErrorParse::getErrorNo('order_nofind'), ErrorParse::getErrorDesc('order_nofind').'微信余额不足！');
	}

	//个人中心
	public static function getUserInfo($param){

		if(!isset($param["iUserID"]) || empty($param["iUserID"]))
		{
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('user_not_login'), ErrorParse::getErrorDesc('user_not_login').'用户登陆失败，请重新登录！');
		}

		$iUserID=$param["iUserID"];
		$mAccountMoney = UserProcess::getmAccountMoney($iUserID);  //余额
		$sPhone = UserProcess::getUInfo($iUserID)['sPhone'];
		if(!$mAccountMoney){
			$mAccountMoney = 0;
		}else{
			$mAccountMoney = round($mAccountMoney,1);
		}
		return array("nErrCode"=>0,"nResult"=>array('mAccountMoney'=>$mAccountMoney,'sPhone'=>$sPhone));
	}

	//个人中心-订单显示
	public static function getUserOrderInfo($param){

		if(!isset($param["iUserID"]) || empty($param["iUserID"]))
		{
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('user_not_login'), ErrorParse::getErrorDesc('user_not_login').'用户登陆失败，请重新登录！');
		}

		$iUserID=$param["iUserID"];
		$UserOrder = OrderProcess::getUserIDouterOrderId($iUserID);
		$OrderList = array();
		$noOrderList = array();
		if($UserOrder){
			foreach($UserOrder as $k => $v){
				$OrderInfo = @OrderProcess::getOrderSeatInfoByOuterOrderId($v['outerOrderId']);
				if(!$OrderInfo['sMovieName']){
					continue;
				}
				if($OrderInfo['orderStatus']>10206){
					continue;
				}
				$date = date('Y年m月d日',strtotime($OrderInfo['dPlayTime']));
				$week = "星期".mb_substr("日一二三四五六",date("w",strtotime($OrderInfo['dPlayTime'])),1,"utf-8");
				$time = date('H:i',strtotime($OrderInfo['dPlayTime']));
				$seatInfo = array();
				foreach(explode(',',$OrderInfo['orderInfo']) as $key => $val){
					$seatInfo[$key] = $val.'座';
				}
				if($OrderInfo['orderStatus'] == 10101 && (900-(time()- strtotime($OrderInfo["createTime"])))>0)  //判断该订单是否超过15分钟  待支付订单
				{
					if($OrderInfo['isWeChatapplet'] == 1){
						$noOrderList[$k]['outerOrderId'] = $OrderInfo['outerOrderId'];
						$noOrderList[$k]['sMovieName'] = $OrderInfo['sMovieName'];
						$noOrderList[$k]['sCinemaName'] = $OrderInfo['sCinemaName'];
						$noOrderList[$k]['sRoomName'] = $OrderInfo['sRoomName'];
						$noOrderList[$k]['seatInfo'] = $seatInfo;
						$noOrderList[$k]['date'] = $date;
						$noOrderList[$k]['week'] = $week;
						$noOrderList[$k]['time'] = $time;
						$noOrderList[$k]['totalPrice'] = $OrderInfo['totalPrice'];
						$noOrderList[$k]['countdown'] = (900 - (time()- strtotime($OrderInfo["createTime"])));
					}
				}elseif($OrderInfo['orderStatus'] > 10102 && $OrderInfo['orderStatus'] <= 10206){
					$OrderList[$k]['outerOrderId'] = $OrderInfo['outerOrderId'];
					$OrderList[$k]['sMovieName'] = $OrderInfo['sMovieName'];
					$OrderList[$k]['sCinemaName'] = $OrderInfo['sCinemaName'];
					$OrderList[$k]['sRoomName'] = $OrderInfo['sRoomName'];
					$OrderList[$k]['seatInfo'] = $seatInfo;
					$OrderList[$k]['date'] = $date;
					$OrderList[$k]['week'] = $week;
					$OrderList[$k]['time'] = $time;
					$OrderList[$k]['totalPrice'] = $OrderInfo['totalPrice'];
					if(strtotime($OrderInfo['dPlayTime'])>strtotime(date("Y-m-d H:i:s"))){
						$dPlayFlag = "未放映";
					}else{
						$dPlayFlag = "已放映";
					}
					$sValidCodeMsg = "";
					$sValidCodeErrMsg = "";
					if(!empty($OrderInfo['sInterfaceValidCode']))
					{
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
								$sValidCodeErrMsg = '如遇机器故障，可凭故障码：'.explode('|',$sInterfaceValidCode[1])[0].'  '.'验证码：'.explode('|',$sInterfaceValidCode[1])[1].'到前台取票';
							}else{
								$sValidCodeErrMsg = '如遇机器故障，可凭故障码：'.explode('|',$sInterfaceValidCode[1])[0].'到前台取票';
							}
						}else{
							if(count(explode('|',$sInterfaceValidCode[0])) == 2){
								$sValidCodeErrMsg = '如遇机器故障，可凭故障码：'.explode('|',$sInterfaceValidCode[0])[0].'  '.'验证码：'.explode('|',$sInterfaceValidCode[0])[1].'到前台取票';
							}else{
								$sValidCodeErrMsg = '如遇机器故障，可凭故障码：'.explode('|',$sInterfaceValidCode[0])[0].'到前台取票';
							}
						}
						if(count(explode('|',$sInterfaceValidCode[0])) == 2){
							$sValidCodeMsg = '取票码：'.explode('|',$sInterfaceValidCode[0])[0].'  '.'验证码：'.explode('|',$sInterfaceValidCode[0])[1];
						}else{
							$sValidCodeMsg = '取票码：'.explode('|',$sInterfaceValidCode[0])[0];
						}
					}else{
						$sValidCodeMsg = "正在出票中...";
					}

					$OrderList[$k]['sValidCodeMsg'] = $sValidCodeMsg;
					$OrderList[$k]['sValidCodeErrMsg'] = $sValidCodeErrMsg;
					$OrderList[$k]['dPlayFlag'] = $dPlayFlag;
					$OrderList[$k]['orderPayType'] = ConfigParse::getPayNameKey($OrderInfo['orderPayType']);
				}
			}
		}
		$OrderList = array_values($OrderList);
		$noOrderList = array_values($noOrderList);
		return array("nErrCode"=>0,"nResult"=>array('finishOrder'=>$OrderList,'unfinishOrder'=>$noOrderList));
	}

	//个人中心-电影卡显示
	public static function getUserCarInfo($param){

		if(!isset($param["iUserID"]) || empty($param["iUserID"]))
		{
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('user_not_login'), ErrorParse::getErrorDesc('user_not_login').'用户登陆失败，请重新登录！');
		}

		$iUserID=$param["iUserID"];
		$UserCarInfo = CouponProcess::getCouponInfoByiUserID($iUserID);  //电影卡
		$UserCarList = array();
		foreach($UserCarInfo as $k => $v){

			if($v['iSalesQuantity'] - $v['iUsedFlag'] == 0){
				continue;
			}
			if(strtotime($v['dEndTime']) - time() <= 0){
				continue;
			}
			if($v['iCouponTypeID'] == 1){
				$UserCarList[$k]['validcount'] = ($v['iSalesQuantity'] - $v['iUsedFlag']).'张';
			}elseif($v['iCouponTypeID'] == 2){
				$UserCarList[$k]['validcount'] = ($v['iSalesQuantity'] - $v['iUsedFlag']).'点';
			}

			$couponmovie = CouponProcess::getcouponmoviearea($v['iCouponID']);
			$sMovieName = array();
			foreach($couponmovie as $val){
				$sMovieName[] = MovieProcess::getMovieInfoByMovieID($val['iMovieID']);
			}
			if(!empty($sMovieName)){
				foreach($sMovieName as $val){
					$v['sMovieName'][] = $val['sMovieName'];
				}
			}
			if(isset($v['sMovieName']) && is_array($v['sMovieName'])){
				$UserCarList[$k]['sMovieName'] = implode(',',$v['sMovieName']);
			}

			$UserCarList[$k]['sCouponName'] = $v['sCouponName'];
			$UserCarList[$k]['sCheckNo'] = $v['sCheckNo'];
			$UserCarList[$k]['dEndTime'] = date("Y-m-d",strtotime($v['dEndTime']));
		}
		$UserCarList = array_values($UserCarList);
		return array("nErrCode"=>0,"nResult"=>array('UserCarInfo'=>$UserCarList));
	}

	//个人中心-添加电影卡
	public static function addCarToUser($param){

		if(!isset($param["iUserID"]) || empty($param["iUserID"]))
		{
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('user_not_login'), ErrorParse::getErrorDesc('user_not_login').'用户登陆失败，请重新登录！');
		}
		if(!isset($param["sCheckNo"]) || empty($param["sCheckNo"]))
		{
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('param_error'), ErrorParse::getErrorDesc('param_error').'卡号不能为空！');
		}
		if(!isset($param["sPassWord"]) || empty($param["sPassWord"]))
		{
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('param_error'), ErrorParse::getErrorDesc('param_error').'密码不能为空！');
		}
		$sCheckNo = $param["sCheckNo"];
		$sPassWord = $param["sPassWord"];
		$iUserID=$param["iUserID"];
		$couponInfo = CouponProcess::getCouponSalesInfoByCheckNo($param["sCheckNo"],array('iUserID','sPassWord','dEndTime','iUsedFlag','iSalesQuantity','sBatchNo','iPassTime','iCouponStatus','iCouponID'));  //电影卡

		if (empty($couponInfo))
		{
			return array("nErrCode"=>-7,"nDescription"=>"您输入的卡号或密码不正确。");
		}
		if ($couponInfo['sPassWord'] != $sPassWord)
		{
			if (empty($couponInfo['iPassTime']))
			{
				$couponInfo['iPassTime'] = 0;
			}
			$iPassTime = $couponInfo['iPassTime'] +1;

			$couponUpdateInfo["sCheckNo"]=$sCheckNo;
			$couponUpdateInfo["iPassTime"]=$iPassTime;
			if ($iPassTime>=5)
			{
				$couponUpdateInfo["iCouponStatus"]=2;
			}
			CouponProcess::updateCouponSalesInfo($sCheckNo,$couponUpdateInfo);
			return array("nErrCode"=>-7,"nDescription"=>"您输入的卡号或密码不正确。");
		}

		if (!empty($couponInfo['iUserID']))
		{
			return array("nErrCode"=>-701,"nDescription"=>"您输入的电影卡已被绑定。");
		}
		if($couponInfo["iCouponStatus"]!="1")
		{
			return array("nErrCode"=>-702,"nDescription"=>"您输入的电影卡已经失效。");
		}

		if($couponInfo['iSalesQuantity'] - $couponInfo['iUsedFlag'] <= 0){
			return array("nErrCode"=>-706,"nDescription"=>"您输入的电影卡点数为零。");
		}

		$iCouponID = $couponInfo["iCouponID"];
		$couponBaseInfo=  CouponProcess::getCouponBaseInfo(array('iCouponID'=>$iCouponID));
		$dEndTime=$couponInfo["dEndTime"];
		if (abs($couponBaseInfo['mRechargePrice'])<=0.000 && (strtotime($dEndTime)-time())<0)        //mRechargePrice充值价格
		{
			return array("nErrCode"=>-703,"nDescription"=>"该电影卡已过期。");
		}

		$bandCount = 0;
		if ($couponBaseInfo['ibindingCount']>0)
		{
			$bandCount = CouponProcess::getBandCountByCouponId($iCouponID, $iUserID);
			if ($bandCount+$couponInfo['iSalesQuantity'] >$couponBaseInfo['ibindingCount'])
			{
				return array("nErrCode"=>-704,"nDescription"=>"您输入的电影卡超过最大绑定数量。");
			}
			$bandCountbyBatch = CouponProcess::getCouponInfoCount(array('iUserID'=>$iUserID,'sBatchNo'=>$couponInfo['sBatchNo']));
			if($bandCountbyBatch >= 10){
				return array("nErrCode"=>-704,"nDescription"=>"您输入的电影卡超过最大绑定数量。");
			}
		}

		$couponInfo=array("sCheckNo"=>$sCheckNo,"iUserID"=>$iUserID,'dBandTime'=>date('Y-m-d H:i:s'));
		if(CouponProcess::updateCouponSalesInfo($sCheckNo,$couponInfo)){
			CouponProcess::bandingCouponLog($sCheckNo,$sPassWord,$iUserID,1);
			return array("nErrCode"=>0);
		}else
		{
			return array("nErrCode"=>-705,"nDescription"=>"系统错误");
		}
	}

	//个人中心-现金券显示
	public static function getUserCashInfo($param){

		if(!isset($param["iUserID"]) || empty($param["iUserID"]))
		{
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('user_not_login'), ErrorParse::getErrorDesc('user_not_login').'用户登陆失败，请重新登录！');
		}

		$iUserID=$param["iUserID"];
		$UserCashInfo = VoucherProcess::getVoucherBaseInfoByiUserID($iUserID,array('iVoucherID','sVoucherName','mVoucherMoney','iVoucherStatusID','dVaildBeginTime','dVaildEndTime'));  //现金券
		$UserCashList = array();
		foreach($UserCashInfo as $k => &$v){

			if($v['mVoucherMoney'] <= 0){
				continue;
			}
			if($v['iVoucherStatusID'] != '2'){
				continue;
			}
			if(strtotime($v['dVaildEndTime']) - time() <= 0 || strtotime($v['dVaildBeginTime']) - time() >= 0){
				continue;
			}
			$UserCashBaseInfo = VoucherProcess::getVoucherBaseInfoByVoucherID($v['iVoucherID'],array('iVoucherUseCount','iVoucherCount','iVoucherUseMoney'));  //现金券
			if($UserCashBaseInfo['iVoucherCount'] <= 0){
				continue;
			}
			$vouchermovie = VoucherProcess::getvouchermoviearea($v['iVoucherID']);
			$sMovieName = array();
			foreach($vouchermovie as $val){
				$sMovieName[] = MovieProcess::getMovieInfoByMovieID($val['iMovieID']);
			}
			if(!empty($sMovieName)){
				foreach($sMovieName as $val){
					$v['sMovieName'][] = $val['sMovieName'];
				}
			}
			if(isset($v['sMovieName']) && is_array($v['sMovieName'])){
				$v['sMovieName'] = implode(',',$v['sMovieName']);
			}
			$v['iVoucherUseCount'] = $UserCashBaseInfo['iVoucherUseCount'];
			$v['iVoucherUseMoney'] = explode('.',$UserCashBaseInfo['iVoucherUseMoney'])[0];
			$v['mVoucherMoney'] = explode('.',$v['mVoucherMoney'])[0];
			$v['dVaildEndTime'] = date("Y-m-d",strtotime($v['dVaildEndTime']));
			$UserCashList[$k] = $v;
		}
		$UserCashList = array_values($UserCashList);
		return array("nErrCode"=>0,"nResult"=>array('UserCarInfo'=>$UserCashList));
	}

	//个人中心-添加现金券
	public static function addCashToUser($param){

		if(!isset($param["iUserID"]) || empty($param["iUserID"]))
		{
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('user_not_login'), ErrorParse::getErrorDesc('user_not_login').'用户登陆失败，请重新登录！');
		}
		if(!isset($param["sVoucherPassWord"]) || empty($param["sVoucherPassWord"]))
		{
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('param_error'), ErrorParse::getErrorDesc('param_error').'券号不能为空！');
		}

		$sVoucherPassWord = $param["sVoucherPassWord"];
		$iUserID=$param["iUserID"];
		$voucherInfo = VoucherProcess::getVoucherBaseInfoBysVoucherPassWord($sVoucherPassWord,array('iUserID','iVoucherStatusID','dVaildEndTime','iVoucherID'));  //现金券

		if (empty($voucherInfo))
		{
			return array("nErrCode"=>-8,"nDescription"=>"您输入的现金券券码不正确。");
		}

		if ($voucherInfo['iVoucherStatusID'] == 3)
		{
			return array("nErrCode"=>-801,"nDescription"=>"您输入的现金券已使用。");
		}

		if ($voucherInfo['iVoucherStatusID'] == 2)
		{
			return array("nErrCode"=>-802,"nDescription"=>"您输入的现金券已被绑定。");
		}

		if ($voucherInfo['iVoucherStatusID'] == 4)
		{
			return array("nErrCode"=>-803,"nDescription"=>"您输入的现金券已失效。");
		}

		if (strtotime($voucherInfo['dVaildEndTime']-time()<0))
		{
			return array("nErrCode"=>-804,"nDescription"=>"您输入的现金券已过期。");
		}
		$arVoucherBaseInfo = VoucherProcess::getVoucherBaseInfoByVoucherID($voucherInfo['iVoucherID'],array('iVoucherID','sVoucherName','ibindingCount','iVoucherUseCount','iVoucherUseMoney'));
		if($arVoucherBaseInfo['ibindingCount']!=0)
		{
			$arVoucherList = VoucherProcess::getVoucherBaseInfoByiUserID($iUserID);
			$i = 0;
			foreach($arVoucherList as $sigVoucher)
			{
				if ($sigVoucher['iVoucherID'] ==  $arVoucherBaseInfo['iVoucherID'])
				{
					$i++;
				}
			}
			if($i >= $arVoucherBaseInfo['ibindingCount'])
			{
				return array("nErrCode"=>-805,"nDescription"=>"该现金券已超出账户最大使用量。");
			}
		}

		$updateVoucher['sVoucherPassWord'] = $sVoucherPassWord;
		$updateVoucher['iVoucherStatusID'] = 2;
		$updateVoucher['iUserID'] = $iUserID;

		$result['dVaildEndTime'] = $voucherInfo['dVaildEndTime'];
		$result['iUserID'] = $iUserID;
		if (VoucherProcess::updateVoucherInfo($sVoucherPassWord,$updateVoucher))
		{
			return array("nErrCode"=>0);
		}
		else {
			return array('nErrCode'=>-806,'nDescription'=>'系统错误');
		}
	}

	//注册发送短信
	public static function sendRegisterMsg($param){
		if(!isset($param["sPhone"]) || empty($param["sPhone"]))
		{
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('param_error'), ErrorParse::getErrorDesc('param_error').'手机号不能为空！');
		}
		$sPhone = $param["sPhone"];
		if(!preg_match("/^1[34578]\d{9}$/", $sPhone)){
			return array("nErrCode"=>-601,'nDescription'=>'请填写正确手机号');
		}
		$randStr = str_shuffle('1234567890');
		$code = substr($randStr,0,6);
		$arPara = array('code'=>$code);
		$sTemplate = 'SMS_7780229';
		$ret = SMSProcess::sendDayuSMS($sPhone, $arPara, $sTemplate);
		if(isset($ret) && is_numeric($ret) && $ret == 1){
			return array("nErrCode"=>0,"nResult"=>array('Msgcode'=>$code));
		}else{
			return array("nErrCode"=>-1,"nResult"=>array('Msgcode'=>'短信发送频繁，请稍后再试！'));
		}
		return array("nErrCode"=>-1,"nResult"=>array('Msgcode'=>'短信发送失败！'));
	}

	//第一次使用小程序获取现金券
	public static function getVoucher($param){

		if(!isset($param["iUserID"]) || empty($param["iUserID"]))
		{
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('user_not_login'), ErrorParse::getErrorDesc('user_not_login').'用户登陆失败，请重新登录！');
		}

		if(!isset($param["getCashFlag"]) || empty($param["getCashFlag"]))
		{
			$param["getCashFlag"] = 0;
		}
		$iUserID = $param["iUserID"];
		$getCashFlag = $param["getCashFlag"];

		if($getCashFlag == 0)
		{
			$flag = UserProcess::getCashFlag($iUserID);
			if($flag == 0){
				return array("nErrCode"=>0,"nResult"=>array('getCashFlag'=>1));
			}elseif($flag == 1){
				return array("nErrCode"=>0,"nResult"=>array('getCashFlag'=>2));
			}else{
				return array("nErrCode"=>-1003,"nDescription"=>'系统错误！');
			}

		}elseif($getCashFlag == 1)
		{
			$flag = UserProcess::updeteCashFlag($iUserID,$getCashFlag);
			if($flag == 0){
				return array("nErrCode"=>-1001,"nDescription"=>'领取现金券失败！');
			}elseif($flag == 1){
				$voucherInfo = VoucherProcess::createVoucher(232,$iUserID,sprintf(Yii::app()->params['batchInfo']['scoreStore']['wechat_favorable'], 'xjq', 232));
				if($voucherInfo){
					return array("nErrCode"=>0,"nResult"=>array('getCashFlag'=>2));
				}else{
					return array("nErrCode"=>-1002,"nDescription"=>'很遗憾，您来迟了一步！');
				}
			}
		}
	}

	//充值-0（电影卡充值），1（余额充值）

	public static function setRecharge($param)
	{
		if(!isset($param["iUserID"]) || empty($param["iUserID"]))
		{
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('user_not_login'), ErrorParse::getErrorDesc('user_not_login').'用户登陆失败，请重新登录！');
		}
		if(!isset($param["money"]) || empty($param["money"]))
		{
			return array("nErrCode"=>-1201,"nDescription"=>'钱不能为0！');
		}
		if($param["money"]<10)
		{
			return array("nErrCode"=>-1202,"nDescription"=>'至少充值10元！');
		}
		if($param["money"]>9999)
		{
			return array("nErrCode"=>-1208,"nDescription"=>'至多充值9999元！');
		}
		if(!isset($param["type"]))
		{
			return array("nErrCode"=>-1203,"nDescription"=>'请至少选择一种充值类型！');
		}
		if(!isset($param['code']) || empty($param['code']))
		{
			return array("nErrCode"=>-1204,"nDescription"=>'用户code为空！');
		}
		$iUserID = $param["iUserID"];
		$money = $param["money"];
		$type = $param['type'];
		$code = $param['code'];
		$openid = PublicProcess::code($code);
		$sendPhone = UserProcess::getUInfo($iUserID)['sPhone'];
		switch($type){
			case 0:
				break;
			case 1:
			{
				$ret = OrderProcess::createRechargeOrder($iUserID,$money,$sendPhone,'',ConfigParse::getOrdersKey('accountRechargeOrder'));
				if(true == $ret['ok']){
					$requestPayment = PayProcess::weixinPay($ret['data']['outerOrderId'],$openid,$money,ConfigParse::getOrdersKey('accountRechargeOrder'));
					if($requestPayment){
						return array("nErrCode"=>0,"nResult"=>array('weixinPay'=>$requestPayment,'outerOrderId'=>$ret['data']['outerOrderId']));
					}
				}
			}
				break;
			default:
				return array("nErrCode"=>-1205,"nDescription"=>'充值类型错误！');
		}
		return array("nErrCode"=>-1206,"nDescription"=>'系统错误！');
	}

	//微信充值成功
	public static function weixinRechargeOk($param){

		if(!isset($param["iUserID"]) || empty($param["iUserID"]))
		{
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('user_not_login'), ErrorParse::getErrorDesc('user_not_login').'用户登陆失败，请重新登录！');
		}
		$iUserID=$param["iUserID"];
		if(!isset($param['outerOrderId']) || empty($param['outerOrderId'])){
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('param_error'), ErrorParse::getErrorDesc('param_error').'该订单不存在！');
		}
		$outerOrderId = $param['outerOrderId'];

		$userOrderInfo = OrderProcess::getOrderInfo($outerOrderId);
		if(empty($userOrderInfo))
		{
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('order_nofind'), ErrorParse::getErrorDesc('order_nofind').'该订单不存在');
		}

		if ($userOrderInfo['iUserId'] != $iUserID)
		{
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('order_nofind'), ErrorParse::getErrorDesc('order_nofind').'该订单不属于您');
		}
		$res = OrderProcess::confirmRechargeOrder($outerOrderId);
		if($res['ok']){
			return array("nErrCode"=>0,"nResult"=>array('orderInfo'=>array('iUserID'=>$res['userID'])));
		}
		return array("nErrCode"=>-1206,"nDescription"=>'系统错误！');
	}

	//微信充值失败
	public static function weixinRechargeFail($param){
		$userOrderInfo = OrderProcess::getOrderInfo($param['outerOrderId']);
		$orderInfo['orderPayType'] = ConfigParse::getPayTypeKey('weixinPay');
		$orderInfo['orderStatus'] = ConfigParse::getPayStatusKey('orderClose');
		$orderInfo['closeTime'] = date('Y-m-d H:i:s');
		$orderInfo['orderInfo'] = $userOrderInfo['orderInfo'].'订单已经取消';
		$orderInfo['outerOrderId'] = $param['outerOrderId'];
		OrderProcess::updateUserOrderInfo($orderInfo);
		return array("nErrCode"=>-1207,"nDescription"=>'微信充值失败！');
	}

	//获取余额充值交易记录
	public static function getRechargeLog($param){
		if(!isset($param["iUserID"]) || empty($param["iUserID"]))
		{
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('user_not_login'), ErrorParse::getErrorDesc('user_not_login').'用户登陆失败，请重新登录！');
		}
		$iUserID = $param["iUserID"];
		$res = OrderProcess::getOrderRechargeLog($iUserID);
		if(!$res){
			return array("nErrCode"=>-1301,"nDescription"=>'暂无');
		}
		foreach($res as $k => $v) {
			$paylog = OrderProcess::getOrderPaylogForOuterOrderId($v['outerOrderId']);
			foreach($paylog as $val){
				if ($val['bankType'] == ConfigParse::getPayTypeKey('accountPay')) {
					$mPrice = (float)$val['totalPrice'];
				}
			}
			$fh = '-';
			if ($v["orderType"] == 100002) {
				$ltitle = '账户充值';
				$mPrice = (float)$v['mPrice'];
				$fh = '+';
			} else if ($v["orderStatus"] == 10210) {
				$ltitle = '订单退款';
				$fh = '+'.$mPrice;
			} else {

				switch ($v["orderType"]) {
					case "100001":
						$ltitle = 'E票网在线选座订单';
						break;
					case "100003":
						$ltitle = '电影卡充值订单';
						break;
					case "200001":
						$ltitle = '活动电影卡订单';
						break;
					case "200002":
						$ltitle = '活动在线选座订单';
						break;
					case "300001":
						$ltitle = '第三方在线选座订单';
						break;
					case "400001":
						$ltitle = '后台销售卡记录';
						break;
					case "400002":
						$ltitle = '现金券购买电影卡';
						break;
					case '500001':
						$fh = '+';
						$ltitle = '联动优势充值订单';
						break;
				}
			}
			$rechargeLog[$k]['ltitle'] = $ltitle;
			$rechargeLog[$k]['fh'] = $fh;
			$rechargeLog[$k]['mPrice'] = $mPrice;
			$rechargeLog[$k]['time'] = $v['createTime'];
		}
		return array("nErrCode"=>0,"nResult"=>array('RechargeLog'=>$rechargeLog));
	}

	//获取活动列表
	public static function getHuodong($param){
		$iUserID=0;
		$huodongList = HuodongProcess::getHuodongList();
		if($huodongList){
			foreach($huodongList as &$v){
				$huodongItem = HuodongProcess::get_user_HuodongItem($v['iHuoDongID'],'all');
				$v['huodongItem'] = $huodongItem;
				if(strtotime($v['dHuoDongBeginDate'])>time()){
					$v['huodongStatus'] = 1;//活动未开始
				}else{
					if(isset($param["iUserID"]) &&!empty($param["iUserID"]))
					{
						$iUserID = $param["iUserID"];
						//获取活动领取记录
						$huodongRecord = HuodongProcess::get_user_Huodongrecord($iUserID,$v['iHuoDongID']);
						if(!$huodongRecord || $huodongRecord['flag'] == 0){
							$v['huodongStatus'] = 2;//该用户未参与该活动
							$i=0;
							foreach($huodongItem as $val){
								if($val['IRemainedCount']<=0){
									$i++;
								}
							}
							if($i==count($huodongItem)){
								$v['huodongStatus'] = 5;//奖品已抽完
							}
						}elseif($huodongRecord['flag'] == 1){
							$v['huodongStatus'] = 3;//该用户已经中奖
							if($huodongRecord['iCouponID'] != 0){
								$couponsales = CouponProcess::getCouponSalesByUserID($iUserID,$huodongRecord['iCouponID'],array());
								$v['couponsales'] = $couponsales;
							}
							if($huodongRecord['iVoucherID'] != 0){
								$voucher = VoucherProcess::getVoucherSalesByUserID($iUserID,$huodongRecord['iVoucherID'],array());
								$v['voucher'] = $voucher;
							}
						}
					}else{
						$iUserID=1;
					}
				}
				if(strtotime($v['dHuoDongEndDate'])<time()){
					$v['huodongStatus'] = 4;//活动已经结束
				}
				if($iUserID == 1){
					$v['huodongStatus'] = 6;//用户未登录
				}
			}
		}
		return array("nErrCode"=>0,"nResult"=>array('huodongList'=>$huodongList));
	}

	//用户参与活动
	public static function subHuodongById($param){
		if(!isset($param["iUserID"]) || empty($param["iUserID"]))
		{
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('user_not_login'), ErrorParse::getErrorDesc('user_not_login').'用户登陆失败，请重新登录！');
		}
		$iUserID = $param["iUserID"];
		$sendPhone = UserProcess::getUInfo($iUserID,array('sPhone'))['sPhone'];
		if(!isset($param["iHuoDongID"]) || empty($param["iHuoDongID"]))
		{
			return array("nErrCode"=>-1501,"nDescription"=>"请选择一个活动类型");
		}
		$iHuoDongID = $param["iHuoDongID"];
		//获取活动领取记录
		$huodongRecord = HuodongProcess::get_user_Huodongrecord($iUserID,$iHuoDongID);
		//获取该活动奖品
		$huodongItemAll = HuodongProcess::get_user_HuodongItem($iHuoDongID);
		if($huodongItemAll){
			$rand = rand(1,count($huodongItemAll));
			$huodongItem = $huodongItemAll[$rand-1];
			if($huodongRecord){
				if($huodongRecord['flag'] == 0){
					//第一次领取奖品活动失败，可接着领取直到成功
					//更新用户领取记录
					$res = HuodongProcess::HuodongBand($huodongItem,$iUserID,$iHuoDongID);
					if($res['nErrCode'] == 0){
						if(HuodongProcess::up_user_Huodongrecord($iUserID,$iHuoDongID,$huodongItem['iGetCount'])){
							return array("nErrCode"=>0,'nResult'=>array('item'=>$huodongItem['iHuodongItemIndex'],'huodongItem'=>$huodongItem,'couponsales'=>$res));
						}else{
							//更新用户领取记录失败
							return array("nErrCode"=>-1507,"nDescription"=>"更新活动记录失败");
						}
					}else{
						return array("nErrCode"=>-1503,"nDescription"=>"领取失败");
					}
				}else{
					//该用户领取活动奖品并成功，不能重复领取
					return array("nErrCode"=>-1502,"nDescription"=>"您已领取该奖品");
				}
			}else{
				//createHuoDongCardOrder($iUserID, $mPrice, $iCount, $orderInfo, $returnUrl, $sendPhone, $fromClient, $payMethod, $iHuoDongItemID);
				$userOrderInfo = OrderProcess::createHuoDongCardOrder($iUserID,0,1,$huodongItem['sHuodongItemName'],"",$sendPhone,'微信小程序',ConfigParse::getPayTypeKey('backCardPay'),$huodongItem['iHuodongItemID'],0,ConfigParse::getOrdersKey('huodongCardOrder'));
				//插入活动记录并且为该用户绑定活动奖品
				if(HuodongProcess::add_user_Huodongrecord($iUserID,$iHuoDongID,$huodongItem['iHuodongItemID'],$userOrderInfo['data']['outerOrderId'],76,$huodongItem['iCouponID'],$huodongItem['iVoucherID'],0,0,0)){
					$res = HuodongProcess::HuodongBand($huodongItem,$iUserID,$iHuoDongID);
					//领取成功
					if($res['nErrCode'] == 0){
						//修改活动记录
						if(HuodongProcess::up_user_Huodongrecord($iUserID,$iHuoDongID,$huodongItem['iGetCount'])){
							return array("nErrCode"=>0,'nResult'=>array('item'=>$huodongItem['iHuodongItemIndex'],'huodongItem'=>$huodongItem,'couponsales'=>$res));
						}else{
							//更新用户领取记录失败
							return array("nErrCode"=>-1507,"nDescription"=>"更新活动记录失败");
						}
					}else{
						return array("nErrCode"=>-1503,"nDescription"=>"领取失败");
					}
				}else{
					return array("nErrCode"=>-1506,"nDescription"=>"活动记录插入失败");
				}
			}
		}
		return array("nErrCode"=>-1508,"nDescription"=>"该活动下没有奖品");
	}


	//短信测试
	public static function sendSeatOnlineMsg($param){

		return array("nErrCode"=>0,"nResult"=>array('Msg'=>OrderProcess::sendSeatOnlineMsg($param['outerOrderId'])));
	}

	//计时函数
	public static function runtime($mode=0)   {
		static   $t;
		if(!$mode)   {
			$t   =   microtime();
			return;
		}
		$t1   =   microtime();
		$mtime1=explode(' ',$t1);
		$mtime=explode(' ',$t);
		return   sprintf('%.3f ms',($mtime1[1]+$mtime1[0]-$mtime[1]-$mtime[0])*1000);
	}
}