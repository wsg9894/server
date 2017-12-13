<?php

/**
 * GOrderProcess - 商品订单操作类
 * @author luzhizhong
 * @version V1.0
 */


class GOrderProcess
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
	private static function filterInputFields($inputFields,$defFields)
	{
		return array_intersect_key($inputFields,$defFields);
	}
	
	/**
	 * 创建订单号
	 *
	 * @return string 订单号
	 */
	private static function createOrderNo()
	{
		$charid = strtoupper(substr(md5(uniqid(mt_rand(), true)),8,24));
		$hyphen = '';
		$uuid = substr($charid, 0, 8).substr($charid, 8, 4).$hyphen.substr($charid,12, 4).$hyphen.substr($charid,16, 4).$hyphen.substr($charid,20,12);
		return 'SS-'.$uuid;
	}
	
	/**
	 * 生成订单
	 *
	 * @param array[] $orderInfo 收货地址信息
	 * @return int 订单号；其他-错误码
	 */
	public static function createOrder($orderInfo)
	{
		//过滤无效字段（将数据表中未定义的字段去除）
		$orderInfo = self::filterInputFields($orderInfo,S_GOrderDB::model()->attributes);
		$orederNo = self::createOrderNo();
		try
		{
			$orderDBObj = new S_GOrderDB();
				
			reset($orderInfo);
			for($i=0; $i<count($orderInfo); $i++)
			{
				$cField = current($orderInfo);
				$key = key($orderInfo);
				$orderDBObj->$key = $cField;
				next($orderInfo);
			}
			$orderDBObj->order_no = $orederNo;
	
			if($orderDBObj->validate() and $orderDBObj->save())
			{
				return $orederNo;
			}
		}
		catch(Exception $e)
		{
		}
	
		return ErrorParse::getErrorNo('unknown_err');
	}
	
	/**
	 * 获取订单信息（By orderNo）
	 *
	 * @param string $orderNo 订单号
	 * @param array[] $fields 所要字段列表，一维数组，如果为空则获取全部
	 * @return array[] 商品信息，一维数组
	 */
	public static function getOrderInfoByOrderno($orderNo, $fields=array())
	{
		$orderInfo = array();
		if(empty($orderNo))
		{
			return $orderInfo;
		}
		$orderDBObj = new S_GOrderDB();
	
		//过滤掉无效字段
		$fields = array_intersect($fields, $orderDBObj->attributeNames());
		if(is_array($fields) and !empty($fields))
		{
			$selStr = '`'.implode('`,`', $fields).'`';
			$sql = "SELECT ".$selStr." FROM {{s_order}} WHERE order_no='".$orderNo."'";
		}else{
			$sql = "SELECT * FROM {{s_order}} WHERE order_no='".$orderNo."'";
		}
	
		$orderInfo = DbUtil::queryRow($sql);
		return $orderInfo;
	}
	
	/**
	 * 获取订单信息（By oid）
	 *
	 * @param string $orderNo 订单号
	 * @param array[] $fields 所要字段列表，一维数组，如果为空则获取全部
	 * @return array[] 商品信息，一维数组
	 */
	public static function getOrderInfoByOid($oid, $fields=array())
	{
		$orderInfo = array();
		if(empty($oid))
		{
			return $orderInfo;
		}
		$orderDBObj = new S_GOrderDB();
	
		//过滤掉无效字段
		$fields = array_intersect($fields, $orderDBObj->attributeNames());
		if(is_array($fields) and !empty($fields))
		{
			$selStr = '`'.implode('`,`', $fields).'`';
			$sql = "SELECT ".$selStr." FROM {{s_order}} WHERE oid=".$oid;
		}else{
			$sql = "SELECT * FROM {{s_order}} WHERE oid=".$oid;
		}

		$orderInfo = DbUtil::queryRow($sql);
		return $orderInfo;
	}

	/**
	 * 修改订单状态
	 *
	 * @param int $oid 订单id
	 * @param int $status 订单状态
	 * @return int 操作成功－1，其他－错误码
	 */
	public static function updateOrderStatusByOid($oid, $status=1)
	{
		$orderDBObj = new S_GOrderDB();
	
		$updateOrderInfo = array('order_status'=>$status);
		$condition = 'oid=:oid';
		$params = array(':oid'=>$oid);
		$orderDBObj->updateByCondition($updateOrderInfo, $condition, $params);
	
		return ErrorParse::getErrorNo('ok');
	}
	
	/**
	 * 修改订单状态
	 *
	 * @param string $orderNo 订单no
	 * @param int $status 订单状态
	 * @return int 操作成功－1，其他－错误码
	 */
	public static function updateOrderStatusByOrderno($orderNo, $status=1)
	{
		$orderDBObj = new S_GOrderDB();
	
		$updateOrderInfo = array('order_status'=>$status);
		$condition = 'order_no=:order_no';
		$params = array(':order_no'=>$orderNo);
		$orderDBObj->updateByCondition($updateOrderInfo, $condition, $params);
	
		return ErrorParse::getErrorNo('ok');
	}
	
	/**
	 * 修改订单信息（By 订单编号）
	 *
	 * @param string $orderNo 订单号
	 * @param array[] $updateOrderInfo 待修改的订单信息
	 * @return int 操作成功－1，其他－错误码
	 */
	public static function updateOrderInfoByOrderno($orderNo, $updateOrderInfo)
	{
		$orderDBObj = new S_GOrderDB();
	
		//过滤掉无效字段（目前只开放‘订单状态’、‘第三方订单号’、‘快递单号’三个字段）
		$updateOrderInfo = self::filterInputFields($updateOrderInfo, array('order_status'=>0, 'trade_no'=>'', 'express_no'=>''));
	
		$condition = 'order_no=:order_no';
		$params = array(':order_no'=>$orderNo);
		$orderDBObj->updateByCondition($updateOrderInfo, $condition, $params);
	
		return ErrorParse::getErrorNo('ok');
	}
	
	/**
	 * 修改订单信息（By 订单id）
	 *
	 * @param int $oid 订单id
	 * @param array[] $updateOrderInfo 待修改的订单信息
	 * @return int 操作成功－1，其他－错误码
	 */
	public static function updateOrderInfoByOid($oid, $updateOrderInfo)
	{
		$orderDBObj = new S_GOrderDB();
		
		//过滤掉无效字段（目前只开放‘订单状态’、‘第三方订单号’、‘快递单号’三个字段）
		$updateOrderInfo = self::filterInputFields($updateOrderInfo, array('order_status'=>0, 'trade_no'=>'', 'express_no'=>''));
	
		$condition = 'oid=:oid';
		$params = array(':oid'=>$oid);
		$orderDBObj->updateByCondition($updateOrderInfo, $condition, $params);
	
		return ErrorParse::getErrorNo('ok');
	}
	
	/**
	 * 取消订单处理
	 *
	 * @param int $oid 订单id
	 * @return int 操作成功－1，其他－错误码
	 */
	public static function cancelOrder($oid)
	{
		$orderInfo = self::getOrderInfoByOid($oid, array('gid', 'uid', 'goods_num', 'goods_tprice', 'order_status','attribute'));
		if(empty($orderInfo))
		{
			return ErrorParse::getErrorNo('order_not_exist');
		}
		if($orderInfo['order_status']!=1)
		{
			//只有在‘待付款’状态下才可以取消订单
			return ErrorParse::getErrorNo('order_error');
		}
		
		$transaction = S_GOrderDB::model()->dbConnection->beginTransaction();	//加入事务处理
		try
		{
			$goodsInfo = GoodsProcess::getGoodsInfo($orderInfo['gid'], array('exchanges', 'price','attribute'));
			$userInfo = UserProcess::getUInfo($orderInfo['uid'], array('iCurScore'));
			
			//第一步、修改订单状态（变更为‘已取消’）
			self::updateOrderStatusByOid($oid, $status=-1);
		
			//第二步、商品还原库存  v2.5商品类别还原库存
			if(isset($goodsInfo['attribute'])&&!empty($goodsInfo['attribute'])){
				$goodType = explode(',',$orderInfo['attribute']);
				$goodsInfo['attribute'] = json_decode($goodsInfo['attribute'],true);
				foreach($goodsInfo['attribute'] as &$v){
					foreach($v['value'] as &$val){
						foreach($goodType as $val1){
							if($val['size'] == $val1){
								$val['num'] = $val['num'] + $orderInfo['goods_num'];
							}
						}
					}
				}
				$goodsInfo['attribute'] = json_encode($goodsInfo['attribute'],true);
			}
			$updateGoodsInfo = array('exchanges'=>($goodsInfo['exchanges']-$orderInfo['goods_num']),'attribute'=>$goodsInfo['attribute']);
			GoodsProcess::updateGoodsInfo($orderInfo['gid'], $updateGoodsInfo);

			//第三步、解冻E豆
			$updateUserInfo = array('iCurScore'=>($userInfo['iCurScore']+$orderInfo['goods_tprice']));
			UserProcess::updateUserInfo($orderInfo['uid'], $updateUserInfo);
			
			$transaction->commit();
		}
		catch(Exception $e)
		{
			$transaction->rollBack();
		}
		
		return ErrorParse::getErrorNo('ok');
	}
	
	/**
	 * 订单支付成功后的处理
	 *
	 * @param string $orderNo 订单号
	 * @param string $tradeNo 第三方交易号
	 * @return int 操作成功－1，其他－错误码
	 */
	public static function paySuccess($orderNo, $tradeNo='')
	{
		$orderInfo = GOrderProcess::getOrderInfoByOrderno($orderNo
				, array('oid', 'gid', 'uid', 'goods_num', 'goods_price', 'goods_tprice'));
		if(empty($orderInfo))
		{
			return ErrorParse::getErrorNo('order_not_exist');
		}
		
		$transaction = S_GOrderDB::model()->dbConnection->beginTransaction();	//加入事务处理
		try
		{
			$goodsInfo = GoodsProcess::getGoodsInfo($orderInfo['gid']
					, array('exchanges', 'price', 'name', 'coupon_id', 'voucher_id', 'third_id'));
			$userInfo = UserProcess::getUInfo($orderInfo['uid'], array('sPhone'));
			
			//第一步、修改订单状态（变更为‘待发货’）、第三方交易号
			self::updateOrderInfoByOrderno($orderNo, array('order_status'=>2, 'trade_no'=>$tradeNo));
			
			//第二步、发放卡、券
			if($goodsInfo['coupon_id']>0)
			{
				//关联电影卡
				$endTime = date("Y-m-d H:i:s",strtotime("+30 day"));
				$cpBaseInfo = CouponProcess::getBaseCouponInfoByCouponID($goodsInfo['coupon_id'], array('mSalePrice'));
				CouponProcess::createCouponSalesInfo($goodsInfo['coupon_id']
													, 1
													, $endTime
													, $orderInfo['uid']
													, $cpBaseInfo['mSalePrice']
													, sprintf(Yii::app()->params['batchInfo']['scoreStore']['exchange_goods'], $orderInfo['gid'], 'dyk', $goodsInfo['coupon_id']));
			}else if($goodsInfo['voucher_id']>0)
			{
				//关联现金券
				VoucherProcess::createVoucher($goodsInfo['voucher_id']
											, $orderInfo['uid']
											, sprintf(Yii::app()->params['batchInfo']['scoreStore']['exchange_goods'], $orderInfo['gid'], 'xjq', $goodsInfo['voucher_id']));
			}else if($goodsInfo['third_id']>0)
			{
				//关联娱乐券
				VoucherProcess::updateThirdInfo($goodsInfo['third_id'], $orderInfo['uid']);
				
				//清除redis（只限于娱乐券，方便在个人中心及时呈现）
				$key = 'recreationinfo1_'.$orderInfo['uid'];
				Yii::app()->redis->delete($key);
			}
			
			//第三步、添加商品订单日志(积分日志)
			ScoreProcess::addScoreLog($orderInfo['uid']
									, $userInfo['sPhone']
									, -1
									, 2
									, $goodsInfo['name']
									, $orderInfo['gid']
									, -$orderInfo['goods_price']
									, $orderInfo['goods_num']
									, $orderInfo['goods_tprice']
									, $orderNo);

		}
		catch(Exception $e)
		{
			$transaction->rollBack();
		}
		
		return ErrorParse::getErrorNo('ok');
	}
	
	/**
	 * 获取订单列表（By uid）
	 *
	 * @param int $uid 用户id
	 * @param array[] $fields 所要字段列表，一维数组，如果为空则获取全部
	 * @param string $filter where添加
	 * @return array[][] 订单列表，二维数组
	 */
	public static function getOrderListByUid($uid, $fields=array(), $filter='')
	{
		if(empty($uid))
		{
			return array();
		}
		$orderDBObj = new S_GOrderDB();
	
		//过滤掉无效字段
		$fields = array_intersect($fields, $orderDBObj->attributeNames());
		$whereStr = empty($filter) ? 'WHERE order_status!=-1 AND uid='.$uid : 'WHERE order_status!=-1 AND uid='.$uid.' AND '.$filter;
		
		if(is_array($fields) and !empty($fields))
		{
			$selStr = '`'.implode('`,`', $fields).'`';
			$sql = "SELECT ".$selStr." FROM {{s_order}} ".$whereStr." ORDER BY createtime DESC";
		}else{
			$sql = "SELECT * FROM {{s_order}} ".$whereStr." ORDER BY createtime DESC";
		}
		
		$orderList = DbUtil::queryAll($sql);
		return $orderList;
	}

	/**
	 * 获取支付订单列表（By uid+payType）
	 *
	 * @param int $uid 用户id
	 * @param string $payType 支付类型（默认：余额支付）
	 * @return array[][] 支付订单列表，二维数组
	 */
	public static function getPayLog($uid, $payType='400003')
	{
		if(empty($uid))
		{
			return array();
		}
		
		$sql = sprintf("SELECT order_no,order_name,goods_price,goods_num,gid,trade_no,express_no,createtime FROM {{s_order}} WHERE uid=%d AND order_pay_type='%s' AND order_status IN(2,3) ORDER BY oid DESC", $uid, $payType);
		$orderList = DbUtil::queryAll($sql);
		
		return $orderList;
	}
}