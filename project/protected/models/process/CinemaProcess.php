<?php

/**
 * CinemaProcess - 影院操作类
 * @author luzhizhong
 * @version V1.0
 */

date_default_timezone_set("Asia/Shanghai");
class CinemaProcess
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
	 * 获取影院详情（By 影院id）
	 *
	 * @param int $cityID 城市id
	 * @param array[] $fields 所要字段列表，一维数组，如果为空则获取全部
	 * @return array[][] 影院列表，二维数组
	 */
	public static function getCinemaListByCity($cityID, $fields=array(),$search="")
	{
		if(empty($cityID))
		{
			return array();
		}
		$selStr = (is_array($fields) and !empty($fields)) ? implode(',', $fields) : '*';
		if($search != ""){
			$sql = sprintf("SELECT ".$selStr." FROM {{b_cinema}} WHERE icityid=$cityID AND (iInterfaceID=11 or iInterfaceID=10 or iInterfaceID=8 or iInterfaceID=5) and (sCinemaName LIKE '%%%s%%' OR toAllPinyin(sCinemaName) LIKE '%%%s%%' OR toFirstPinyin(sCinemaName) LIKE '%%%s%%')",$search, $search, $search);
		}else{
			$sql = sprintf("SELECT ".$selStr." FROM {{b_cinema}} WHERE iCityID=%d AND (iInterfaceID=11 or iInterfaceID=10 or iInterfaceID=8 or iInterfaceID=5)", $cityID);
		}
		return DbUtil::queryAll($sql);
	}

	/**
	 * 获取影院列表（By 城市id）
	 *
	 * @param int $cityID 城市id
	 * @param array[] $fields 所要字段列表，一维数组，如果为空则获取全部
	 * @return array[][] 影院列表，二维数组
	 */
	public static function getCinemaListByCinemaID($cinemaID, $fields=array())
	{
		if(empty($cinemaID))
		{
			return array();
		}

		$cinemaDBObj = new B_CinemaDB();

		//过滤掉无效字段
		$fields = array_intersect($fields, $cinemaDBObj->attributeNames());
		$selStr = (is_array($fields) and !empty($fields)) ? '`'.implode('`,`', $fields).'`' : '*';

		$sql = sprintf("SELECT ".$selStr." FROM {{b_cinema}} WHERE iCinemaID=%d AND iInterfaceID!=''", $cinemaID);
		return DbUtil::queryAll($sql);
	}

	/**
	 * 获取E票网影院和太和影院匹配的信息
	 * @param $iCinemaID
	 * @return array
	 */
	public static function getCinemaMatchInfo($iCinemaID){
		if(empty($iCinemaID)){
			return array();
		}
		$sql = sprintf("SELECT * FROM {{out_cinema}} WHERE iCinemaID=%d and state=1", $iCinemaID);
		return DbUtil::queryRow($sql);
	}

	/**
	 * 太和影院和E票网影院信息
	 * @param $iCinemaID
	 * @return array
	 */
	public static function getOutCinemaMatchInfo($iCinemaID){
		$CinemaMatchInfo = self::getCinemaMatchInfo($iCinemaID);
		if(empty($CinemaMatchInfo)){
			GeneralFunc::getJson(array('ok'=>false,'msg'=>'该影院不存在'));
		}
		$CinemaInfo = self::getCinemaListByCinemaID($iCinemaID);
		if(empty($CinemaInfo)){
			GeneralFunc::getJson(array('ok'=>false,'msg'=>'该影院暂无排期'));
		}
		$CinemaMatchInfo['sTel'] = $CinemaInfo[0]['sTel'];
		return $CinemaMatchInfo;
	}

	/**
	 * 获取影院列表串（By 城市id）
	 *
	 * @param int $cityID 城市id
	 * @return string 影院列表串（形如：id1,id2,id3）
	 */
	public static function getCinemasByCity($cityID)
	{
		if(empty($cityID))
		{
			return array();
		}

		$cinemaList = self::getCinemaListByCity($cityID, array('iCinemaID'));

		//转换成id串
		$tempList = array();
		foreach ($cinemaList as $index => $cinemaInfo)
		{
			$tempList[] = $cinemaInfo['iCinemaID'];
		}
		$tempList = array_unique($tempList);

		return empty($tempList) ? '' : implode(',', $tempList);
	}


	/**
	 * 太和获取影院列表串（By 城市id）
	 *
	 * @param int $cityID 城市id
	 * @return string 影院列表串（形如：id1,id2,id3）
	 */
	public static function getCinemaIdListByCity($cityID)
	{
		if(empty($cityID))
		{
			return '';
		}
		$cinemaList = self::getMatchCinemaIdList($cityID);

		//转换成id串
		$tempList = array();
		foreach ($cinemaList as $index => $cinemaInfo)
		{
			$tempList[] = $cinemaInfo['iCinemaID'];
		}
		$tempList = array_unique($tempList);

		return empty($tempList) ? '' : implode(',', $tempList);
	}

	/**
	 * 太和,获取已匹配的影院id
	 * @param $cityID
	 * @return array
	 */
	public static function getMatchCinemaIdList($cityID){
		if(empty($cityID))
		{
			return array();
		}
		$sql = sprintf("SELECT iCinemaID FROM {{out_cinema}} WHERE iCinemaID IN (SELECT iCinemaID FROM {{b_cinema}} WHERE iCityID=%d AND (iInterfaceID=11 or iInterfaceID=10 or iInterfaceID=8 or iInterfaceID=5)) AND state=1",$cityID);
		return DbUtil::queryAll($sql);
	}

	/**
	 * 获取影片上映影院
	 *
	 *@param int $cityID 城市id
	 * @return string 影院列表串（形如：id1,id2,id3）
	 * 只需要一个最低价格和影院ID
	 **/
	public static function getRoomMovieListByMoveID($cinemaIds,$movieId,$iUserID=0)
	{
		$arRoomMovieList = self::getRoomMovieList($cinemaIds,$movieId,array('min(huodongPrice) as minPrice','iEpiaoCinemaID','dBeginTime','dEndBuyDate'));
		$dateNow = date("Y-m-d H:i:s");
		if(!empty($arRoomMovieList)){
			foreach ($arRoomMovieList as $k => &$arRoomMovie)
			{
				if ($arRoomMovie['dEndBuyDate']>$dateNow )
				{
					if($movieId == 1913){
						$hour = date('H');
						$OrderSeatInfo = OrderProcess::getOrderSeatInfoByMovieId($movieId);
						if($OrderSeatInfo['OrderNum']){
							if($OrderSeatInfo['OrderNum'] < 50){
								if($iUserID!=0){
									$UserOrder = OrderProcess::getUserOrderByMovieID($iUserID,$movieId);
									if(!$UserOrder['UserNum']||$UserOrder['UserNum']<1){
										if (($hour>=0 && $hour<8) || $hour>=23)
										{
											$arRoomMovie['minPrice'] = '19.9';
										}else{
											$arRoomMovie['minPrice'] = '9.9';
										}
									}
								}else{
									if (($hour>=0 && $hour<8) || $hour>=23)
									{
										$arRoomMovie['minPrice'] = '19.9';
									}else{
										$arRoomMovie['minPrice'] = '9.9';
									}
								}
							}
							$arRoomMovie['count'] = $OrderSeatInfo['OrderNum'];
						}else{
							if (($hour>=0 && $hour<8) || $hour>=23)
							{
								$arRoomMovie['minPrice'] = '19.9';
							}else{
								$arRoomMovie['minPrice'] = '9.9';
							}
						}
					}
				}
			}
			$arArrange= array('ok'=>true,'data'=>$arRoomMovieList);
			return $arArrange['data'];
		}
		return 0;
	}

	/**
	 * 获取影片排期
	 *
	 *@param int $cityID 城市id
	 * @return string 影院列表串（形如：id1,id2,id3）
	 **/
	public static function getRoomMovieListByCinemaId($cinemaId,$iUserID)
	{
		$dateDay = strtotime(date("Y-m-d"));
		$arRoomMovieList = self::getRoomMovieList($cinemaId,'',array('iMovieID','iRoomID','iRoomMovieID','sRoomName','sDimensional','sLanguage','huodongPrice','dBeginTime','dEndTime','dEndBuyDate'));
		$dateNow = date("Y-m-d H:i:s");
		$aMovieList = array();
		$iMovieID = array();
		foreach ($arRoomMovieList as &$arRoomMovie)
		{
			if ($arRoomMovie['dEndBuyDate']>$dateNow )
			{
				$roomInfo = CinemaProcess::getRoomInfo($arRoomMovie['iRoomID'],array('isVip'));
				if($roomInfo['isVip'] == 1){
					if(!strstr(strtolower($arRoomMovie['sRoomName']),'vip')){
						$arRoomMovie['sRoomName'] .= '(vip厅)';
					}
				}
				$date = date("Y-m-d",strtotime($arRoomMovie['dBeginTime']));
				$days=round((strtotime($date)-$dateDay)/86400);
				if ($days>6)
				{
					continue;
				}
				if(empty($iMovieID[$arRoomMovie['iMovieID']]) || $iMovieID[$arRoomMovie['iMovieID']] != $arRoomMovie['iMovieID']){
					$arMovieInfo = MovieProcess::getMovieInfoByMovieID($arRoomMovie['iMovieID']);
					if (empty($arMovieInfo))
					{
						continue;
					}
					$aMovieList[$arRoomMovie['iMovieID']]['iMovieID'] = $arRoomMovie['iMovieID'];
					$aMovieList[$arRoomMovie['iMovieID']]['sMovieName'] = $arMovieInfo['sMovieName'];
					$aMovieList[$arRoomMovie['iMovieID']]['sImageUrl'] = $arMovieInfo['sImageUrl'];
					$iMovieID[$arRoomMovie['iMovieID']] = $arRoomMovie['iMovieID'];
				}
				if(empty($aMovieList[$arRoomMovie['iMovieID']])){
					continue;
				}
				$dBeginTime = $arRoomMovie['dBeginTime'];
				$time = strtotime($dBeginTime);
				$arRoomMovie['dBeginTime'] = date("H:i",$time);
				if(explode('.',$arRoomMovie['huodongPrice'])[1] == 0){
					$arRoomMovie['huodongPrice'] = explode('.',$arRoomMovie['huodongPrice'])[0];
				}else{
					$arRoomMovie['huodongPrice'] = round($arRoomMovie['huodongPrice'],1);
				}
				//追龙
				if($arRoomMovie['iMovieID'] == 1913){
					$hour = date('H');
					$OrderSeatInfo = OrderProcess::getOrderSeatInfoByMovieId($arRoomMovie['iMovieID']);
					if($OrderSeatInfo['OrderNum']){
						if($OrderSeatInfo['OrderNum'] < 50){
							if($iUserID!=0){
								$UserOrder = OrderProcess::getUserOrderByMovieID($iUserID,$arRoomMovie['iMovieID']);
								if(!$UserOrder['UserNum']||$UserOrder['UserNum']<1){
									if (($hour>=0 && $hour<8) || $hour>=23)
									{
										$arRoomMovie['minPrice'] = '19.9';
									}else{
										$arRoomMovie['minPrice'] = '9.9';
									}
								}
							}else{
								if (($hour>=0 && $hour<8) || $hour>=23)
								{
									$arRoomMovie['minPrice'] = '19.9';
								}else{
									$arRoomMovie['minPrice'] = '9.9';
								}
							}
						}
						$arRoomMovie['count'] = $OrderSeatInfo['OrderNum'];
					}else{
						if (($hour>=0 && $hour<8) || $hour>=23)
						{
							$arRoomMovie['minPrice'] = '19.9';
						}else{
							$arRoomMovie['minPrice'] = '9.9';
						}
					}
				}

				$arRoomMovie['mPrice'] = $arRoomMovie['huodongPrice'];
				$dEndTime = $arRoomMovie['dEndTime'];
				$time = strtotime($dEndTime);
				$arRoomMovie['dEndTime'] = date("H:i",$time);
				if(strtotime($arRoomMovie['dEndTime'])<strtotime($arRoomMovie['dBeginTime'])){
					$arRoomMovie['dEndTime'] = '次日'.$arRoomMovie['dEndTime'];
				}
				$aMovieList[$arRoomMovie['iMovieID']]['aDateList'][$days]['sDate'] =  PublicProcess::dateTime($days,$date);
				$aMovieList[$arRoomMovie['iMovieID']]['aDateList'][$days]['aArrangeList'][] = $arRoomMovie;
			}
		}
		foreach($aMovieList as &$v){
			foreach($v['aDateList'] as $k =>$v1){
				if($k != 0){
					$v['aDateList'][0]['sDate'] =  PublicProcess::dateTime(0,date("Y-m-d"));
					$v['aDateList'][0]['aArrangeList'] = "";
				}
				break;
			}
		}
		$arArrange= array('ok'=>true,'data'=>$aMovieList);
		return $arArrange['data'];
	}

	public static function getRoomMovieListByCinema($cinemaId){
		$dateDay = strtotime(date("Y-m-d"));
		$arRoomMovieList = self::getRoomMovieList($cinemaId,array('iMovieID','iRoomID','iRoomMovieID','sRoomName','sDimensional','sLanguage','huodongPrice','dBeginTime','dEndTime','dEndBuyDate','mCinemaPrice'));
		$dateNow = date("Y-m-d H:i:s");
		$arRoomList = array();
		foreach ($arRoomMovieList as &$arRoomMovie)
		{
			$roomInfo = CinemaProcess::getRoomInfo($arRoomMovie['iRoomID'],array('isVip'));
			if($roomInfo['isVip'] == 1){
				if(!strstr(strtolower($arRoomMovie['sRoomName']),'vip')){
					$arRoomMovie['sRoomName'] .= '(vip厅)';
				}
			}
			$movieId = $arRoomMovie['iMovieID'];
			if (empty($movieId))
				continue;
			$arMovieMatchInfo = OutMovieProcess::getMatchMovieInfo($movieId);
			if(empty($arMovieMatchInfo)){
				continue;
			}
			$date = date("Y-m-d",strtotime($arRoomMovie['dBeginTime']));
			$days=round((strtotime($date)-$dateDay)/86400);
			if ($days>6)
			{
				continue;
			}
			$arRoomList[$movieId][$date][] = $arRoomMovie;
		}
		return $arRoomList;
	}

	// 根据影院获取所有排期
	public static function getRoomMovieList($cinemaId,$movieId=0,$fields=array())
	{
		$DateNow = date('Y-m-d H:i:s');
		if(empty($cinemaId))
		{
			return array();
		}
		$cinemaDBObj = new B_CinemaDB();
		//过滤掉无效字段
		//$fields = array_intersect($fields, $cinemaDBObj->attributeNames());
		$selStr = (is_array($fields) and !empty($fields)) ? implode(',', $fields) : '*';
		if(is_array($cinemaId) && !empty($cinemaId)){
			if($movieId == 0){
				$SQL=sprintf("select ".$selStr." from {{e_roommovie}} where iEpiaoCinemaID in (%s) and dEndBuyDate>'%s'",implode(',',$cinemaId),$DateNow);
			}else{
				$SQL=sprintf("select ".$selStr." from {{e_roommovie}} where iEpiaoCinemaID in (%s) and iMovieID = $movieId and dEndBuyDate>'%s' group by iEpiaoCinemaID,left(dBeginTime,10)",implode(',',$cinemaId),$DateNow);
			}
		}else{
			$SQL=sprintf("select ".$selStr." from {{e_roommovie}} where iEpiaoCinemaID in (%s) and dEndBuyDate>'%s'",$cinemaId,$DateNow);
		}
		return DbUtil::queryAll($SQL);
	}

	// 根据影院获取所有排期
	public static function getRoomMovieListByiRoommovieID($iRoommovieID,$fields=array())
	{
		if(empty($iRoommovieID))
		{
			return array();
		}

		$cinemaDBObj = new B_CinemaDB();
		//过滤掉无效字段
		$fields = array_intersect($fields, $cinemaDBObj->attributeNames());
		$selStr = (is_array($fields) and !empty($fields)) ? '`'.implode('`,`', $fields).'`' : '*';
		$sql = sprintf("SELECT ".$selStr." FROM {{e_roommovie}} where iRoommovieID='%s'",$iRoommovieID);
		return DbUtil::queryRow($sql);
	}

	// 获取座位图
	public static function getRoomInfo($iRoomID,$fields=array())
	{
		if(empty($iRoomID))
		{
			return array();
		}

		$cinemaDBObj = new B_RoomDB();
		//过滤掉无效字段
		$fields = array_intersect($fields, $cinemaDBObj->attributeNames());
		$selStr = (is_array($fields) and !empty($fields)) ? '`'.implode('`,`', $fields).'`' : '*';
		$sql = sprintf("SELECT ".$selStr." FROM {{b_room}} where iRoomID='%s'",$iRoomID);
		return DbUtil::queryRow($sql);
	}
    //电影卡限制影院查询
	public static function getCouponCinemaInfo($iCouponID,$iCinemaId)
	{
		$subSql[] = "iCouponID='".mysql_escape_string($iCouponID)."'";
		$subSql[] = "iCinemaID='".mysql_escape_string($iCinemaId)."'";
		$SQL = sprintf("select * from {{e_couponticketcinemaarea}} where %s ",implode(' and ', $subSql));
		$result= DbUtil::queryRow($SQL);
		return  $result;
	}

	public static function getRoomInfobyInterfaceId($iInterfaceID,$iCinemaNo,$iRoomNo)
	{
		$sql = "SELECT * FROM {{b_room}} WHERE iInterfaceID=$iInterfaceID and
                sCinemaInterfaceNo='".$iCinemaNo."' and sRoomInterfaceNo='".$iRoomNo."'";
		$result= DbUtil::queryAll($sql);
		return  $result;
	}

	public static function getRoomMovieListByCity()
	{
		$DateNow = date('Y-m-d H:i:s');
		$SQL=sprintf("select b.iCityID from {{e_roommovie}} as a,{{b_cinema}} as b where iEpiaoCinemaID = iCinemaID and dEndBuyDate>'%s' GROUP by iCityID",$DateNow);
		$result= DbUtil::queryAll($SQL);
		if (empty($result) || !isset($result))
		{
			return 0;
		}
		return  $result;
	}

	//search 不为空获取一条判断是否可以插入，为空则获取所有日志
	public static function getCinemaSearchLog($openid,$search="",$fields=array())
	{
		$cinemaDBObj = new B_CinemasearchlogDB();
		//过滤掉无效字段
		$fields = array_intersect($fields, $cinemaDBObj->attributeNames());
		$selStr = (is_array($fields) and !empty($fields)) ? '`'.implode('`,`', $fields).'`' : '*';
		if($search != ''){
			$sql = sprintf("SELECT * FROM {{b_cinemasearchlog}} WHERE openid='%s' and search='%s'",$openid,$search);
			return DbUtil::queryRow($sql);
		}else{
			$sql = sprintf("SELECT ".$selStr." FROM {{b_cinemasearchlog}} WHERE openid='%s' order by dCreateTime desc limit 5",$openid);
			return DbUtil::queryAll($sql);;
		}
	}
   //插入影院搜索记录，只保持最新的5条
	public static function setCinemaSearchLog($openid,$search)
	{
		$i = 0;
		$sql = sprintf("insert INTO {{b_cinemasearchlog}} (openid,search,dCreateTime)  VALUES ('%s','%s',NOW())",$openid,$search);
		if(DbUtil::execute($sql)){
			$i = 1;
			$sql = sprintf("SELECT * FROM {{b_cinemasearchlog}} WHERE openid='%s' order by dCreateTime asc",$openid);
			$result = DbUtil::queryAll($sql);
			if(count($result) > 5){
				$i = count($result)-5;
				foreach($result as $k => $v){
					if($i > 0){
						$data = $v['search'];
						$sql = sprintf("delete FROM {{b_cinemasearchlog}} WHERE openid='%s'and search='%s'",$openid,$data);
						DbUtil::execute($sql);
						$i--;
					}else{
						$i = -1;break;
					}
				}
			}
		}
		if($i != 0){
			return 1;
		}else{
			return 0;
		}
	}

	//删除特定的搜索记录
	public static function delCinemaSearchLog($openid,$searchid)
	{
		if($searchid != 'all'){
			$sql = sprintf("delete FROM tb_b_cinemasearchlog WHERE openid='%s' and searchid='%s'",$openid,$searchid);
			$succ     =DbUtil::execute($sql);
		}elseif($searchid == 'all'){
			$sql = sprintf("delete FROM tb_b_cinemasearchlog WHERE openid='%s'",$openid);
			$succ     = DbUtil::execute($sql);
		}
		return  $succ;
	}
}