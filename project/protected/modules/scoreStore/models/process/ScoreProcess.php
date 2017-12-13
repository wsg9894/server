<?php

/**
 * ScoreProcess - E豆操作类
 * @author luzhizhong
 * @version V1.0
 */


class ScoreProcess
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
	 * 检查UnionID（适用于添加E豆日志）
	 *
	 * @param int $uid 用户id
	 * @param int $source E豆来源（11-邀请好友关注；12-邀请好友完成绑定；2-在线选座订单；3-绑定有礼；4-活动券订单）
	 * @param string $union_id 关联id（如果source是邀请好友关注，union_id是好友openid；如果是邀请好友完成绑定，union_id是好友uid；如果是在线选座订单，则union_id是订单id；如果是活动券订单，则union_id是活动id）
	 * @return bool $union_id有效：true；$union_id无效：false
	 */
	private static function checkUnionID($uid, $source=1, $union_id='')
	{
		$ret = FALSE;
		switch($source)
		{
			case -1:				//商品兑换
				break;
			case 11:				//邀请好友关注
				$ret = TRUE;
				break;
			case 12:				//邀请好友完成绑定
				
				if($uid!=$union_id and UserProcess::isRegByUid($union_id))
				{
					$ret = TRUE;
				}
				break;
			case 13:				//新用户关注
				$ret = TRUE;
				break;
			case 2:					//验证订单
				
				$orderDBObj = new L_OrdersDB();
				$condition = 'outerOrderId=:outerOrderId and iUserId=:iUserId';
				$params = array(':outerOrderId'=>$union_id,':iUserId'=>$uid);
				
				if($orderDBObj->getCountByCondition($condition, $params) > 0)
				{
					$ret = TRUE;
				}
				break;
			default:
				$ret = TRUE;		//其他的来源全部放行
				break;
		}
		
		return $ret;
	}
	
	/**
	 * 获取E豆日志描述
	 *
	 * @param int $source E豆来源
	 * @param string $union_id 关联id（如果source是邀请好友关注，union_id是好友openid；如果是邀请好友完成绑定，union_id是好友uid；如果是活动券订单，则union_id是活动id）
	 * @return bool UnionID有效：true；UnionID无效：false
	 */
	private static function getSocreLogDesc($source, $union_id='')
	{
		$desc = '';
		switch($source)
		{
			case 11:
				$inviteUserInfo = UserProcess::getUInfo($union_id, array('sPhone'));
				$desc = '邀请新用户：'.$inviteUserInfo['sPhone'];
				break;
			case 12:
				$inviteUserInfo = UserProcess::getUInfo($union_id, array('sPhone'));
				$desc = '邀请好友完成绑定：'.$inviteUserInfo['sPhone'];
				break;
			case 13:
				$desc = '新用户关注有礼';
				break;
			case 14:
				$inviteUserInfo = UserProcess::getUInfo($union_id, array('sPhone'));
				$desc = '邀请好友完成注册：'.$inviteUserInfo['sPhone'];
				break;
			case 2:
				$desc = '在线选座订单';
				break;
			case 3:
				$desc = '微信绑定有礼';
				break;
			case 4:
				$actInfo = ActProcess::getActInfo($union_id, array('sHuoDongName'));
				$desc = empty($actInfo['sHuoDongName']) ? '活动不存在' : $actInfo['sHuoDongName'];
				break;
			case 6:
				$desc = '每日签到';
				break;
			default:
				break;
		}
		
		return $desc;
	}
	/**
	 * 添加E豆日志
	 *
	 * @param int $uid 用户id
	 * @param string $phone 用户手机号
	 * @param int $source E豆来源
	 * @param int $type 日志类型（1-获取（含管理后台对用户的E豆变更）；2-兑换）
	 * @param string $desc E豆日志描述
	 * @param string $union_id 关联id（如果source是邀请好友注册，union_id是好友uid；如果是活动券订单，则union_id是活动id；如果是商品兑换，则union_id是商品id）
	 * @param int $score 分值
	 * @param int $num 兑换数量
	 * @param int $sendCost 商品运费
	 * @param int $orderNo 交易单号
	 * @return int 添加成功：1；添加失败：错误码
	 */
	public static function addScoreLog($uid, $phone, $source=1, $type=1, $desc='', $union_id='', $score=10, $num=1, $sendCost=0, $orderNo='')
	{
		try
		{
			$slDBObj = new S_ScoreLogDB();
			
			$slDBObj->uid = $uid;
			$slDBObj->phone = $phone;
			$slDBObj->score = $score;
			$slDBObj->source = $source;
			$slDBObj->desc = $desc;
			$slDBObj->union_id = $union_id;
			$slDBObj->log_type = $type;
			$slDBObj->createtime = GeneralFunc::getCurTime();
			
			$slDBObj->num = $num;
			$slDBObj->total_score = $score*$num;
			$slDBObj->send_cost = $sendCost;
			$slDBObj->order_no = $orderNo;
				
			$slDBObj->save();
		}
		catch(Exception $e)
		{
			return ErrorParse::getErrorNo('process_exception');
		}
		
		return ErrorParse::getErrorNo('ok');
	}
	
	/**
	 * 增加E豆操作
	 *
	 * @param int $uid 用户id
	 * @param int $source E豆来源（11-邀请好友关注；12-邀请好友完成绑定；13-新用户关注；14-邀请好友完成注册；2-在线选座订单；3-绑定有礼；4-活动券订单；5-管理后台积分变更；6-签到）
	 * @param string $type 操作类型（1-获取（含管理后台对用户的E豆变更）；2-兑换）
	 * @param string $desc E豆来源描述
	 * @param string $union_id 关联id（如果source是邀请好友关注，union_id是好友openid；如果是邀请好友完成绑定，union_id是好友uid；如果是在线选座订单，则union_id是订单id；如果是活动券订单，则union_id是活动id）
	 * @param int $score E豆（如果是与订单相关的E豆来源，则为实际交易金额）
	 * @return int 操作成功：1；操作失败：错误码
	 */
	public static function addScore($uid, $source=2, $type=1, $desc='', $union_id='', $score=10)
	{
		if(empty($uid) or FALSE==UserProcess::isRegByUid($uid))
		{
			//用户不存在
			return ErrorParse::getErrorNo('user_not_exist');
		}
		
		if(FALSE==self::checkUnionID($uid, $source, $union_id) or (in_array($source, array(2,4)) and !is_numeric($score)))
		{
			//参数错误，需要保证union_id的有效性
			return ErrorParse::getErrorNo('param_error');
		}
		
		//重复操作验证
		$repeatLogInfo = array();
		switch($source)
		{
			case 11:		//如果被邀请者已经赠送过E豆，则不再处理
				$repeatLogInfo = self::getScoreLogInfo(array('lid'), sprintf("union_id='%s' AND source=%d", $union_id, $source));
				break;
			case 12:		//邀请好友关注、绑定，只增加一次E豆
			case 14:		//邀请好友注册，只增加一次E豆
				//$repeatLogInfo = self::getScoreLogInfo(array('lid'), sprintf("uid=%d AND union_id='%s' AND source=%d", $uid, $union_id, $source));
				$repeatLogInfo = self::getScoreLogInfo(array('lid'), sprintf("union_id='%s' AND source=%d", $union_id, $source));
				break;
			case 3:			//绑定有礼，只增加一次E豆
			case 13:		//新用户关注，只增加一次E豆
				$repeatLogInfo = self::getScoreLogInfo(array('lid'), sprintf("uid=%d AND source=%d", $uid, $source));
				break;
			case 6:			//签到，每天每用户只签到一次
				$repeatLogInfo = self::getScoreLogInfo(array('lid'), sprintf("uid=%d AND source=%d AND LEFT(createtime,10)='%s'", $uid, $source, GeneralFunc::getCurDate()));
				break;
			default:
				break;
		}
		
		if(!empty($repeatLogInfo))
		{
			return ErrorParse::getErrorNo('ok');
		}
		
		//E豆上限处理
		if($source==12 or $source==14)
		{
			$postersInfo = self::getPubPostersInfo();
			$logCount = self::getScoreLogCount(sprintf("uid=%d AND source=%d AND LEFT(createtime,10)='%s'", $uid, $source, GeneralFunc::getCurDate()));
			if(!empty($postersInfo['inviter_point_daylimit']) and $logCount>=$postersInfo['inviter_point_daylimit'])
			{
				return ErrorParse::getErrorNo('limit_invite_over');
			}
		}
		
		//获取E豆配置
		$score = self::getSourcePoint($source, $score);
		if(0==$score)
		{
			//如果E豆设置为0，则此来源不做处理
			return ErrorParse::getErrorNo('ok');
		}
		
		$uInfo = UserProcess::getUInfo($uid, array('sPhone', 'iCurScore', 'iTotalScore'));
		
		$transaction = E_UserbaseinfoDB::model()->dbConnection->beginTransaction();		//加入事务处理
		try
		{
			//给用户增加E豆
			$userDBObj = new E_UserbaseinfoDB();
			$attributes = array('iCurScore'=>($uInfo['iCurScore']+$score), 'iTotalScore'=>($uInfo['iTotalScore']+$score));
			$condition = 'iUserID=:iUserID';
			$params = array(':iUserID'=>$uid);
			$userDBObj->updateByCondition($attributes, $condition, $params);
			
			//添加E豆日志
			if(empty($desc))
			{
				$desc = self::getSocreLogDesc($source, $union_id);
			}
			self::addScoreLog($uid, $uInfo['sPhone'], $source, $type, $desc, $union_id, $score);
			
			$transaction->commit();
		}
		catch(Exception $e)
		{
			$transaction->rollBack();
		}
				
		return ErrorParse::getErrorNo('ok');
	}
	
	/**
	 * 获取E豆来源分值
	 *
	 * @param int $source E豆来源
	 * @param int $score E豆（如果是与订单相关的E豆来源，则为实际交易金额）
	 * @return int E豆分值
	 */
	public static function getSourcePoint($source, $score=10)
	{
		$sConfDBObj = new S_ScoreConfDB();
		$condition = '`key`=:key';
		$params = array(':key'=>Yii::app()->params['scoreConf']['keyConf']['sourcePoint']);
		
		$scoreConf = $sConfDBObj->getOneResultByCondition($condition, $params);
		$sPointConf = json_decode($scoreConf['value'], TRUE);
		
		$ret = 0;
		switch($source)
		{
			case 11:				//邀请好友关注
				$postersInfo = self::getPubPostersInfo();
				$ret = !is_numeric($postersInfo['inviter_subscribe_point']) ? 0 : $postersInfo['inviter_subscribe_point'];
				break;
			case 12:				//邀请好友完成绑定
				$postersInfo = self::getPubPostersInfo();
				$ret = !is_numeric($postersInfo['inviter_binding_point']) ? 0 : $postersInfo['inviter_binding_point'];
				break;
			case 13:				//被邀请者关注（新用户关注）
				$postersInfo = self::getPubPostersInfo();
				$ret = !is_numeric($postersInfo['invitee_subscribe_point']) ? 0 : $postersInfo['invitee_subscribe_point'];
				break;
			case 14:				//邀请好友完成注册
				$postersInfo = self::getPubPostersInfo();
				$ret = !is_numeric($postersInfo['inviter_reg_point']) ? 0 : $postersInfo['inviter_reg_point'];
				break;
			case 2:					//在线选座订单
				$mainSoureceID = Yii::app()->params['scoreConf']['sourceConf']['buy_ticket_online']['source_id'];
				$ret = !is_numeric($sPointConf[$mainSoureceID]['point']) ? 0 : round($score*$sPointConf[$mainSoureceID]['point']);
				break;
			case 3:					//绑定有礼
				$mainSoureceID = Yii::app()->params['scoreConf']['sourceConf']['banding_wx']['source_id'];
				$ret = !is_numeric($sPointConf[$mainSoureceID]['point']) ? 0 : $sPointConf[$mainSoureceID]['point'];
				break;
			case 4:					//活动券订单
				$mainSoureceID = Yii::app()->params['scoreConf']['sourceConf']['join_act']['source_id'];
				$ret = !is_numeric($sPointConf[$mainSoureceID]['point']) ? 0 : round($score*$sPointConf[$mainSoureceID]['point']);
				break;
			case 5:					//管理后台的E豆变更（赠送等），E豆分值不做处理
				$ret = $score;
				break;
			case 6:					//签到
				$mainSoureceID = Yii::app()->params['scoreConf']['sourceConf']['sign_in']['source_id'];
				$ret = !is_numeric($sPointConf[$mainSoureceID]['point']) ? 0 : $sPointConf[$mainSoureceID]['point'];
				break;
			default:
				break;
		}
		
		
		return $ret;
	}
	
	
	/**
	 * 获取E豆日志数量
	 *
	 * @param string $filter where添加
	 * @return int 日志数量
	 */
	public static function getScoreLogCount($filter='')
	{
		$sLogDBObj = new S_ScoreLogDB();
		
		if(empty($filter))
		{
			$sql = sprintf('SELECT COALESCE(SUM(num),0) AS num FROM {{s_scorelog}}');
		}else{
			$sql = sprintf('SELECT COALESCE(SUM(num),0) AS num FROM {{s_scorelog}} WHERE 1 AND %s', $filter);
		}
		
		$sLogInfo = $sLogDBObj->getOneResultBySql($sql);
		$sLogCount = $sLogInfo['num'];
		
		return $sLogCount;
	}
	
	/**
	 * 获取E豆数量
	 *
	 * @param string $filter where添加
	 * @return int E豆数量
	 */
	public static function getScoreSum($filter='')
	{
		$sLogDBObj = new S_ScoreLogDB();
	
		if(empty($filter))
		{
			$sql = sprintf('SELECT COALESCE(SUM(total_score),0) AS num FROM {{s_scorelog}}');
		}else{
			$sql = sprintf('SELECT COALESCE(SUM(total_score),0) AS num FROM {{s_scorelog}} WHERE 1 AND %s', $filter);
		}
	
		$sLogInfo = $sLogDBObj->getOneResultBySql($sql);
		$scoreSum = $sLogInfo['num'];
	
		return $scoreSum;
	}
	
	/**
	 * 获取E豆日志列表
	 *
	 * @param array[] $fields 所要字段列表，一维数组，如果为空则获取全部
	 * @param string $filter where添加
	 * @param int $curPage	当前页
	 * @param int $pageSize	每页数量
	 * @return array[][] E豆日志列表，二维数组
	 */
	public static function getScoreLogList($fields=array(), $filter='', $curPage=1, $pageSize=6)
	{
		$sLogDBObj = new S_ScoreLogDB();
		$sLogCount = self::getScoreLogCount($filter);
		
		//过滤掉无效字段
		$fields = array_intersect($fields, $sLogDBObj->attributeNames());
		if(is_array($fields) and !empty($fields))
		{
			$selStr = '`'.implode('`,`', $fields).'`';
		}else{
			$selStr = '*';
		}
		$whereStr = empty($filter) ? 'WHERE 1' : 'WHERE 1 AND '.$filter;
		$sql = 'SELECT '.$selStr.' FROM {{s_scorelog}} '.$whereStr.' ORDER BY lid DESC LIMIT '.($curPage-1)*$pageSize.','.$pageSize;
		
		$ret = array();
		$ret['sLogList'] = DbUtil::queryAll($sql);
		$ret['pageInfo'] = GeneralFunc::getPageInfo($sLogCount, $curPage, $pageSize);
	
		return $ret;
	}
	
	/**
	 * 获取日志信息（单条）
	 *
	 * @param array[] $fields 所要字段列表，一维数组，如果为空则获取全部
	 * @param string $filter where添加
	 * @return array[] E豆日志信息，一维数组
	 */
	public static function getScoreLogInfo($fields=array(), $filter='')
	{
		$sLogDBObj = new S_ScoreLogDB();
	
		//过滤掉无效字段
		$fields = array_intersect($fields, $sLogDBObj->attributeNames());
		if(is_array($fields) and !empty($fields))
		{
			$selStr = '`'.implode('`,`', $fields).'`';
		}else{
			$selStr = '*';
		}
	
		$whereStr = empty($filter) ? 'WHERE 1' : 'WHERE 1 AND '.$filter;
		$sql = 'SELECT '.$selStr.' FROM {{s_scorelog}} '.$whereStr;
	
		$logInfo = DbUtil::queryRow($sql);
		return $logInfo;
	}
	/**
	 * 获取E豆商城配置信息
	 *
	 * @param string $key 配置key
	 * @return string $value
	 */
	public static function getScoreConf($key)
	{
		$ret = '';
		$sql = sprintf("SELECT `value` FROM {{s_scoreconf}} WHERE `key`='%s'", $key);
		$confInfo = DbUtil::queryRow($sql);
		if(!empty($confInfo['value']))
		{
			$ret = json_decode($confInfo['value'], TRUE);
		}
		
		return $ret;
	}
	
	/**
	 * 获取当前发布的海报信息
	 *
	 * @return array[] 海报信息，一维数组
	 */
	public static function getPubPostersInfo()
	{
		$sql = 'SELECT * FROM {{s_posters}} WHERE is_pub=1 LIMIT 1';
		$postersInfo = DbUtil::queryRow($sql);
		
		if(empty($postersInfo))
		{
			$sql = 'SELECT * FROM {{s_posters}} ORDER BY pid DESC LIMIT 1';
			$postersInfo = DbUtil::queryRow($sql);
		}
		
		return $postersInfo;
	}
	/**
	 * 获取用户签到统计信息
	 *
	 * @param int $uid 用户id
	 * @return array[] 统计信息，一维数组
	 */
	public static function getUserSignInCount($uid)
	{
		$sql = sprintf('SELECT COALESCE(SUM(score),0) AS signInSum,COUNT(DISTINCT(LEFT(createtime,10))) AS signInDays FROM {{s_scorelog}} WHERE uid=%d AND source=%d', $uid, Yii::app()->params['scoreConf']['sourceConf']['sign_in']['source_id']);
		$countInfo = DbUtil::queryRow($sql);
		
		return $countInfo;
	}
	
	/**
	 * 判断用户今日是否已签到
	 *
	 * @param int $uid 用户id
	 * @return bool FALSE-未签到；TRUE-已签到
	 */
	public static function isSignInToday($uid)
	{
		if(empty($uid) or !is_numeric($uid))
		{
			return false;
		}
		$sql = sprintf("SELECT lid FROM {{s_scorelog}} WHERE uid=%d AND source=%d AND LEFT(createtime,10)='%s'", $uid, Yii::app()->params['scoreConf']['sourceConf']['sign_in']['source_id'], GeneralFunc::getCurDate());
		$signInInfo = DbUtil::queryRow($sql);
	
		return empty($signInInfo) ? FALSE : TRUE;
	}
	
	/**
	 * 绑定处理（用于绑定有礼的后续操作）
	 * 1、给被邀请者加券
	 * 2、给被邀请者加豆
	 * 3、给邀请者加豆
	 *
	 * @param string $openId 用户Openid
	 * @return bool
	 */
	public static function bindingOpera($openId)
	{
		$uInfo = UserProcess::getUInfoByOpenid($openId, array('iUserID', 'sPhone'));
		if(empty($uInfo))
		{
			return FALSE;
		}
		
		try
		{
			$postersInfo = ScoreProcess::getPubPostersInfo();
			
			//第一步、给被邀请者加券
			$inviteeVoucherArr = explode(',', $postersInfo['invitee_prize']);
			foreach ($inviteeVoucherArr as $index => $voucherID)
			{
				$ret = VoucherProcess::createVoucher($voucherID
						, $uInfo['iUserID']
						, sprintf(Yii::app()->params['batchInfo']['scoreStore']['invite'], 'xjq', $voucherID));
				$prizeList[] = array('iVoucherID'=>$voucherID, 'sVoucherName'=>$ret['sVoucherName'], 'mVoucherMoney'=>round($ret['mVoucherMoney']));
			}
			//记录奖品日志
			PrizeProcess::addPrizeLog($uInfo['iUserID'], $postersInfo['pid'], $postersInfo['invitee_prize'], $prizeType=2);
				
			//第二步、给被邀请者加E豆
			self::addScore($uInfo['iUserID'], 3);
			
			//第三步、给邀请者加E豆
			$inviterUid = UserProcess::getInviteUsersByOpenid($openId);
			ScoreProcess::addScore($inviterUid, 12, 1, '', $uInfo['iUserID']);
		}
		catch(Exception $e)
		{
			return FALSE;
		}
		
		return TRUE;
	}
	
	/**
	 * 获取绑定成功的数据返回
	 * 1、被邀请者的券列表
	 * 2、被邀请者的加豆数量
	 * 3、推荐商品（两个）
	 *
	 * @return array[][] 返回数据
	 */
	public static function getBindingSuccessData()
	{
		//券信息
		$postersInfo = ScoreProcess::getPubPostersInfo();
		$prizeList = PrizeProcess::getPrizeListByVoucherIDs($postersInfo['invitee_prize']);
		
		//绑定有礼积分
		$bindingScore = self::getSourcePoint(Yii::app()->params['scoreConf']['sourceConf']['banding_wx']['source_id']);
		
		//推荐商品（两个）
		$recommendInfo = ScoreProcess::getScoreConf(Yii::app()->params['scoreConf']['keyConf']['recommend']);	//推荐位
		$selFields = array('gid', 'list_name', 'list_pic', 'price');
		$filter = sprintf('status=1 AND gid NOT IN(%d,%d)', $recommendInfo['firstGoodsID'], $recommendInfo['secondGoodsID']);
		$tempArr = GoodsProcess::getGoodsList($selFields, 'status=1', 1, 2);
		$goodsList = $tempArr['goodsList'];
		
		$ret = array('voucherList'=>$prizeList, 'bindingScore'=>$bindingScore, 'goodsList'=>$goodsList);
		return $ret;
	}
	
}