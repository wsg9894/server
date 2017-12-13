<?php

/**
 * VoucherProcess - 现金券操作类
 * @author luzhizhong
 * @version V1.0
 */


class VoucherProcess
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
	 * 获取现金券基础信息（By voucherID）
	 *
	 * @param int $voucherID 现金券id
	 * @param array[] $fields 所要字段列表，一维数组，如果为空则获取全部
	 * @return array[] 现金券信息
	 */
	public static function getVoucherBaseInfoByVoucherID($voucherID, $fields=array())
	{
		$voucherDBObj = new B_VoucherDB();
	
		//过滤掉无效字段
		$fields = array_intersect($fields, $voucherDBObj->attributeNames());
		if(is_array($fields) and !empty($fields))
		{
			$selStr = '`'.implode('`,`', $fields).'`';
			$sql = 'SELECT '.$selStr.' FROM {{b_voucher}} WHERE iVoucherID='.$voucherID;
		}else{
			$sql = 'SELECT * FROM {{b_voucher}} WHERE iVoucherID='.$voucherID;
		}
	
		return DbUtil::queryRow($sql);
	}

	/**
	 * 获取用户现金券基础信息（By iUserID）
	 *
	 * @param int iUserID 用户id
	 * @param array[] $fields 所要字段列表，一维数组，如果为空则获取全部
	 * @return array[] 现金券信息
	 */
	public static function getVoucherBaseInfoByiUserID($iUserID, $fields=array())
	{
		$voucherDBObj = new E_VoucherDB();

		//过滤掉无效字段
		$fields = array_intersect($fields, $voucherDBObj->attributeNames());
		if(is_array($fields) and !empty($fields))
		{
			$selStr = '`'.implode('`,`', $fields).'`';
			$sql = 'SELECT '.$selStr.' FROM {{e_voucher}} WHERE iUserID='.$iUserID;
		}else{
			$sql = 'SELECT * FROM {{e_voucher}} WHERE iUserID='.$iUserID;
		}

		return DbUtil::queryAll($sql);
	}
	/**
	 * 获取用户现金券基础信息（By sVoucherPassWord）
	 *
	 * @param int iUserID 用户id
	 * @param array[] $fields 所要字段列表，一维数组，如果为空则获取全部
	 * @return array[] 现金券信息
	 */
	public static function getVoucherBaseInfoBysVoucherPassWord($sVoucherPassWord, $fields=array())
	{
		$voucherDBObj = new E_VoucherDB();

		//过滤掉无效字段
		$fields = array_intersect($fields, $voucherDBObj->attributeNames());

		if(is_array($fields) and !empty($fields))
		{
			$selStr = '`'.implode('`,`', $fields).'`';
			$sql = 'SELECT '.$selStr.' FROM {{e_voucher}} WHERE sVoucherPassWord='."'$sVoucherPassWord'";
		}else{
			$sql = 'SELECT * FROM {{e_voucher}} WHERE sVoucherPassWord='."'$sVoucherPassWord'";
		}
		return DbUtil::queryRow($sql);
	}

	public static function getvouchermoviearea($iVoucherID)
	{
		$sql = 'SELECT iMovieID FROM {{e_vouchermoviearea}} WHERE iVoucherId='.$iVoucherID;

		return DbUtil::queryAll($sql);
	}
	/**
	 * 生成娱乐券
	 *
	 * @param int $thirdID 娱乐券id
	 * @param int $uid 绑定用户id
	 * @return int 1-成功；其他-错误码
	 */
	public static function updateThirdInfo($thirdID, $uid)
	{
		$userInfo = UserProcess::getUInfo($uid, array('sPhone'));
		if(empty($userInfo))
		{
			return ErrorParse::getErrorNo('user_not_exist');
		}
		
		$sql = sprintf("UPDATE {{b_huodongitem_third_code}} set iUserID=%d,dBuyDate='%s',flag=1,sendPhone='%s' WHERE third_id=%s AND flag!=1 LIMIT 1", $uid, GeneralFunc::getCurTime(), $userInfo['sPhone'], $thirdID);
		DbUtil::execute($sql);
		
		//清除缓存
		file_get_contents(Yii::app()->params['baseUrl'].'tasks/delUserRedis.php?type=third&uid='.$uid);
		
		return ErrorParse::getErrorNo('ok');
		
	}
	/**
	 * 生成现金券
	 *
	 * @param int $voucherId 现金券id
	 * @param int $uid 绑定用户id
	 * @param string $batchNo 批号
	 * @return array[] 现金券信息-成功；其他-错误码
	 */
	public static function createVoucher($voucherId, $uid=0, $batchNo='',$iHuoDongID=0,$dEndTime='')
	{
		$vcBaseInfo = self::getVoucherBaseInfoByVoucherID($voucherId);
		if(empty($vcBaseInfo))
		{
			return ErrorParse::getErrorNo('param_error');
		}
		
		$sVoucherPassword = self::randomkeys(12, "m");
		
		$voucherInfo = array();
		try {
			if (false == self::isExistPassword($sVoucherPassword))
			{
				$voucherInfo["sVoucherPassWord"] = $sVoucherPassword;
				$voucherInfo["iVoucherID"] = $voucherId;
				$voucherInfo["iCompanyID"] = $vcBaseInfo['iCompanyID'];
				$voucherInfo["sCreateUser"] = $vcBaseInfo['sCreateUser'];
				$voucherInfo["dVaildBeginTime"] = GeneralFunc::getCurTime();
				$activeTimeStr = empty($vcBaseInfo['dActiveTime']) ? '+30 day' : '+'.(30*$vcBaseInfo['dActiveTime']).' day';
				$voucherInfo["dVaildEndTime"] = date("Y-m-d H:i:s",strtotime($activeTimeStr));
				$voucherInfo["sVoucherName"] = $vcBaseInfo['sVoucherName'];
				$voucherInfo["mVoucherMoney"] = $vcBaseInfo['mVoucherMoney'];
				$voucherInfo["iVoucherCount"] = $vcBaseInfo['iVoucherCount'];
				$voucherInfo["iUserID"] = $uid;
				$voucherInfo['iVoucherTypeID'] = $vcBaseInfo['iVoucherTypeID'];
				$voucherInfo['sBatchNo'] = $batchNo;
				$voucherInfo['iVoucherStatusID'] = UserProcess::isRegByUid($uid) ? 2 : 1; //1-未使用；2-已绑定；3-已使用；4-已失效

				if($dEndTime!=""){
					$voucherInfo["dVaildEndTime"] = $dEndTime;
				}
				if($iHuoDongID!=0){
					$ret = self::insertVouponInfo($voucherInfo);
				}else{
					$ret = self::insertEVoucherInfo($voucherInfo);
				}
				
				//删除缓存
				file_get_contents(Yii::app()->params['baseUrl'].'tasks/delUserRedis.php?type=voucher&uid='.$uid);
				
				if (1!=$ret) 
				{
					return $ret;
				}
			}
		} 
		catch (Exception $e) 
		{
			return ErrorParse::getErrorNo('unknown_err');
		}
		return $voucherInfo;
	}
	/**
	 * 判断现金券密码是否存在
	 *
	 * @param string $wcPassword 现金券密码
	 * @return bool
	 */
	public static function isExistPassword($wcPassword)
	{
		$voucherDBObj = new E_VoucherDB();
		$sql = sprintf('SELECT COUNT(sVoucherPassWord) FROM {{e_voucher}} WHERE sVoucherPassWord="%s"', $wcPassword);
		
		$vcCount = $voucherDBObj->getCountBySql($sql);
		return $vcCount>0;
		
	}

	/**
	 * 获取现金券总张数
	 *
	 * @param string $wcPassword 现金券密码
	 * @return bool
	 */
	public static function getVoucherCount($voucherInfo)
	{
		foreach($voucherInfo as $key =>$v)
		{
			if($key == 'dCreateTime'){
				$v = "$v%";
				$subSql[] = "$key like'".mysql_escape_string($v)."'";
			}else{
				$subSql[] = "$key='".mysql_escape_string($v)."'";
			}
		}
		$sql = sprintf('SELECT COUNT(sVoucherPassWord) as sCount FROM {{e_voucher}} WHERE  %s ',implode(' and ', $subSql));
		$result = DbUtil::queryRow($sql);
		return  $result["sCount"];

	}

	/**
	 * 录入现金券售卖信息
	 *
	 * @param array[] $voucherInfo 现金券信息
	 * @return bool
	 */
	public static function insertEVoucherInfo($voucherInfo)
	{
		$voucherDBObj = new E_VoucherDB();
		reset($voucherInfo);
		for($i=0; $i<count($voucherInfo); $i++)
		{
			$vField = current($voucherInfo);
			$key = key($voucherInfo);
			$voucherDBObj->$key = $vField;
			next($voucherInfo);
		}
	
		if($voucherDBObj->validate() and $voucherDBObj->save())
		{
			return ErrorParse::getErrorNo('ok');
		}
	
		return ErrorParse::getErrorNo('unknown_err');
	}

	/**
	 * 录入现金券售卖信息
	 *
	 * @param array[] $vouponInfo 现金券信息
	 * @return bool
	 */
	public static function insertVouponInfo($vouponInfo)
	{
		$subSql = array();
		$csubSql=array();

		foreach($vouponInfo as $key =>$v)
		{
			$subSql[] = "'".mysql_escape_string($v)."'";
			$csubSql[]=$key;
		}
		$SQL = sprintf("insert into  {{e_voucher}} (%s,dCreateTime) VALUES(%s,now())",implode(',', $csubSql),implode(',', $subSql));
		return DbUtil::execute($SQL);
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
	 * 修改现金券售卖信息
	 *
	 * @param string $sVoucherPassWord 现金券券号
	 * @param array[][] $updateVoucher 现金券售卖信息
	 * @return bool
	 */
	public static function updateVoucherInfo($sVoucherPassWord, $updateVoucher)
	{
		//去除无效字段项
		$couponInfo = self::filterInputFields($updateVoucher, E_VoucherDB::model()->attributes);

		//修改拓展信息
		$cpSalesDBObj = new E_VoucherDB();

		$condition = 'sVoucherPassWord=:sVoucherPassWord';
		$params = array(':sVoucherPassWord'=>$sVoucherPassWord);
		$cpSalesDBObj->updateByCondition($updateVoucher, $condition, $params);

		return TRUE;
	}

	//在线选座筛选现金券
	public static function getUserVoucherListForBookSeat($userId,$price,$count,$iMovieId)
	{
		$arVoucherList = self::getVoucherBaseInfoByiUserID($userId);
		$ret = array();
		foreach ($arVoucherList as $v)
		{
			if ($v['iVoucherStatusID'] != 2)
			{
				continue;
			}

			if ($v['dVaildEndTime'] <date('Y-m-d H:m:s'))
			{
				continue;
			}

			$vouchermovie = self::getvouchermoviearea($v['iVoucherID']);

			if ($vouchermovie)
			{
				$i = 0;
				foreach($vouchermovie as $k => $val){
					if($val['iMovieID'] == $iMovieId){
						break;
					}
					$i++;
				}
				if($i == count($vouchermovie)){
					continue;
				}
			}
			$arVoucherBaseInfo = self::getVoucherBaseInfoByVoucherID($v['iVoucherID']);
			if ($price>$arVoucherBaseInfo['iVoucherUseMoney'] && $count>=$arVoucherBaseInfo['iVoucherUseCount'])
			{
				$v['isLock'] = false;
				$v['iVoucherUseCount'] = $arVoucherBaseInfo['iVoucherUseCount'];
				$v['dVaildEndTime'] = date("Y-m-d",strtotime($v['dVaildEndTime']));
				$v['mVoucherMoney'] = explode('.',$v['mVoucherMoney'])[0];
				$ret[] = $v;
			}
		}

		return $ret;
	}

	/**
	 * 修改现金券状态-订单失败
	 */
	public static function updateVoucheriUsedunFlag($sVoucherPassWord)
	{
		$SQL = sprintf("update {{e_voucher}}  set iVoucherStatusID = 2  where sVoucherPassWord='%s'",$sVoucherPassWord);
		return DbUtil::execute($SQL);
	}

	/**
	 * 修改现金券状态
	 */
	public static function updateVoucheriUsedFlag($sVoucherPassWord)
	{
		$SQL = sprintf("update {{e_voucher}}  set iVoucherStatusID = 3  where sVoucherPassWord='%s'",$sVoucherPassWord);
		return DbUtil::execute($SQL);
	}

	public static function getVoucherSalesByBatch($sBatchNo,$fields)
	{
		$cpDBObj = new E_VoucherDB();

		//过滤掉无效字段
		$fields = array_intersect($fields, $cpDBObj->attributeNames());
		if(is_array($fields) and !empty($fields))
		{
			$selStr = '`'.implode('`,`', $fields).'`';
			$sql = 'SELECT '.$selStr.' FROM {{e_voucher}} WHERE sBatchNo='.$sBatchNo;
		}else{
			$sql = 'SELECT * FROM {{e_voucher}} WHERE sBatchNo='.$sBatchNo;
		}
		return DbUtil::queryAll($sql);
	}

	public static function getVoucherSalesByUserID($userID,$iVoucherID,$fields)
	{
		$cpDBObj = new E_VoucherDB();

		//过滤掉无效字段
		$fields = array_intersect($fields, $cpDBObj->attributeNames());
		if(is_array($fields) and !empty($fields))
		{
			$selStr = '`'.implode('`,`', $fields).'`';
			$sql = 'SELECT '.$selStr.' FROM {{e_voucher}} WHERE iUserID='.$userID.' and iVoucherID='.$iVoucherID;
		}else{
			$sql = 'SELECT * FROM {{e_voucher}} WHERE iUserID='.$userID.' and iVoucherID='.$iVoucherID;
		}
		return DbUtil::queryAll($sql);
	}
}