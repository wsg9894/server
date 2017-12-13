<?php

/**
 * PrizeProcess - 奖品操作类
 * @author luzhizhong
 * @version V1.0
 */


class PrizeProcess
{
	function __construct()
	{
	}
	function __destruct()
	{
	}
	/**
	 * 添加奖品日志
	 *
	 * @param int $uid 用户id
	 * @param int $postersID 海报id
	 * @param string $voucherIDS 奖品id串（现金券id）
	 * @param int $prizeType 奖品类型（1-邀请好友所得；2-接受邀请所得）
	 * @return int 添加成功：1；添加失败：错误码
	 */
	public static function addPrizeLog($uid, $postersID, $voucherIDS, $prizeType=1)
	{
		try
		{
			$pLogDBObj = new S_PrizeLogDB();
			
			$pLogDBObj->uid = $uid;
			$pLogDBObj->posters_id = $postersID;
			$pLogDBObj->voucher_ids = $voucherIDS;
			$pLogDBObj->prize_type = $prizeType;
			
			$pLogDBObj->save();
		}
		catch(Exception $e)
		{
			return ErrorParse::getErrorNo('process_exception');
		}
		
		return ErrorParse::getErrorNo('ok');
	}

	/**
	 * 获取奖品日志信息（单条）
	 *
	 * @param array[] $fields 所要字段列表，一维数组，如果为空则获取全部
	 * @param string $filter where添加
	 * @return array[] 奖品日志信息，一维数组
	 */
	public static function getPrizeLogInfo($fields=array(), $filter='')
	{
		$pLogDBObj = new S_PrizeLogDB();
	
		//过滤掉无效字段
		$fields = array_intersect($fields, $pLogDBObj->attributeNames());
		if(is_array($fields) and !empty($fields))
		{
			$selStr = '`'.implode('`,`', $fields).'`';
		}else{
			$selStr = '*';
		}
	
		$whereStr = empty($filter) ? 'WHERE 1' : 'WHERE 1 AND '.$filter;
		$sql = 'SELECT '.$selStr.' FROM {{s_prizelog}} '.$whereStr;
	
		$logInfo = DbUtil::queryRow($sql);
		return $logInfo;
	}
	
	/**
	 * 判断是否发过奖品
	 *
	 * @param int $uid 用户id
	 * @param int $postersID 海报id
	 * @param int $prizeType 奖品类型（1-邀请好友所得；2-接受邀请所得）
	 * @return bool
	 */
	public static function getSendPrizeFlag($uid, $postersID, $prizeType=1)
	{
		$fields = array('lid');
		$filter = sprintf('uid=%d AND posters_id=%d AND prize_type=%d', $uid, $postersID, $prizeType);
		$logInfo = self::getPrizeLogInfo($fields, $filter);
		
		return empty($logInfo) ? FALSE : TRUE;
	}
	
	/**
	 * 通过现金券id串，获取现金券总金额
	 *
	 * @param string $voucherIDs 现金券id串（形如：166,159）
	 * @return int 现金券总金额
	 */
	public static function getPrizeTotalAmountByVoucherIDs($voucherIDs)
	{
		$sql = sprintf('SELECT SUM(mVoucherMoney) AS amount FROM {{b_voucher}} WHERE iVoucherID IN(%s)', $voucherIDs);
		$voucherInfo = DbUtil::queryRow($sql);
		
		return empty($voucherInfo['amount']) ? 0 : round($voucherInfo['amount']);
	}

	/**
	 * 通过现金券id串，获取现金券列表
	 *
	 * @param string $voucherIDs 现金券id串（形如：166,159）
	 * @return int 现金券总金额
	 */
	public static function getPrizeListByVoucherIDs($voucherIDs)
	{
		$sql = sprintf('SELECT iVoucherID,sVoucherName,ROUND(mVoucherMoney) AS mVoucherMoney FROM {{b_voucher}} WHERE iVoucherID IN(%s)', $voucherIDs);
		$voucherList = DbUtil::queryAll($sql);
	
		return $voucherList;
	}
	
}