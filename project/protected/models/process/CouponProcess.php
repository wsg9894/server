<?php

/**
 * CouponProcess - 电影卡操作类
 * @author luzhizhong
 * @version V1.0
 */


class CouponProcess
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
	 * 生成电影卡
	 *
	 * @param int $iCouponID 电影卡id
	 * @param int $iCouponCount 生成电影卡数量
	 * @param string $dEndTime 电影卡失效时间
	 * @param int $iUserID 绑定用户id
	 * @param int $mRealPrice 真实价格
	 * ...
	 * @return int 1-成功；其他-错误码
	 */
	public static function createCouponSalesInfo($iCouponID,$iCouponCount,$dEndTime,$iUserID,$mRealPrice,$batchNo='',$outerOrderId='',$iCompanyId='',$maxPrice = 0,$dBeginTime = "",$ihuodongID = 0)
	{
		if($dBeginTime == "")
		{
			$dBeginTime = date('Y-m-d H:i:s');
		}
		if($batchNo == "")
		{
			if($ihuodongID != 0)
			{
				$batchNo = "epiaowang".$ihuodongID."dyk".$iCouponID;
			}
		}
		$CouponCardInfo = self::initCouponSales(10,6,$ihuodongID);
		$CouponBaseInfo=array("iCouponID"=>$iCouponID,
				"iSalesQuantity"=>$iCouponCount,
				"dEndTime"=>$dEndTime,
				"iUserID"=>$iUserID,
				"MaxPrice"=>$maxPrice,
				"mRealPrice"=>$mRealPrice,
				"outerOrderId"=>$outerOrderId,
				"iUsedFlag"=>0,
				"iCouponStatus"=>1,
				"iCoCompanyID"=>$iCompanyId,
				"sBatchNo"=>$batchNo,
				"dBuyDate"=>$dBeginTime,
			    "dBeginTime"=>$dBeginTime,
		);
		$CouponInfo=array_merge($CouponCardInfo,$CouponBaseInfo);
		if(self::updateCoupon($CouponCardInfo['sCheckNo'], $CouponInfo))
		{
			return ErrorParse::getErrorNo('ok');
		}

		return ErrorParse::getErrorNo('unknown_err');
	}
	
	/**
	 * 获取电影卡售卖信息（By checkNo）
	 *
	 * @param string $sCheckNo 电影卡卡号
	 * @param array[] $fields 所要字段列表，一维数组，如果为空则获取全部
	 * @return array[] 电影卡信息
	 */
	public static function getCouponSalesInfoByCheckNo($sCheckNo, $fields=array())
	{
		$cpSalesDBObj = new E_CouponSalesDB();
		
		//过滤掉无效字段
		$fields = array_intersect($fields, $cpSalesDBObj->attributeNames());
		if(is_array($fields) and !empty($fields))
		{
			$selStr = '`'.implode('`,`', $fields).'`';
			$sql = 'SELECT '.$selStr.' FROM {{e_couponsales}} WHERE sCheckNo="'.$sCheckNo.'"';
		}else{
			$sql = 'SELECT * FROM {{e_couponsales}} WHERE sCheckNo="'.$sCheckNo.'"';
		}
		
		return DbUtil::queryRow($sql);
	}
	/**
	 * 初始化电影卡
	 *
	 * @param int $clen 卡号长度
	 * @param int $plen 密码长度
	 * ...
	 * @return array[] 电影卡信息
	 */
	public static function initCouponSales($clen,$plen,$huodong = 0)
	{
		$sCheckNo = self::randomKeys($clen,"n");
		$sPassword= self::randomKeys($plen, "n");
		
		$couponInfo = array("sCheckNo"=>$sCheckNo,"sPassWord"=>$sPassword);
		$arCouponInfo = self::getCouponSalesInfoByCheckNo($sCheckNo);
		if(empty($arCouponInfo))
		{
			if($huodong != 0){
				self::insertCouponSales($couponInfo);
			}else{
				self::insertCouponSalesInfo($couponInfo);
			}
		}

		return $couponInfo;
	}
	
	/**
	 * 录入电影卡售卖信息
	 *
	 * @param array[] $couponInfo 电影卡信息
	 * @return bool
	 */
	public static function insertCouponSalesInfo($couponInfo)
	{
		$cpSalesDBObj = new E_CouponSalesDB();
		reset($couponInfo);
		for($i=0; $i<count($couponInfo); $i++)
		{
			$cField = current($couponInfo);
			$key = key($couponInfo);
			$cpSalesDBObj->$key = $cField;
			next($couponInfo);
		}
		if($cpSalesDBObj->validate() and $cpSalesDBObj->save())
		{
			return ErrorParse::getErrorNo('ok');
		}
		return ErrorParse::getErrorNo('unknown_err');
	}

	/**
	 * 录入电影卡售卖信息
	 *
	 * @param array[] $couponInfo 电影卡信息
	 * @return bool
	 */
	public static function insertCouponSales($couponInfo)
	{
		$subSql = array();
		$csubSql=array();

		foreach($couponInfo as $key =>$v)
		{
			$subSql[] = "'".mysql_escape_string($v)."'";
			$csubSql[]=$key;
		}
		$SQL = sprintf("insert into  {{e_couponsales}} (%s,dCreateTime) VALUES(%s,now())",implode(',', $csubSql),implode(',', $subSql));
		return DbUtil::execute($SQL);
	}
	
	/**
	 * 修改电影卡售卖信息
	 *
	 * @param string $checkNo 电影卡卡号
	 * @param array[][] $couponInfo 电影卡售卖信息
	 * @return bool
	 */
	public static function updateCouponSalesInfo($checkNo, $couponInfo)
	{
		//去除无效字段项
		$couponInfo = self::filterInputFields($couponInfo, E_CouponSalesDB::model()->attributes);

		//修改拓展信息
		$cpSalesDBObj = new E_CouponSalesDB();
		
		$condition = 'sCheckNo=:sCheckNo';
		$params = array(':sCheckNo'=>$checkNo);
		$cpSalesDBObj->updateByCondition($couponInfo, $condition, $params);
		
		return TRUE;
	}

	/**
	 * 修改电影卡支付减点
	 *
	 * @param string $checkNo 电影卡卡号
	 * @param array[][] $couponInfo 电影卡售卖信息
	 * @return bool
	 */
	public static function updateCouponiUsedFlag($checkNo, $count)
	{
		$SQL = sprintf("update {{e_couponsales}}  set iUsedFlag = iUsedFlag + %d  where sCheckNo='%s'",$count,$checkNo);
		return DbUtil::execute($SQL);
	}

	/**
	 * 修改电影卡信息
	 *
	 * @param string $checkNo 电影卡卡号
	 * @param array[][] $couponInfo 电影卡售卖信息
	 * @return bool
	 */
	public static function updateCoupon($checkNo,$CouponInfo)
	{
		if(empty($checkNo) || count($CouponInfo) == 0)
		{
			return array();
		}
		foreach($CouponInfo as $key => $val){
			$subSql[] = "$key='".mysql_escape_string($val)."'";
		}
		$SQL = sprintf("update {{e_couponsales}}  set %s where sCheckNo = $checkNo",implode(',', $subSql));
		return DbUtil::execute($SQL);
	}

	/**
	 * 修改电影卡支付减点-订单失败
	 *
	 * @param string $checkNo 电影卡卡号
	 * @param array[][] $couponInfo 电影卡售卖信息
	 * @return bool
	 */
	public static function updateCouponiUsedunFlag($checkNo, $count)
	{
		$SQL = sprintf("update {{e_couponsales}}  set iUsedFlag = iUsedFlag - %d  where sCheckNo='%s'",$count,$checkNo);
		return DbUtil::execute($SQL);
	}

	public static function getBandCountByCouponId($iCouponID,$iUserID)
	{
		$SQL = sprintf("select sum(iSalesQuantity) as count from {{e_couponsales}} where iCouponID=%d and iUserID=%d", $iCouponID, $iUserID);
		$result = DbUtil::queryRow($SQL);
		if (empty($result["count"]))
		{
			return 0;
		}
		return  $result["count"];
	}

	public static function getCouponInfoCount($couponInfo)
	{
		foreach($couponInfo as $key =>$v)
		{
			if($key == 'dCreateTime'){
				$v = "$v%";
				$subSql[] = "$key like'".mysql_escape_string($v)."'";
			}else{
				$subSql[] = "$key='".mysql_escape_string($v)."'";
			}
		}
		if(count($subSql)!=0)
		{
			$SQL = sprintf("select count(sCheckNo) as sCount from {{e_couponsales}}  where  %s",implode(' and ', $subSql));
		}
		else {
			$SQL = sprintf("select count(sCheckNo) as sCount from {{e_couponsales}} ");
		}
		$result = DbUtil::queryRow($SQL);
		return  $result["sCount"];
	}

	public static function getCouponBaseInfo($CouponBaseInfo)
	{
		foreach($CouponBaseInfo as $key =>$v)
		{
			$subSql[] = "$key='".mysql_escape_string($v)."'";
		}
		$SQL = sprintf("select * from {{b_coupon}} where %s order by sCouponName ",implode(' and ', $subSql));
		return DbUtil::queryRow($SQL);

	}

	public static function bandingCouponLog($sCheckNo,$pass,$iUserID,$isbangding)
	{
		$SQL = sprintf("insert into  {{e_counponbandinglog}} (sCheckNo,sPassWord,iUserID,BandingJiebTime,iSbangding,LogCreatime) VALUES(%s,%s,%s,now(),%s,now())",$sCheckNo,$pass,$iUserID,$isbangding);
		return DbUtil::execute($SQL);//返回bool型的值 ture false
	}
	/**
	 * 生成随机码
	 *
	 * @param int $length 长度
	 * @param char $type 类型
	 * @return string 随机码
	 */
	private static function randomKeys($length,$type)
	{
		$pattern='1234567890ABCDEFGHIJKLMNPQRSTUVWXYZ';
		if($type=="n")
		{
			$bnum=0;
			$endnum=9;
		}
		if($type=="u")
		{
			$bnum=10;
			$endnum=34;
		}
		if($type=="m")
		{
			$bnum=0;
			$endnum=34;
		}
		$key="";
		for($i=0;$i<$length;$i++)
		{
			$key .= $pattern{mt_rand($bnum,$endnum)};    //生成php随机数
		}
		return $key;
	}
	/**
	 * 获取电影卡基础信息
	 *
	 * @param int $couponID 电影卡id
	 * @param array[] $fields 所要字段列表，一维数组，如果为空则获取全部
	 * @return array[] 电影卡基础信息，一维数组
	 */
	public static function getBaseCouponInfoByCouponID($couponID, $fields=array())
	{
		$cpDBObj = new B_CouponDB();
	
		//过滤掉无效字段
		$fields = array_intersect($fields, $cpDBObj->attributeNames());
		if(is_array($fields) and !empty($fields))
		{
			$selStr = '`'.implode('`,`', $fields).'`';
			$sql = 'SELECT '.$selStr.' FROM {{b_coupon}} WHERE iCouponID='.$couponID;
		}else{
			$sql = 'SELECT * FROM {{b_coupon}} WHERE iCouponID='.$couponID;
		}
	
		return DbUtil::queryRow($sql);
	}

	/**
	 * 待支付页面获取电影卡基础信息
	 *
	 * @param int $couponID 电影卡id
	 * @param array[] $fields 所要字段列表，一维数组，如果为空则获取全部
	 * @return array[] 电影卡基础信息，一维数组
	 */
	public static function getUserCouponForCinemaIdAndMovieId($userId,$maxPrice,$iCinemaId,$iMovieId,$baseCouponNum,$dimensional,$roomId, $fields=array())
	{
		$iVip = 0;
		$arRoomInfo = CinemaProcess::getRoomInfo($roomId);
		if (!empty($arRoomInfo))
		{
			$iVip = $arRoomInfo['isVip'];
		}
		$arCouponlist = self::getCouponInfoByiUserID($userId,$maxPrice);
		$arRet =array();

		foreach ($arCouponlist as $v)
		{
			if (strtotime($v['dEndTime']) -time()<0)
			{
				continue;
			}

			if ($v['iCouponStatus'] !=1)
			{
				continue;
			}

			$iCouponID=$v["iCouponID"];
			$couponBaseInfo=  self::getBaseCouponInfoByCouponID($iCouponID);
			if($couponBaseInfo["iIsForTicket"]=="1")
			{
				continue;
			}

			$iMaxusedCount=$couponBaseInfo["iMaxUsedCount"]; //规定的最大使用数量

			if ($iMaxusedCount > 0)
			{
				$iUsedCouponCount=  self::getUsedCouponInfoByiUserID($iCouponID, $userId);

				if($iUsedCouponCount>=$iMaxusedCount)
				{
					continue;
				}
			}


			$arCouponCinema= CinemaProcess::getCouponCinemaInfo($iCouponID,$iCinemaId);
			if(empty($arCouponCinema))
			{
				continue;
			}
			$iCurCount = $v["iSalesQuantity"]-$v["iUsedFlag"];
			$couponPay = 1;
			if ($arCouponCinema['iChange2D'] == 0 && $arCouponCinema['iChange3D'] == 0 && $arCouponCinema['iChangeIMax'] == 0 && $arCouponCinema['iChangeVip'] == 0)    //热点卡
			{
				if ($iCurCount <$baseCouponNum)
				{
					continue;
				}
				$couponPay = $baseCouponNum;
			}
			else {
				if ($iVip == 1)
				{
					$couponPay=$arCouponCinema["iChangeVip"];
				}
				else
				{
					switch ($dimensional)
					{
						case "2D":
						{
							$couponPay=$arCouponCinema["iChange2D"];
							break;
						}
						case "3D":
						{
							$couponPay=$arCouponCinema["iChange3D"];
							break;
						}
						case "IMAX":
						{
							$couponPay=$arCouponCinema["iChangeIMax"];
							break;
						}
						default:
						{
							if($maxPrice>80){
								$couponPay=2;
							}
						}
						break;
					}
				}


				if ($couponPay<=0)
				{
					continue;
				}
				if ($iCurCount<$couponPay)
				{
					continue;
				}
			}



			if (MovieProcess::getCouponMovieByCouponIdAndiMovieId($iCouponID,$iMovieId))
			{
				$v['isLock'] = false;
				$v['validcount'] = $v["iSalesQuantity"]-$v["iUsedFlag"];
				$v['couponpay'] = $couponPay;
				$arRet[] = $v;
			}
		}
		return $arRet;
	}

	//根据iUserID获取电影卡
	public static function  getCouponInfoByiUserID($iUserID,$maxPrice = null)
	{
		if(!empty($maxPrice))
		{
			$couponInfo=array("iUserID"=>$iUserID,"MaxPrice" =>$maxPrice);
		}
		else{
			$couponInfo=array("iUserID"=>$iUserID);
		}
		foreach($couponInfo as $key =>$v)
		{
			if($key == "MaxPrice")
			{
				$key = "a.".$key;
				if($v != 0)
				{
					$subSql[] = "($key>='".mysql_escape_string($v)."'"." or "."$key='0')";
				}
			}
			else{
				$subSql[] = "$key='".mysql_escape_string($v)."'";
			}
		}
		$SQL = sprintf("select * from {{e_couponsales}}  a,{{b_coupon}} b  where %s and a.iCouponID=b.iCouponID  and  iCouponStatus=1 order by dEndTime,sCheckNo desc",implode(' and ', $subSql));
		return DbUtil::queryAll($SQL);
	}
    //查看电影卡的使用情况
	public static function getUsedCouponInfoByiUserID($iCouponID,$iUserID)
	{
		$SQL = sprintf("select sum(cardCount) as count from {{l_paylog}} where iCouponID=%d and iUserID=%d and status>='%s' and status<'%s'",$iCouponID, $iUserID,ConfigParse::getPayStatusKey('orderPay'), ConfigParse::getPayStatusKey('orderEnd'));
		$result = DbUtil::queryRow($SQL);
		if (empty($result["count"]))
		{
			return 0;
		}
		return  $result["count"];
	}

	public static function getcouponmoviearea($iCouponID)
	{
		$sql = 'SELECT iMovieID FROM {{e_couponmoviearea}} WHERE iCouponID='.$iCouponID;

		return DbUtil::queryAll($sql);
	}

	public static function getCouponSalesByBatch($sBatchNo,$fields)
	{
		$cpDBObj = new E_CouponSalesDB();

		//过滤掉无效字段
		$fields = array_intersect($fields, $cpDBObj->attributeNames());
		if(is_array($fields) and !empty($fields))
		{
			$selStr = '`'.implode('`,`', $fields).'`';
			$sql = 'SELECT '.$selStr.' FROM {{e_couponsales}} WHERE sBatchNo='.$sBatchNo;
		}else{
			$sql = 'SELECT * FROM {{e_couponsales}} WHERE sBatchNo='.$sBatchNo;
		}
		return DbUtil::queryAll($sql);
	}

	public static function getCouponSalesByUserID($userID,$iCouponID,$fields)
	{
		$cpDBObj = new E_CouponSalesDB();

		//过滤掉无效字段
		$fields = array_intersect($fields, $cpDBObj->attributeNames());
		if(is_array($fields) and !empty($fields))
		{
			$selStr = '`'.implode('`,`', $fields).'`';
			$sql = 'SELECT '.$selStr.' FROM {{e_couponsales}} WHERE iCouponID='.$iCouponID.' and iUserID='.$userID;
		}else{
			$sql = 'SELECT * FROM {{e_couponsales}} WHERE iCouponID='.$iCouponID.' and iUserID='.$userID;
		}
		return DbUtil::queryAll($sql);
	}


	/**
	 * 电影卡绑定操作（用于下游合作方【口粮】）
	 *
	 * @param string $checkNo 卡号
	 * @param string $passWord 密码
	 * @param int $partnerID 口粮号（用户ID，唯一标识）
	 * @param string $phone 用户手机号
	 * @return int 1-成功；其他-错误码
	 */
	public static function partnerBinding_KouL($checkNo, $passWord, $partnerID, $phone)
	{
		
		return 1;
	}
}