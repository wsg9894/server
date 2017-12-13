<?php

/**
 * HuodongProcess - 活动操作类
 * @author anqing
 * @version V1.0
 */


class HuodongProcess
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
	 * 获取活动列表
	 */
	public static function getHuodongList()
	{
		$sql = sprintf("select * from {{b_huodong}} as a,{{b_huodongbanner}} as b where isWeChatapplet=1 and a.iHuoDongID=b.iHuoDongID and state=0  order by dHuoDongEndDate desc");
		return DbUtil::queryAll($sql);
	}

	/**
	 * 获取用户活动记录
	 */
	public static function get_user_Huodongrecord($iUserID,$iHuoDongID)
	{
		if(empty($iUserID) || empty($iHuoDongID))
		{
			return array();
		}

		$sql = sprintf("select * from {{e_huodong}} where iHuoDongID=$iHuoDongID and iUserID=$iUserID");
		return DbUtil::queryRow($sql);
	}

	/**
	 * 获取活动奖品
	 * iHuodongItemIndex  奖品等级
	 */
	public static function get_user_HuodongItem($iHuoDongID,$all="")
	{
		if(empty($iHuoDongID))
		{
			return array();
		}
		if($all == "all"){
			$sql = sprintf("select * from {{b_huodongitem}} where iHuoDongID=$iHuoDongID");
			return DbUtil::queryAll($sql);
		}
		$sql = sprintf("select * from {{b_huodongitem}} where iHuoDongID=$iHuoDongID and IRemainedCount>=iGetCount");
		return DbUtil::queryAll($sql);
	}

	/**
	 * 更新奖品信息
	 */
	public static function upHuodongItem($iHuodongItemId,$iHuodongItemInfo)
	{
		if(empty($iHuodongItemId) || count($iHuodongItemInfo) == 0)
		{
			return array();
		}
		foreach($iHuodongItemInfo as $key => $val){
			$subSql[] = "$key='".mysql_escape_string($val)."'";
		}

		$sql = sprintf("update {{b_huodongitem}} set %s where iHuodongItemID = $iHuodongItemId",implode(',', $subSql));
		return DbUtil::execute($sql);
	}

	/**
	 * 更新用户活动记录
	 */
	public static function up_user_Huodongrecord($iUserID,$iHuoDongID,$iGetCount)
	{
		if(empty($iUserID) || empty($iHuoDongID))
		{
			return array();
		}

		$sql = sprintf("update {{e_huodong}} set flag = 1,iSalesQuantity=$iGetCount  where iHuoDongID=$iHuoDongID and iUserID=$iUserID");
		return DbUtil::execute($sql);
	}

	/**
	 * 添加用户活动记录
	 */
	public static function add_user_Huodongrecord($iUserID,$iHuoDongID,$iHuodongItemID,$outerOrderID,$fromClint,$iCouponID,$iVoucherID,$iCount,$third_id,$MovieID)
	{
		$sql = sprintf("INSERT into {{e_huodong}}
						(iUserID,iHuoDongID,iHuodongItemID,outerOrderId,fromClient,sCreateUser,flag,iCouponID,iVoucherID,dCreateTime,iSalesQuantity,third_id,iMovieID)
						 VALUES
						(%s,%s,%s,'%s','%s','epw',0,%s,%s,now(),%s,%s,%s)", $iUserID, $iHuoDongID, $iHuodongItemID, $outerOrderID, $fromClint, $iCouponID, $iVoucherID,$iCount,$third_id,$MovieID);
		return DbUtil::execute($sql);
	}

	/*
	 * 活动绑定记录
	 * */
	public static function HuodongBand($huodongItem,$iUserID,$iHuoDongID){
		//为该用户绑定该活动奖品
		//判断每日发放上线
		$iPerDayCount = $huodongItem['iPerDayCount'];
		//判断奖品是现金券还是电影卡
		if($huodongItem['iCouponID'] != 0){
			//电影卡
			if($iPerDayCount != 0){
				//获取今日发放电影卡的总数
				$count = CouponProcess::getCouponInfoCount(array('dCreateTime'=>date("Y-m-d",time())));
				if($iPerDayCount<=$count){
					return array("nErrCode"=>-1504,"nDescription"=>"已达今日上限");
				}
			}
			if($huodongItem['iHuoDongItemTypeID'] == 1){ // 免费
				$CouponBase = CouponProcess::getBaseCouponInfoByCouponID($huodongItem['iCouponID'],array("mSalePrice","maxPrice","sCouponName"));
				//绑定电影卡
				$batchNo = sprintf(Yii::app()->params['batchInfo']['scoreStore']['wechat_favorable'], 'dyk', $huodongItem['iCouponID']);
				$ret=CouponProcess::createCouponSalesInfo($huodongItem['iCouponID'],$huodongItem['iGetCount'],$huodongItem['dEndTime'],$iUserID,$CouponBase['mSalePrice'],$batchNo,"","",$CouponBase['maxPrice'],"",$iHuoDongID);
				if($ret == 1){
					//减库存
					$couponsales = CouponProcess::getCouponSalesByUserID($iUserID,$huodongItem['iCouponID'],array('sCheckNo','sPassWord'));
					foreach($couponsales as &$v){
						$v['sVoucherName'] = $CouponBase['sCouponName'];
					}
					$IRemainedCount = $huodongItem['IRemainedCount'] - $huodongItem['iGetCount'];
					self::upHuodongItem($huodongItem['iHuodongItemID'],array('IRemainedCount'=>$IRemainedCount));
					return array("nErrCode"=>0,'nResult'=>$couponsales);
				}
			}elseif($huodongItem['iHuoDongItemTypeID'] == 2){ //低价优惠

			}elseif($huodongItem['iHuoDongItemTypeID'] == 3){  //在线选座资格

			}
		}
		if($huodongItem['iVoucherID'] != 0){
			//现金券
			if($iPerDayCount != 0){
				//获取今日发放现金券的总数
				$count = VoucherProcess::getVoucherCount(array('dCreateTime'=>date("Y-m-d",time())));
				if($iPerDayCount<=$count){
					return array("nErrCode"=>-1504,"nDescription"=>"已达今日上限");
				}
			}
			if($huodongItem['iHuoDongItemTypeID'] == 1){ // 免费
				//绑定现金券
				$batchNo=sprintf(Yii::app()->params['batchInfo']['scoreStore']['wechat_favorable'], 'xjq', $huodongItem['iVoucherID']);
				for($i=0;$i<$huodongItem['iGetCount'];$i++){
					$ret[] = VoucherProcess::createVoucher($huodongItem['iVoucherID'],$iUserID,$batchNo,$iHuoDongID,$huodongItem['dEndTime']);
				}
				if(count($ret) != 0){
					//减库存
					$IRemainedCount = $huodongItem['IRemainedCount'] - $huodongItem['iGetCount'];
					self::upHuodongItem($huodongItem['iHuodongItemID'],array('IRemainedCount'=>$IRemainedCount));
					return array("nErrCode"=>0,'nResult'=>$ret);
				}
			}
		}
	}
}