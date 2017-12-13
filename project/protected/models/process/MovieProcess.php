<?php

/**
 * MovieProcess - 影片/排期操作类
 * @author luzhizhong
 * @version V1.0
 */


class MovieProcess
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
	 * 获取影片列表（By 城市id）
	 *
	 * @param string $cinemas 影院id串（形如：id1,id2,id3）
	 * @return array[][] 影片列表，二维数组
	 */
	public static function getCinemaListByCity($cinemas)
	{
		if(empty($cinemas))
		{
			return array();
		}
		
		$sql = sprintf("SELECT rm.iMovieID,MIN(rm.huodongPrice) AS minPrice,m.sMovieName,m.sDirector,m.sActor,m.iMovieScore,m.iRunTime,m.sImageUrl,rm.dBeginTime,m.iFavorMoiveID,m.dPlayTime
				FROM {{e_roommovie}} rm, {{b_movie}} m 
				WHERE rm.iEpiaoCinemaID IN (%s) AND rm.dEndBuyDate>'%s' AND rm.iMovieID=m.iMovieID 
				GROUP BY rm.iMovieID ORDER BY COUNT(rm.iMovieID) DESC"
				, $cinemas, GeneralFunc::getCurTime());
		return DbUtil::queryAll($sql);
	}

	/**
	 * 获取太和已匹配影片列表（By 城市id）
	 *
	 * @param string $cinemas 影院id串（形如：id1,id2,id3）
	 * @return array[][] 影片列表，二维数组
	 */
	public static function getMovieListByCity($cinemas)
	{
		if(empty($cinemas))
		{
			return array();
		}

		$sql = sprintf("SELECT m.iMovieID,m.sMovieName,m.sThMovieName
				FROM {{e_roommovie}} rm, (select bm.iMovieID,bm.sMovieName,om.sThMovieName from tb_b_movie bm,tb_out_movie om where bm.iMovieID=om.iMovieID) m
				WHERE rm.iEpiaoCinemaID IN (%s) AND rm.dEndBuyDate>'%s' AND rm.iMovieID=m.iMovieID
				GROUP BY rm.iMovieID ORDER BY COUNT(rm.iMovieID) DESC"
			, $cinemas, GeneralFunc::getCurTime());
		return DbUtil::queryAll($sql);
	}

	/**
	 * 通过e票网影片id获取和太和的影片匹配关系
	 * @param $iMovieiD
	 * @return array
	 */
	public static function getMovieMatchInfo($iMovieiD){
		if(empty($iMovieiD)){
			return array();
		}
		$sql = sprintf("select * from {{out_movie}} where iMovieID=%d",$iMovieiD);
		return DbUtil::queryRow($sql);
	}

	/**
	 * 获取影片信息（By 影片id）
	 *
	 * @param int $movieID 影片id
	 * @return array[] 影片信息
	 */
	public static function getMovieInfoByMovieID($movieID)
	{
		if(empty($movieID))
		{
			return array();
		}
		$sql = sprintf("SELECT sMovieName,dPlayTime,sDirector,sActor,iMovieScore,iRunTime,sImageUrl,sMovieInfo,iFavorMoiveID,sSmallImageUrl,mPrevueUrl
				FROM {{b_movie}} 
				WHERE iMovieID=%d", $movieID);
		return DbUtil::queryRow($sql);
	}

	public static function getMovieMatchInfoList($movieID){
		if(empty($movieID))
		{
			return array();
		}
		$sql = sprintf("select om.sThMovieName as sMovieName,bm.dPlayTime,bm.sDirector,bm.sActor,bm.iMovieScore,bm.iRunTime,bm.sImageUrl,bm.sMovieInfo,bm.iFavorMoiveID,bm.sSmallImageUrl,bm.mPrevueUrl from {{b_movie}} bm,{{out_movie}} om where bm.iMovieID=om.iMovieID and bm.iMovieID=%d",$movieID);
		return DbUtil::queryRow($sql);
	}

	//电影卡限制影片查询
	public static function  getCouponMovieByCouponIdAndiMovieId($iCouponID,$iMovieID)
	{
		$iCouponID = "'$iCouponID'";
		$iMovieID = "'$iMovieID'";
		$SQL = sprintf("select count(*) as count from {{e_couponmoviearea}} where iCouponID = $iCouponID");
		$result= DbUtil::queryRow($SQL);
		if($result['count']){
			$SQL = sprintf("select count(*) as count from {{e_couponmoviearea}} where iCouponID = $iCouponID and iMovieID = $iMovieID");
			$result= DbUtil::queryRow($SQL);
			if($result['count']){
				return  $result['count'];
			}else{
				return  0;
			}
		}else{
			return  1;
		}
	}


	public static function getCinemaInfoByName($sMovieName,$dPlayTime){
		$sql = sprintf("SELECT iMovieID,sMovieName
				FROM {{b_movie}}
				WHERE SUBSTRING(dPlayTime,1,10) >= '%s' and SUBSTRING(dPlayTime,1,10) <= '%s' and sMovieName LIKE '%%%s%%'", date('Y-m-d',strtotime("$dPlayTime -1 day")),date('Y-m-d',strtotime("$dPlayTime + 1 day")),$sMovieName);
		return DbUtil::queryRow($sql);
	}
}