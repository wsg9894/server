<?php

/**
 * AjaxController - 用于Ajax操作
 * @author luzhizhong
 * @version V1.0
 */

Yii::import('application.models.data.db.*');
Yii::import('application.modules.output.models.data.*');
Yii::import('application.modules.output.models.process.*');

class AjaxController extends Controller
{
	/**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array(
		);
	}

	/**
	 * 获取上映影片列表页
	 * @param int $cityID
	 * @return array
	 */
	public function actionGetMovieList(){
		$cityID = empty($_REQUEST['iCityID']) ? 1 : $_REQUEST['iCityID'];
		$cityInfo = CityProcess::getCityInfoByCityId($cityID);
		if (empty($cityInfo)){
			return array("ok"=>false);
		}
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
				$movieInfo['sImageUrl'] = 'http://m.epiaowang.com'.$movieInfo['sImageUrl'];
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
		GeneralFunc::getJson(array('flag'=>true, 'movieList'=>$movieList,'cityInfo'=>$cityInfo));
	}

	/**
	 * 获取有排期的影院列表
	 * @return string
	 */
	public static function actionGetCinemaList(){
		$city = 1;
		if(isset($_REQUEST['iCityID']) && !empty($_REQUEST['iCityID'])){
			$city = $_REQUEST['iCityID'];
		}
		if(empty($_REQUEST['longitude']) || empty($_REQUEST['latitude'])){
			return GeneralFunc::returnErr(ErrorParse::getErrorNo('param_error'), ErrorParse::getErrorDesc('param_error').'经纬度不得为空！');
		}
		$lat1 = $_REQUEST['latitude'];    //纬度
		$lng1 = $_REQUEST['longitude'];    //经度
		$CinemaList = CinemaProcess::getCinemaListByCity($city,array('iCinemaID','minPrice','sCoordinates','sCinemaName','iCityID','sRegion','sAddress','bIsCarPark','bIs4D','bIs4K','bIsIMAX','bIsJUMU','bIsDUBI','bIsEAT','iHotCinema'));
		$cinemaInfo = array();
		$cinemaId = array();
		foreach ($CinemaList as $v)
		{
			$cinemaId[] = $v['iCinemaID'];
		}
		$arArrange = CinemaProcess::getRoomMovieList($cinemaId,0,array('iEpiaoCinemaID'));
		$cinemaInfo = array();
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
		GeneralFunc::getJson(array('flag'=>true,'cinemaList'=>$cinemaInfo));
	}


	/**
	 * 获取城区信息
	 */
	public static function actionGetRegionInfo(){
		$city = 1;
		if(isset($_REQUEST['iCityID']) && !empty($_REQUEST['iCityID'])){
			$city = $_REQUEST['iCityID'];
		}
		$RegionList = CityProcess::getRegionList($city);
		GeneralFunc::getJson(array("ok"=>true,"data"=>$RegionList));
	}

	/**
	 * 获取用户搜索记录
	 */
	public static function actionGetCinemaSearchLog(){
		$ret['ok'] = false;
		$uSessInfo = UserProcess::getLoginSessionInfo();
		$openid = isset($uSessInfo['openid'])?$uSessInfo['openid']:'';
		$sPhone = isset($uSessInfo['sPhone'])?$uSessInfo['sPhone']:'';
		if(!empty($openid)){
			$retSearchLog = CinemaProcess::getCinemaSearchLog($openid,"",array('searchid','search'));
			$ret = array('ok'=>true,'data'=>$retSearchLog);
		}
		if(!empty($sPhone)){
			$IsoutPart = UserProcess::getOpenidBysPhone($sPhone,array('outPart'));
			if(!empty($IsoutPart)){
				$retSearchLog = CinemaProcess::getCinemaSearchLog($IsoutPart['outPart'],"",array('searchid','search'));
				$ret = array('ok'=>true,'data'=>$retSearchLog);
			}
		}
		GeneralFunc::getJson($ret);
	}

	/**
	 * 设置搜索记录
	 */
	public static function actionSetCinemaSearchLog(){
		$ret['ok'] = true;
		$uSessInfo = UserProcess::getLoginSessionInfo();
		$openid = isset($uSessInfo['openid'])?$uSessInfo['openid']:'';
		$sPhone = isset($uSessInfo['sPhone'])?$uSessInfo['sPhone']:'';
		$search = htmlspecialchars($_REQUEST['search']);
		if(!empty($openid)){
			$retSearchLog = CinemaProcess::getCinemaSearchLog($openid,$search);
			if(empty($retSearchLog)){
				CinemaProcess::setCinemaSearchLog($openid,$search);
//				$ret = array('ok'=>true);
			}

		}
		if(!empty($sPhone)){
			$IsoutPart = UserProcess::getOpenidBysPhone($sPhone,array('outPart'));
			if(!empty($IsoutPart)){
				$retSearchLog = CinemaProcess::getCinemaSearchLog($openid,$search);
				if(empty($retSearchLog)){
					CinemaProcess::setCinemaSearchLog($openid,$search);
//					$ret = array('ok'=>true);
				}
			}
		}
		GeneralFunc::getJson($ret);
	}

	//获取搜索影院列表
	public static function actionGetSearchCinemaInfo(){
		$city = 1;
		if(isset($_REQUEST['iCityID']) && !empty($_REQUEST['iCityID'])){
			$city = $_REQUEST['iCityID'];
		}
		$lat1 = $_REQUEST['latitude'];    //纬度
		$lng1 = $_REQUEST['longitude'];    //经度
		$search = $_REQUEST['search'];
		$iMovieID = isset($_REQUEST['iMovieID']) ? $_REQUEST['iMovieID']:0;

		$CinemaList = CinemaProcess::getCinemaListByCity($city,array('iCinemaID','minPrice','sCoordinates','sCinemaName','iCityID','sRegion','sAddress','bIsCarPark','bIs4D','bIs4K','bIsIMAX','bIsJUMU','bIsDUBI','bIsEAT','iHotCinema','toFirstPinyin(sCinemaName) as FirstPinyin'),$search);
		$cinemaId = array();
		foreach ($CinemaList as $v)
		{
			$cinemaId[] = $v['iCinemaID'];
		}
		if(empty($cinemaId)){
			GeneralFunc::getJson(array("ok"=>false,"msg"=>"您搜索的影院不存在！"));exit(0);
		}
		$arArrange = CinemaProcess::getRoomMovieList($cinemaId,$iMovieID,array('iEpiaoCinemaID'));

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
					break;
				}
			}
		}
		if(empty($cinemaInfo)){
			GeneralFunc::getJson(array("ok"=>false,"msg"=>"您搜索的影院不存在！"));exit(0);
		}
		usort($cinemaInfo,function($a, $b){
			if ($a['cinemaInfo']['dist'] < $b['cinemaInfo']['dist'])
				return -1;
			else if ($a['cinemaInfo']['dist'] == $b['cinemaInfo']['dist'])
				return 0;
			else
				return 1;
		});
		GeneralFunc::getJson(array("ok"=>true,"data"=>$cinemaInfo));
	}

	/**
	 * 获取影片详情
	 */
	public static function actionGetMovieInfo()
	{
		$movieID = $_REQUEST['movieId'];

		//获取影片信息
		$movieInfo = MovieProcess::getMovieInfoByMovieID($movieID);

		if(FALSE==empty($movieInfo['sImageUrl']))
		{
			//@todo 后续需改为配置
			$movieInfo['sImageUrl'] = 'http://m.epiaowang.com'.$movieInfo['sImageUrl'];
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
		GeneralFunc::getJson(array('ok'=>true,'data'=>$movieInfo));

	}

	/**
	 * 获取影片上映影院
	 */
	public static function actionGetMovieCinemaList()
	{
		if(empty($_REQUEST['movieId']))
		{
			GeneralFunc::getJson(array('ok'=>false,'msg'=>'影片编号不能为空'));
		}
		$movieId = $_REQUEST['movieId'];
		$cityId = empty($_REQUEST['cityId']) ? 1 : $_REQUEST['cityId'];
		$cityInfo = CityProcess::getCityInfoByCityId($cityId);
		if (empty($cityInfo)){
			GeneralFunc::getJson(array("ok"=>false,'msg'=>'城市信息不存在'));
		}
		$arMovieInfo = MovieProcess::getMovieInfoByMovieID($movieId);
		if (empty($arMovieInfo))
		{
			GeneralFunc::getJson(array('ok'=>false,'msg'=>'该影片不存在'));
		}
		if(empty($_REQUEST['longitude']) || empty($_REQUEST['latitude'])){
			GeneralFunc::getJson(array('ok'=>false,'msg'=>'经纬度不得为空'));
		}
		$lat1 = $_REQUEST['latitude'];    //纬度
		$lng1 = $_REQUEST['longitude'];    //经度
		$arCinemaInfo = CinemaProcess::getCinemaListByCity($cityId,array('iCinemaID','sCoordinates','sCinemaName','sRegion','sAddress','bIsCarPark','bIs4D','bIs4K','bIsIMAX','bIsJUMU','bIsDUBI','bIsEAT','iHotCinema'));
//		print_r($arCinemaInfo);die;
		if (empty($arCinemaInfo))
		{
			GeneralFunc::getJson(array('ok'=>false,'msg'=>'该城市没有任何影院'));
		}
		$moviecinemaInfo = array();
		$date = array();
		$dateDay = strtotime(date("Y-m-d"));
		foreach ($arCinemaInfo as $v)
		{
			$cinemaId[] = $v['iCinemaID'];
		}
		$arRoomMovieList = CinemaProcess::getRoomMovieListByMoveID($cinemaId, $movieId);
//		print_r($arRoomMovieList);die;
		foreach ($arCinemaInfo as $k=>&$row)
		{
			$i = 0;
			if(!empty($arRoomMovieList)){
				foreach ($arRoomMovieList as $keys =>$v)
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
					$moviecinemaInfo[$keys] = $row;
					$moviecinemaInfo[$keys]['date'][] = $key;
					$date[$key]=1;
					$i++;
				}
			}
		}
		$moviecinemaInfo = array_values($moviecinemaInfo);
		ksort($date);
		usort($moviecinemaInfo,function($a, $b){
			if ($a['dist'] < $b['dist'])
				return -1;
			else if ($a['dist'] == $b['dist'])
				return 0;
			else
				return 1;
		});
		GeneralFunc::getJson(array('ok'=>true,'data'=>$moviecinemaInfo,'cityInfo'=>$cityInfo,'date'=>$date));
	}

	/**
	 * 获取城市列表
	 */
	public function actionGetCityList(){
		$CityList = CityProcess::getCityInfo();
		GeneralFunc::getJson(array('ok'=>true,'data'=>$CityList));
	}

	/**
	 * 获取城市详情
	 */
	public function actionGetCityInfo(){
		$cityId = empty($_REQUEST['cityId']) ? 1 : $_REQUEST['cityId'];
		$cityInfo = CityProcess::getCityInfoByCityId($cityId);
		GeneralFunc::getJson(array('ok'=>true,'data'=>$cityInfo));
	}

	/**
	 * 获取城市详情
	 */
	public function actionGetCityInfoByName(){
		$sCityName = empty($_REQUEST['sCityName']) ? "北京市" : $_REQUEST['sCityName'];
		$cityInfo = CityProcess::getCityInfoByName($sCityName);
		GeneralFunc::getJson(array('ok'=>true,'data'=>$cityInfo));
	}

	/**
	 * 获取影片排期
	 */
	public function actionGetMovieArrangeList(){
		$iMovieID = empty($_REQUEST['movieID'])?0:$_REQUEST['movieID'];
		$iCinemaID = empty($_REQUEST['cinemaID'])?0:$_REQUEST['cinemaID'];
		if(!empty($iMovieID)){
			$movieInfo = MovieProcess::getMovieMatchInfo($iMovieID);
			if (empty($movieInfo))
			{
				GeneralFunc::getJson(array('ok'=>false,'msg'=>'该影片不存在'));
			}
		}

		$arCinemaInfo = CinemaProcess::getOutCinemaMatchInfo($iCinemaID);

		$arRoomMovieList = CinemaProcess::getRoomMovieListByCinema($iCinemaID);
		if(empty($arRoomMovieList)){
			GeneralFunc::getJson(array("ok"=>true,'movie'=>array(),'cinema'=>$arCinemaInfo,'arrange'=>array()));
		}
		$arMovie = array();
		foreach ($arRoomMovieList as $key =>$v)
		{
			foreach ($v as  $date=>$v1)
			{
				if (!empty($arMovie[$key]))
				{
					continue;
				}
				// 根据排期里的影片ID，获得影片
				$movieInfo = MovieProcess::getMovieInfoByMovieID($key);
				if (empty($movieInfo))
				{
					continue;
				}
				$arMovie[$key] = $movieInfo;
			}

		}
		GeneralFunc::getJson(array('ok'=>true,'movie'=>$arMovie,'cinema'=>$arCinemaInfo,'arrange'=>$arRoomMovieList));
	}

	/**
	 * 获取影厅信息
	 */
	public function actionGetRoomInfo(){
		$iRoomMovieID = empty($_REQUEST['roommovieid'])?0:$_REQUEST['roommovieid'];
		if(empty($iRoomMovieID)){
			GeneralFunc::getJson(array('ok'=>false,'msg'=>'排期编号不能为空'));
		}
		$arArrangeInfo = CinemaProcess::getRoomMovieListByiRoommovieID($iRoomMovieID);
		if(empty($arArrangeInfo))
		{
			GeneralFunc::getJson(array('ok'=>false,'msg'=>'场次已经过期'));
		}
		if(strtotime($arArrangeInfo["dBeginTime"])<=time())
		{
			GeneralFunc::getJson(array('ok'=>false,'msg'=>"场次已经过期"));
		}

		//加入太和的影片和影院名称
		$MovieMatch = OutMovieProcess::getMatchMovieInfo($arArrangeInfo['iMovieID']);
		$arArrangeInfo['sThMovieName'] = $MovieMatch['sThMovieName'];

		$CinemaMatch = CinemaProcess::getOutCinemaMatchInfo($arArrangeInfo['iEpiaoCinemaID']);
		$arArrangeInfo['sThCinemaName'] = $CinemaMatch['sThCinemaName'];

		$uSessInfo = UserProcess::getLoginSessionInfo();
		$phone = isset($uSessInfo['sPhone'])?$uSessInfo['sPhone']:'';
		$ret = array('ok'=>true,'data'=>array('seat'=>$arArrangeInfo,'phone'=>$phone));
		GeneralFunc::getJson($ret);
	}

	/**
	 * 获取影厅信息
	 */
	public function actionGetRoomSeatInfo(){
		$iRoomMovieID = empty($_REQUEST['roommovieid'])?0:$_REQUEST['roommovieid'];
		$arArrangeInfo = CinemaProcess::getRoomMovieListByiRoommovieID($iRoomMovieID);
		if (empty($arArrangeInfo))
		{
			GeneralFunc::getJson(array());
		}
		//获取影厅座位图状态
		$arRoomInfo = CinemaProcess::getRoomInfo($arArrangeInfo['iRoomID']);
		if (empty($arRoomInfo['sSeatInfo']))
		{
			GeneralFunc::getJson(array());
		}
		$arSeatInfo = json_decode($arRoomInfo['sSeatInfo'],true);
		$bookSeatingArrange['sRoomMovieInterfaceNo'] = $arArrangeInfo['sRoomMovieInterfaceNo'];
		$bookSeatingArrange['iInterfaceID'] = $arArrangeInfo['iInterfaceID'];
		$bookSeatingArrange['sCinemaInterfaceNo'] = $arArrangeInfo['sCinemaInterfaceNo'];
		$bookSeatingArrange['sRoomInterfaceNo'] = $arArrangeInfo['sRoomInterfaceNo'];
		$arLockSeatInfo = CinemaInterfaceProcess::GetSelectedSeat($bookSeatingArrange);
		foreach ($arLockSeatInfo as $lockSeatInfo)
		{
			foreach($arSeatInfo['seatinfo'] as &$seatInfo)
			{
				if (($lockSeatInfo['ColumnId']  == $seatInfo['seatCol']
						&&  $lockSeatInfo['RowId']  == $seatInfo['seatRow'])
					|| $lockSeatInfo['SeatId'] == $seatInfo['SeatNo'])
				{
					$seatInfo['seatState'] = $lockSeatInfo['SeatStatus'];
				}
			}
		}
		GeneralFunc::getJson(json_encode($arSeatInfo));
	}

	/**
	 * 创建在线选座订单
	 */
	public function actionCreateSeatOrder(){
		$iRoomMovieID = empty($_REQUEST['plan_id'])?0:$_REQUEST['plan_id'];
		$seat_no=  $_REQUEST['seat_no'];
		$seat_info=  $_REQUEST['seat_info'];
		$sendPhone=  $_REQUEST['mobile'];
		$fromClient=$_REQUEST['fromClient'];
		$uSessInfo = UserProcess::getLoginSessionInfo();
		if(empty($uSessInfo)){
			GeneralFunc::getJson(array("status"=>"3","error"=>"用户登陆失败，请重新登录"));;
		}
		$iUserID=$uSessInfo['iUserID'];
		$type=ConfigParse::getOrdersKey('onlineSeatOrder');
		$returnUrl="";
		$iSelectedCount=count(explode("@@",$seat_no));
		if ($iSelectedCount<=0)
		{
			GeneralFunc::getJson(array("status"=>1,"error"=>"请至少选择一个座位。"));
		}
		$roomMovieInfo= CinemaProcess::getRoomMovieListByiRoommovieID($iRoomMovieID);
		if (empty($roomMovieInfo))
		{
			GeneralFunc::getJson(array("status"=>1,"error"=>"该影厅没有排期"));
		}
		$dEndBuyTime = strtotime($roomMovieInfo['dEndBuyDate']);
		if ($dEndBuyTime<time())
		{
			GeneralFunc::getJson(array("status"=>1,"error"=>"您选择的场次已经过期，请选择其他场次。"));
		}
		GeneralFunc::writeLog('createBookSeatingOrder,sPhone:'.$sendPhone.",user_agent:".$_SERVER['HTTP_USER_AGENT'], Yii::app()->getRuntimePath().'/H5yii/');
		$iHuoDongItemID=33;
		$isWeChatapplet=0;
		// 判断是否是在微信浏览器里
		$agent = $_SERVER ['HTTP_USER_AGENT'];
		if (! strpos ( $agent, "icroMessenger" )) {
			//不是在微信浏览器里
			$isWeChatapplet=2;
		}

		$MovieMatchInfo = MovieProcess::getMovieMatchInfo($roomMovieInfo['iMovieID']);
		if(empty($MovieMatchInfo)){
			GeneralFunc::getJson(array("status"=>1,"error"=>"该影片不存在"));
		}
		$rel=OOrderProcess::createSeatOrder($iUserID, $iRoomMovieID, $uSessInfo['sPhone'], $iSelectedCount, $seat_no, $seat_info, $fromClient, $returnUrl, $iHuoDongItemID, $type,$isWeChatapplet);
		GeneralFunc::writeLog('createSeatOrder'.print_r($rel,true), Yii::app()->getRuntimePath().'/H5yii/');
		$rel2= OCinemaInterfaceProcess::GetCreateOrderResult($rel["data"]["outerOrderId"]);
		GeneralFunc::writeLog('GetCreateOrderResult:'.print_r($rel2,true), Yii::app()->getRuntimePath().'/H5yii/');
		if($rel2["ResultCode"]==ConfigParse::getOrderStatusKey('ORDER_STATUS_Successed'))
		{
			$orderSeatInfo=array(
				'iUserId'=>$iUserID,
				'outerOrderId'=>$rel["data"]["outerOrderId"],
				'sInterfaceOrderNo'=>$rel2["InterfaceOrderNo"]
			);
			OOrderProcess::updateOrderSeatByOrderInfo($iUserID, $orderSeatInfo);
			GeneralFunc::writeLog('updateOrderSeatByOrderInfo', Yii::app()->getRuntimePath().'/H5yii/');
			//调用太和创建订单
			$ThRet = ThOrderProcess::createThOrder($MovieMatchInfo['iThMovieID'],$MovieMatchInfo['sThMovieName'],$rel["data"]["outerOrderId"],$roomMovieInfo['iEpiaoCinemaID'],$roomMovieInfo['iMovieID'],$roomMovieInfo['mPrice'],$iRoomMovieID,$uSessInfo['sPhone'],$roomMovieInfo['sRoomName'],$roomMovieInfo['sDimensional'],$roomMovieInfo['sLanguage'],'',$roomMovieInfo['dBeginTime'],$seat_info,$iSelectedCount);
			GeneralFunc::writeLog('createThOrder'.print_r($ThRet,true), Yii::app()->getRuntimePath().'/H5yii/');
		}
		else{
			GeneralFunc::getJson(array("status"=>1,"error"=>"哎呀，座位可能被抢占了，请更换座位或稍后尝试。",'data'=>$rel["data"]["outerOrderId"]));
		}
		if($rel["ok"])
		{
			$maxPrice = explode(".",$rel["data"]["mPrice"]);
			GeneralFunc::getJson(array("status"=>0,"error"=>GeneralFunc::substrOrderID($rel["data"]["outerOrderId"]),"mPrice"=>$maxPrice[0]));
		}
		else {
			GeneralFunc::getJson(array("status"=>1,"error"=>$rel["data"]));
		}
	}

	/**
	 * 订单详情
	 */
	public function actionGetSeatOrderInfo(){
		$outOrderId = $_REQUEST['orderId'];
		$outOrderId = OOrderProcess::getThSubOrderInfo($outOrderId)['outerOrderId'];
		$userOrderInfo = OOrderProcess::getOrderSeatInfoByOuterOrderId($outOrderId);
		if(empty($userOrderInfo)){
			GeneralFunc::getJson(array("ok"=>false,'err_code'=>201,'msg'=>'订单不存在'));
		}
		$uSessInfo = UserProcess::getLoginSessionInfo();
		if(empty($uSessInfo)){
			GeneralFunc::getJson(array("ok"=>false,'err_code'=>301,'msg'=>'用户登陆失败,，请重新登录'));
		}
		$iUserID=$uSessInfo['iUserID'];
		if ($userOrderInfo['iUserId'] != $iUserID)
		{
			GeneralFunc::getJson(array("ok"=>false,'err_code'=>301,'msg'=>'该订单不属于您'));
		}
		$userOrderInfo["createTime"] = 900-(time()- strtotime($userOrderInfo["createTime"]));
		if ($userOrderInfo["createTime"]<=0)
		{
			GeneralFunc::getJson(array("ok"=>false,'err_code'=>202,'msg'=>'订单已过期'));
		}
		if ($userOrderInfo['orderStatus'] != ConfigParse::getPayStatusKey('orderNoPay'))
		{
			GeneralFunc::getJson(array("ok"=>false,'err_code'=>202,'msg'=>'该笔订单不是待支付订单'));
		}
		$arArrangeInfo= CinemaProcess::getRoomMovieListByiRoommovieID($userOrderInfo['iRoomMovieID']);
		if (empty($arArrangeInfo))
		{
			GeneralFunc::getJson(array("ok"=>false,'err_code'=>202,'msg'=>'该场次已过期，请重新选择场次'));
		}
		if (empty($userOrderInfo['sInterfaceOrderNo']))
		{
			GeneralFunc::getJson(array("ok"=>false,'err_code'=>202,'msg'=>'创建订单异常，请重新下单'));
		}

		//加入太和的影片和影院名称
		$MovieMatch = OutMovieProcess::getMatchMovieInfo($arArrangeInfo['iMovieID']);
		$userOrderInfo['sThMovieName'] = $MovieMatch['sThMovieName'];

		$CinemaMatch = CinemaProcess::getOutCinemaMatchInfo($arArrangeInfo['iEpiaoCinemaID']);
		$userOrderInfo['sThCinemaName'] = $CinemaMatch['sThCinemaName'];

		GeneralFunc::getJson(array("ok"=>true,'order'=>$userOrderInfo,'usermoney'=>  UserProcess::getmAccountMoney($iUserID)));
	}

	/**
	 * 成功页
	 */
	public function actionGetSuccessOrder(){
		if (empty($_REQUEST['orderId']) || empty($_REQUEST['orderId']))
		{
			GeneralFunc::getJson(array("ok"=>false,'err_code'=>301,'msg'=>'参数非法'));
		}
		$userId = $_REQUEST['userId'];
		$outerOrderId = $_REQUEST['orderId'];
		$arOrderInfo = OOrderProcess::getOrderSeatInfoByOuterOrderId($outerOrderId);
		if (empty($arOrderInfo))
		{
			GeneralFunc::getJson(array("ok"=>false,'err_code'=>301,'msg'=>'订单不存在'));
		}

		if ($arOrderInfo['iUserId'] != $userId)
		{
			GeneralFunc::getJson(array("ok"=>false,'err_code'=>301,'msg'=>'该订单不属于您'));
		}

		if ($arOrderInfo['orderType'] != ConfigParse::getOrdersKey('onlineSeatOrder'))
		{
			GeneralFunc::getJson(array("ok"=>false,'err_code'=>301,'msg'=>'订单不是选座订单'));
		}


		if ($arOrderInfo['orderStatus'] == ConfigParse::getPayStatusKey('orderNoPay') ||
			$arOrderInfo['orderStatus'] >= ConfigParse::getPayStatusKey('orderEnd') )
		{
			GeneralFunc::getJson(array("ok"=>false,'err_code'=>301,'msg'=>'订单未成功'));
		}
		GeneralFunc::getJson(array('ok'=>true,'data'=>$arOrderInfo));
	}
}