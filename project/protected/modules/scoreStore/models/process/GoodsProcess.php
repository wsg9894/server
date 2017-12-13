<?php

/**
 * GoodsProcess - 商品操作类
 * @author luzhizhong
 * @version V1.0
 */

Yii::import('application.models.process.lib.alipay.lib.*');
Yii::import('application.models.process.lib.alipay.Alipay');

class GoodsProcess
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
	 * 获取商品信息（By 商品id）
	 *
	 * @param int $gid 商品id
	 * @param array[] $fields 所要字段列表，一维数组，如果为空则获取全部
	 * @return array[] 商品信息，一维数组
	 */
	public static function getGoodsInfo($gid, $fields=array())
	{
		$goodsInfo = array();
		if(empty($gid))
		{
			return $goodsInfo;
		}
		$goodsDBObj = new S_GoodsDB();
	
		//过滤掉无效字段
		$fields = array_intersect($fields, $goodsDBObj->attributeNames());
		if(is_array($fields) and !empty($fields))
		{
			$selStr = '`'.implode('`,`', $fields).'`';
			$sql = 'SELECT '.$selStr.' FROM {{s_goods}} WHERE gid='.$gid;
		}else{
			$sql = 'SELECT * FROM {{s_goods}} WHERE gid='.$gid;
		}
		
		$goodsInfo = DbUtil::queryRow($sql);
		if(!empty($goodsInfo['detail_pic']))
		{
			$goodsInfo['detail_pic_arr'] = json_decode($goodsInfo['detail_pic'], true);
		}
		if(!empty($goodsInfo['desc_pic']))
		{
			$goodsInfo['desc_pic_arr'] = json_decode($goodsInfo['desc_pic'], true);
		}
		return $goodsInfo;
	}
	
	/**
	 * 获取商品数量
	 *
	 * @param string $filter where添加
	 * @return int 商品数量
	 */
	public static function getGoodsCount($filter='')
	{
		$goodsDBObj = new S_GoodsDB();
		$sql = sprintf('SELECT COUNT(gid) FROM {{s_goods}} WHERE 1 AND %s', $filter);
		
		$goodsCount = $goodsDBObj->getCountBySql($sql);
		return $goodsCount;
	}
	/**
	 * 获取商品列表
	 *
	 * @param array[] $fields 所要字段列表，一维数组，如果为空则获取全部
	 * @param string $filter where添加
	 * @param int $curPage	当前页
	 * @param int $pageSize	每页数量
	 * @return array[][] 商品列表，二维数组
	 */
	public static function getGoodsList($fields=array(), $filter='', $curPage=1, $pageSize=6)
	{
		$goodsDBObj = new S_GoodsDB();
		$goodsCount = self::getGoodsCount($filter);
		
		//过滤掉无效字段
		$fields = array_intersect($fields, $goodsDBObj->attributeNames());
		if(is_array($fields) and !empty($fields))
		{
			$selStr = '`'.implode('`,`', $fields).'`';
		}else{
			$selStr = '*';
		}
		
		$whereStr = empty($filter) ? 'WHERE 1' : 'WHERE 1 AND '.$filter;
		$sql = 'SELECT '.$selStr.' FROM {{s_goods}} '.$whereStr.' ORDER BY `weight` DESC LIMIT '.($curPage-1)*$pageSize.','.$pageSize;
		
		$ret = array();
		$ret['goodsList'] = DbUtil::queryAll($sql);
		$ret['pageInfo'] = GeneralFunc::getPageInfo($goodsCount, $curPage, $pageSize);
	
		return $ret;
	}
	
	/**
	 * 获取商品库存
	 *
	 * @param int $gid 商品id
	 * @return int 商品库存
	 */
	public static function getGoodsStock($gid)
	{
		$fields = array('total', 'exchanges');
		$goodsInfo = self::getGoodsInfo($gid);
		
		$stock = 0;
		if(!empty($goodsInfo) and $goodsInfo['total'] > $goodsInfo['exchanges'])
		{
			$stock = $goodsInfo['total'] - $goodsInfo['exchanges'];
		}
		
		return $stock;
	}
	
	/**
	 * 检查商品是否可兑换
	 *
	 * @param int $uid 用户id
	 * @param int $gid 商品id
	 * @param int $exchangeNum 兑换数量
	 * @return int 1-可兑换；其他-错误码
	 */
	public static function isExchange($uid, $gid, $exchangeNum=1)
	{
		$goodsFields = array('starttime', 'endtime', 'total', 'exchanges', 'limit_day', 'limit_user', 'status', 'price');
		$goodsInfo = self::getGoodsInfo($gid, $goodsFields);
		
		$userFields = array('sPhone', 'iCurScore');
		$userInfo = UserProcess::getUInfo($uid, $userFields);
  		
		//是否本人操作
		$uSessInfo = UserProcess::getLoginSessionInfo();
		if(empty($uSessInfo['iUserID']) or $uSessInfo['iUserID']!=$uid)
		{
			return ErrorParse::getErrorNo('user_not_login');
		}
 	
		//用户是否存在
		if(empty($userInfo))
		{
			return ErrorParse::getErrorNo('user_not_exist');
		}
	
		//商品是否存在
		if(empty($goodsInfo))
		{
			return ErrorParse::getErrorNo('goods_not_exist');
		}
		
		//商品抢兑时间判断（开始时间）
		if(GeneralFunc::getCurTime() <= $goodsInfo['starttime'])
		{
			return ErrorParse::getErrorNo('goods_not_start');
		}
		
		//商品过期判断（结束时间）
		if(GeneralFunc::getCurTime() > $goodsInfo['endtime'])
		{
			return ErrorParse::getErrorNo('goods_endtime_over');
		}

		//商品已下架
		if($goodsInfo['status']==0)
		{
			return ErrorParse::getErrorNo('goods_do_not_sale');
		}
		
		//库存判断
		if($goodsInfo['total'] <= $goodsInfo['exchanges'])
		{
			return ErrorParse::getErrorNo('goods_stock_empty');
		}

		//用户的当前E豆可以兑换的数量（用户当前E豆可兑换多少）
		$scoreExchangeNum = floor($userInfo['iCurScore']/$goodsInfo['price']);
		if($exchangeNum>$scoreExchangeNum)
		{
			return ErrorParse::getErrorNo('user_score_not_enough');
		}
		
		//今日可兑换的商品数量（今日商品放量多少）
		$goodsAllowExchangeNum = GoodsProcess::getAllowExchangeNum($gid);
		if($goodsAllowExchangeNum<=0)
		{
			return ErrorParse::getErrorNo('limit_day_over');
		}else if($exchangeNum>$goodsAllowExchangeNum)
		{
			return ErrorParse::getErrorNo('goods_exchangenum_error');
		}
		
		//用户剩余的兑换数量（用户还有多少数量的兑换资格）
		$filter = sprintf("uid=%d AND source=-1 AND union_id=%d", $uid, $gid);
		$userExchangeNum = ScoreProcess::getScoreLogCount($filter);	//用户已兑换数量
		if($goodsInfo['limit_user']>0)
		{
			$userAllowExchangeNum = $goodsInfo['limit_user'] - $userExchangeNum;
		}else{
			$userAllowExchangeNum = $goodsAllowExchangeNum;
		}
		if($exchangeNum>$userAllowExchangeNum)
		{
			return ErrorParse::getErrorNo('limit_user_over');
		}
		
		
		
/* 		//用户当前积分是否够兑换商品
		if($userInfo['iCurScore'] < $goodsInfo['price'])
		{
			return ErrorParse::getErrorNo('user_score_not_enough');
		}
		
		//商品-每日兑换上限判断
		$todayExchangeCount = ScoreProcess::getScoreLogCount("source=-1 AND LEFT(createtime,10)='".GeneralFunc::getCurDate()."' AND union_id=".$gid);
		if($goodsInfo['limit_day']>0 and $goodsInfo['limit_day']<=$todayExchangeCount)
		{
			return ErrorParse::getErrorNo('limit_day_over');
		}

		//商品-每用户兑换上限判断
		$userExchangeCount = ScoreProcess::getScoreLogCount("uid=".$uid." AND source=-1 AND union_id=".$gid);
		if($goodsInfo['limit_user']>0 and $goodsInfo['limit_user']<=$userExchangeCount)
		{
			return ErrorParse::getErrorNo('limit_user_over');
		}
 */		

		return ErrorParse::getErrorNo('ok');
	}

	/**
	 * 商品兑换（V1.0）
	 *
	 * @param int $uid 用户id
	 * @param int $gid 商品id
	 * @return int 1-成功；其他-错误码
	 */
	public static function exchangeGoods($uid, $gid)
	{
		//是否可兑换判断
		$ret = self::isExchange($uid, $gid);
		if($ret!=1)
		{
			return $ret;
		}
		$goodsFields = array('name', 'price', 'exchanges', 'coupon_id', 'voucher_id', 'third_id');
		$goodsInfo = self::getGoodsInfo($gid, $goodsFields);
		
		$userFields = array('sPhone', 'iCurScore');
		$userInfo = UserProcess::getUInfo($uid, $userFields);
		
		$transaction = S_GoodsDB::model()->dbConnection->beginTransaction();		//加入事务处理
		try
		{
			//发放卡、券
			if($goodsInfo['coupon_id']>0)
			{
				//关联电影卡
				$endTime = date("Y-m-d H:i:s",strtotime("+30 day"));
				$cpBaseInfo = CouponProcess::getBaseCouponInfoByCouponID($goodsInfo['coupon_id'], array('mSalePrice'));
				CouponProcess::createCouponSalesInfo($goodsInfo['coupon_id']
													, 1
													, $endTime
													, $uid
													, $cpBaseInfo['mSalePrice']
													, sprintf(Yii::app()->params['batchInfo']['scoreStore']['exchange_goods'], $gid, 'dyk', $goodsInfo['coupon_id']));
			}else if($goodsInfo['voucher_id']>0)
			{
				//关联现金券
				VoucherProcess::createVoucher($goodsInfo['voucher_id']
											, $uid
											, sprintf(Yii::app()->params['batchInfo']['scoreStore']['exchange_goods'], $gid, 'xjq', $goodsInfo['voucher_id']));
			}else if($goodsInfo['third_id']>0)
			{
				//关联娱乐券
				VoucherProcess::updateThirdInfo($goodsInfo['third_id'], $uid);
				
				//清除redis（只限于娱乐券，方便在个人中心及时呈现）
				$key = 'recreationinfo1_'.$uid;
				Yii::app()->redis->delete($key);
			}
			
			//添加商品订单日志(积分日志)
			ScoreProcess::addScoreLog($uid, $userInfo['sPhone'], -1, 2, $goodsInfo['name'], $gid, -$goodsInfo['price']);
							
			//用户扣除积分
			$score = $goodsInfo['price'];
			$userDBObj = new E_UserbaseinfoDB();
			$attributes = array('iCurScore'=>($userInfo['iCurScore']-$score));
			$condition = 'iUserID=:iUserID';
			$params = array(':iUserID'=>$uid);
			$userDBObj->updateByCondition($attributes, $condition, $params);
				
			//商品减库存
			$goodsDBObj = new S_GoodsDB();
			$attributes = array('exchanges'=>($goodsInfo['exchanges']+1));
			$condition = 'gid=:gid';
			$params = array(':gid'=>$gid);
			$goodsDBObj->updateByCondition($attributes, $condition, $params);
			
			$transaction->commit();
		}
		catch(Exception $e)
		{
			$transaction->rollBack();
		}

		return ErrorParse::getErrorNo('ok');
	}
	
	/**
	 * 判断是否为虚拟商品
	 *
	 * @param array[] $goodsInfo 商品信息
	 * @return bool
	 */
	public static function isVirGoods($goodsInfo)
	{
		if(!empty($goodsInfo['coupon_id']) and $goodsInfo['coupon_id']>0)
		{
			return true;
		}
		if(!empty($goodsInfo['voucher_id']) and $goodsInfo['voucher_id']>0)
		{
			return true;
		}
		if(!empty($goodsInfo['third_id']) and $goodsInfo['third_id']>0)
		{
			return true;
		}
		
		return false;
	}
	/**
	 * 虚拟商品兑换（V1.2）
	 *
	 * @param array[] $userInfo 用户信息
	 * @param array[] $goodsInfo 商品信息
	 * @param array[] $orderInfo 订单信息
	 * @return int 1-成功；其他-错误码
	 */
	public static function exchangeVirGoods($userInfo, $goodsInfo, $orderInfo)
	{
		$transaction = S_GoodsDB::model()->dbConnection->beginTransaction();		//加入事务处理
		try
		{
			//发放卡、券
			if($goodsInfo['coupon_id']>0)
			{
				//关联电影卡
				$endTime = date("Y-m-d H:i:s",strtotime("+30 day"));
				$cpBaseInfo = CouponProcess::getBaseCouponInfoByCouponID($goodsInfo['coupon_id'], array('mSalePrice'));
				CouponProcess::createCouponSalesInfo($goodsInfo['coupon_id']
				, 1
				, $endTime
				, $userInfo['iUserID']
				, $cpBaseInfo['mSalePrice']
				, sprintf(Yii::app()->params['batchInfo']['scoreStore']['exchange_goods'], $goodsInfo['gid'], 'dyk', $goodsInfo['coupon_id']));
			}else if($goodsInfo['voucher_id']>0)
			{
				//关联现金券
				VoucherProcess::createVoucher($goodsInfo['voucher_id']
				, $userInfo['iUserID']
				, sprintf(Yii::app()->params['batchInfo']['scoreStore']['exchange_goods'], $goodsInfo['gid'], 'xjq', $goodsInfo['voucher_id']));
			}else if($goodsInfo['third_id']>0)
			{
				//关联娱乐券
				VoucherProcess::updateThirdInfo($goodsInfo['third_id'], $userInfo['iUserID']);
	
				//清除redis（只限于娱乐券，方便在个人中心及时呈现）
				//$key = 'recreationinfo1_'.$userInfo['iUserID'];
				//Yii::app()->redis->delete($key);
				//file_get_contents(Yii::app()->params['baseUrl'].'tasks/delUserRedis.php?type=third&uid='.$userInfo['iUserID']);
			}
				
			//1、生成订单
			$orderInfo['goods_type'] = 2;	//虚拟商品
			$orderInfo['order_status'] = 3;	//此处要将状态置为‘已发货’
			$orderNo = GOrderProcess::createOrder($orderInfo);

			//2、商品减库存
			$goodsDBObj = new S_GoodsDB();
			$attributes = array('exchanges'=>($goodsInfo['exchanges']+1));
			$condition = 'gid=:gid';
			$params = array(':gid'=>$goodsInfo['gid']);
			$goodsDBObj->updateByCondition($attributes, $condition, $params);
				
			//3、用户扣除E豆
			$score = $goodsInfo['price'];
			$userDBObj = new E_UserbaseinfoDB();
			$attributes = array('iCurScore'=>($userInfo['iCurScore']-$score));
			$condition = 'iUserID=:iUserID';
			$params = array(':iUserID'=>$userInfo['iUserID']);
			$userDBObj->updateByCondition($attributes, $condition, $params);
	
			//4、添加商品订单日志(积分日志)
			ScoreProcess::addScoreLog($userInfo['iUserID'], $userInfo['sPhone'], -1, 2, $goodsInfo['name'], $goodsInfo['gid'], -$goodsInfo['price'], 1, 0, $orderNo);
				
			$transaction->commit();
		}
		catch(Exception $e)
		{
			$transaction->rollBack();
		}
	
		return ErrorParse::getErrorNo('ok');
	}
	
	/**
	 * 实物商品兑换（V1.2）
	 *
	 * @param array[] $userInfo 用户信息
	 * @param array[] $goodsInfo 商品信息
	 * @param array[] $orderInfo 订单信息
	 * @param string $payType 支付类型
	 * @return int 1-成功；其他-错误码
	 */
	public static function exchangePhyGoods($userInfo, $goodsInfo, $orderInfo, $payType)
	{
		//第一步、订单处理
		$transaction = S_GOrderDB::model()->dbConnection->beginTransaction();	//加入事务处理
		try
		{
			//1、生成订单
			$orderNo = GOrderProcess::createOrder($orderInfo);
				
			//2、商品减库存 v2.5增加商品类别，减商品类别
			$updateGoodsInfo = array('exchanges'=>($goodsInfo['exchanges']+$orderInfo['goods_num']),'attribute'=>$goodsInfo['attribute']);
			GoodsProcess::updateGoodsInfo($goodsInfo['gid'], $updateGoodsInfo);
				
			//3、冻结E豆
			$updateUserInfo = array('iCurScore'=>($userInfo['iCurScore']-$goodsInfo['price']*$orderInfo['goods_num']));
			UserProcess::updateUserInfo($userInfo['iUserID'], $updateUserInfo);
				
			$transaction->commit();
		}
		catch(Exception $e)
		{
			$transaction->rollBack();
		}
		
		//第二步、支付处理
		if($orderInfo['order_amount']>0)
		{
			switch($payType)
			{
				case Yii::app()->params['payType']['account']:					//余额支付
					$curCcountMoney = UserProcess::getmAccountMoney($userInfo['iUserID']);
					if($curCcountMoney<$orderInfo['order_amount'])
					{
						GeneralFunc::alert('您的余额不足，请充值...');
						GeneralFunc::gotoUrl(Yii::app()->params['baseUrl'].'project/index.php?r=scoreStore/Site/goodsOrder&gid='.$goodsInfo['gid']);
						exit(0);
					}
					
					//扣除余额
					UserProcess::updateAccountPay($userInfo['iUserID'], $orderInfo['order_amount']);
					break;
				case Yii::app()->params['payType']['weixin']:					//微信支付
					$url = "/wxpays/pay/jsapi_ss.php?orderNo=".$orderNo;
					header("Location: ".$url);
					break;
				case Yii::app()->params['payType']['alipay']:					//支付宝支付
					$alipay = new Alipay();
					$alipay->doSubmitMobile($orderNo, $goodsInfo['name'], $orderInfo['order_amount']);
					break;
				default:
					GeneralFunc::alert('请选择支付方式...');
					GeneralFunc::gotoUrl(Yii::app()->params['baseUrl'].'project/index.php?r=scoreStore/Site/goodsOrder&gid='.$goodsInfo['gid']);
					exit(0);
					break;
			}
		}
		
		//第三步、添加兑换记录（只针对余额支付、纯E豆支付）
		if(FALSE==in_array($payType, array(Yii::app()->params['payType']['weixin'], Yii::app()->params['payType']['alipay'])))
		{
			//1、修改订单状态（待发货）
			GOrderProcess::updateOrderStatusByOrderno($orderNo, $status=2);
				
			//2、添加兑换记录
			ScoreProcess::addScoreLog($userInfo['iUserID']
									, $userInfo['sPhone']
									, -1
									, 2
									, $goodsInfo['name']
									, $goodsInfo['gid']
									, -$goodsInfo['price']
									, $orderInfo['goods_num']
									, -$goodsInfo['send_cost']
									, $orderNo);
			
		}
		return true;
	}
	
	/**
	 * 获取今日可兑换的商品数量（作为判断是否‘抢光了’的依据）
	 *
	 * @param int $gid 商品id
	 * @return int 今日可兑换的商品数量
	 */
	public static function getAllowExchangeNum($gid)
	{
		$allowExchangeNum = 0;

		$goodsFields = array('total', 'exchanges', 'limit_day');
		$goodsInfo = self::getGoodsInfo($gid, $goodsFields);
		
		if(empty($goodsInfo))
		{
			return $allowExchangeNum;
		}
		
		//今日已兑换数量
		$filter = sprintf("source=-1 AND union_id=%d AND LEFT(createtime,10)='%s'", $gid, GeneralFunc::getCurDate());
		$saleNum = ScoreProcess::getScoreLogCount($filter);
		
		//今日可兑换数量
		$remainNum = $goodsInfo['total']-$goodsInfo['exchanges'];	//剩余库存
		
		if($goodsInfo['limit_day']>0)
		{
			$todayAllows = $goodsInfo['limit_day']-$saleNum;
			$allowExchangeNum = $remainNum < $todayAllows ? $remainNum : $todayAllows;
		}else{
			$allowExchangeNum = $remainNum;
		}
		
		return $allowExchangeNum;
	}
	
	/**
	 * 修改商品信息
	 *
	 * @param int $gid 商品id
	 * @param array[] $updateGoodsInfo 待修改的商品信息
	 * @return int 操作成功－1，其他－错误码
	 */
	public static function updateGoodsInfo($gid, $updateGoodsInfo)
	{
		$goodsDBObj = new S_GoodsDB();
		
		//过滤掉无效字段
		$updateGoodsInfo = self::filterInputFields($updateGoodsInfo, $goodsDBObj->model()->attributes);
		
		if(empty($updateGoodsInfo))
		{
			return ErrorParse::getErrorNo('ok');
		}
		
		$condition = 'gid=:gid';
		$params = array(':gid'=>$gid);
		$goodsDBObj->updateByCondition($updateGoodsInfo, $condition, $params);
	
		return ErrorParse::getErrorNo('ok');
	}

	/**
	 * 根据错误码返回错误信息（用于前台）
	 * @param int $gid 商品id
	 * @param int $errNo 错误码
	 * @return array[] 返回数组
	 */
	public static function getExchangeErrInfo($gid, $errNo)
	{
		$retArr = array();
		switch($errNo)
		{
			case '-201':		//用户不存在
				$retArr = array('ok'=>FALSE, 'errmsg'=>'用户不存在');
				break;
			case '-202':		//用户未登录
				$gotoUrl = sprintf('/usercenter/login.html?go=/project/index.php?r=scoreStore/Site/goodsDetail&gid=%d&iUserSourceID=77', $gid);
				$retArr = array('ok'=>FALSE, 'errmsg'=>'兑换商品需要<a href='.$gotoUrl.'>登录</a>');
				break;
			case '-401':		//商品不存在
				$retArr = array('ok'=>FALSE, 'errmsg'=>'商品不存在');
				break;
			case '-402':		//商品库存为零
				$retArr = array('ok'=>FALSE, 'errmsg'=>'该商品已抢光');
				break;
			case '-403':		//商品已过期
				$retArr = array('ok'=>FALSE, 'errmsg'=>'该商品已下架');
				break;
			case '-404':		//当前积分不足
				$retArr = array('ok'=>FALSE, 'errmsg'=>'您的E豆不足');
				break;
			case '-405':		//每日兑换已达上限
				$retArr = array('ok'=>FALSE, 'errmsg'=>'今日已抢光');
				break;
			case '-406':		//每用户兑换已达上限
				$retArr = array('ok'=>FALSE, 'errmsg'=>'已经抢过了');
				break;
			case '-407':		//商品已下架
				$retArr = array('ok'=>FALSE, 'errmsg'=>'商品已下架');
				break;
			case '-408':		//抢兑尚未开始
				$retArr = array('ok'=>FALSE, 'errmsg'=>'抢兑即将开始');
				break;
			case '-409':		//商品兑换数量有误（1、库存不足；2、已达今日限量；3、用户E豆不足）
				$retArr = array('ok'=>FALSE, 'errmsg'=>'库存不足/已达今日上限/您的E豆不足');
				break;
			default:
				$retArr = array('ok'=>TRUE, 'errmsg'=>'');
				break;
		}
	
		return $retArr;
	}
}