<?php

/**
 * UserProcess - 用户信息操作类
 * @author luzhizhong
 * @version V1.0
 */


class UserProcess
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
	 * 判断用户是否注册（By Uid）
	 *
	 * @param int $uid 用户id
	 * @return bool 已注册：TRUE；未注册：FALSE
	 */
	public static function isRegByUid($uid)
	{		
		$userDBObj = new E_UserbaseinfoDB();
		$condition = 'iUserID=:iUserID';
		$params = array(':iUserID'=>$uid);
		
		return $userDBObj->getCountByCondition($condition, $params) > 0 ? TRUE :FALSE;
	}
	
	/**
	 * 获取用户信息
	 *
	 * @param int $uid 用户id
	 * @param array[] $fields 所要字段列表，一维数组，如果为空则获取全部
	 * @return array[] 用户信息，一维数组
	 */
	public static function getUInfo($uid, $fields = array())
	{
		if(empty($uid) or !is_numeric($uid) or !self::isRegByUid($uid))
		{
			return array();
		}
		$userDBObj = new E_UserbaseinfoDB();

		//过滤掉无效字段
		$fields = array_intersect($fields, $userDBObj->attributeNames());
		if(is_array($fields) and !empty($fields))
		{
			$selStr = '`'.implode('`,`', $fields).'`';
			$sql = 'SELECT '.$selStr.' FROM {{e_userbaseinfo}} where iUserID='.$uid;
		}else{
			$sql = 'SELECT * FROM {{e_userbaseinfo}} where iUserID='.$uid;
		}
		
		return DbUtil::queryRow($sql);
	}
	/**
	 * 判断用户是否登录
	 *
	 * @return int 已登录：uid；未登录：0
	 */
	public static function isLogin()
	{
		$uid = 0;
		$sessObj = new Session();
		if($sessObj->is_registry('iUserID'))
		{
			$uid = $sessObj->get('iUserID');
		}
	
		return $uid;
	}
	
	/**
	 * 获取登录Sess信息
	 *
	 * @return array[] 登录Sess信息
	 */
	public static function getLoginSessionInfo()
	{
		$uInfo = array();
		$sessObj = new Session();
		
		if($sessObj->is_registry('iUserID'))
		{
			$uInfo['iUserID'] = $sessObj->get('iUserID');
			$uInfo['sPhone'] = $sessObj->get('sPhone');
			$uInfo['activity'] = $sessObj->get('activity');					//用于标注是否为‘新注册用户’
			$uInfo['activity_verify'] = $sessObj->get('activity_verify');	//标注是否为‘新注册用户’，且加入验证门槛
		}
		if($sessObj->is_registry('openid'))
		{
			$uInfo['openid'] = $sessObj->get('openid');
		}
		
		return $uInfo;
	}

	/**
	 *
	 * @return array
	 */
	public static function addLoginSessionInfo($UserInfo)
	{
		$sessObj = new Session();
		$uInfo['iUserID'] = $sessObj->add('iUserID',$UserInfo['iUserID']);
		$uInfo['sPhone'] = $sessObj->add('sPhone',$UserInfo['sPhone']);
		return $uInfo;
	}

	/**
	 * 设置页面的iCurCid存session
	 *
	 * @return array[] 登录Sess信息
	 */
	public static function setPageSessionInfo($iCurCid)
	{
		$sessObj = new Session();
		$sessObj->add('iCurCid',$iCurCid);
	}
	/**
	 * 获取当前页面的iCurCid
	 *
	 * @return array[] 登录Sess信息
	 */
	public static function getPageSessionInfo()
	{
		$uInfo = array();
		$sessObj = new Session();
		$uInfo['iCurCid'] = $sessObj->get('iCurCid');
		return $uInfo;
	}
	
	/**
	 * 获取openid Sess信息
	 *
	 * @return array[] 登录Sess信息
	 */
	public static function getOpenIDSessionInfo()
	{
		$uInfo = array();
		$sessObj = new Session();
		
		if($sessObj->is_registry('openid'))
		{
			$uInfo['openid'] = $sessObj->get('openid');
		}
		return $uInfo;
	}
	
	
	
	/**
	 * 获取带有logo的二维码图片地址
	 *
	 * @param int $uid	用户id
	 * @return string 二维码图片地址
	 */
	public static function getInviteQRCodePath($uid)
	{
		$logoQR = sprintf(Yii::app()->params['qrConf']['qrPath']['logo'], $uid);
		if(file_exists($logoQR))
		{
			return $logoQR;
		}else{
			return '';
		}
	}

	/**
	 * 获取海报图片地址
	 *
	 * @param int $uid	用户id
	 * @return string 海报图片地址
	 */
	public static function getInvitePostersPath($uid)
	{
		if(empty($uid))
		{
			return '';
		}
		
		$postersPic = sprintf(Yii::app()->params['qrConf']['qrPath']['posters'], $uid);
		if(file_exists($postersPic))
		{
			return $postersPic;
		}else{
			return '';
		}
	}
	/**
	 * 生成邀请二维码
	 *
	 * @param int $uid	用户id
	 * @param string $qrUrl	二维码Url
	 * @return string 二维码图片地址
	 */
	public static function createInviteQRCode($uid, $qrUrl)
	{
		if(empty($uid))
		{
			return ErrorParse::getErrorNo('user_not_exist');
		}
		$errorCorrectionLevel = Yii::app()->params['qrConf']['errorCorrectionLevel'];		//容错级别
		$matrixPointSize = Yii::app()->params['qrConf']['matrixPointSize'];					//生成图片大小
		
		$logo = Yii::app()->params['qrConf']['eLogo'];
		$primeQR = sprintf(Yii::app()->params['qrConf']['qrPath']['prime'], $uid);//原始二维码图
		$logoQR = sprintf(Yii::app()->params['qrConf']['qrPath']['logo'], $uid);	//带logo的二维码图
		
		//生成原始二维码图片
		QRcode::png($qrUrl, $primeQR, $errorCorrectionLevel, $matrixPointSize, 2);
		
		if ($logo !== FALSE)
		{
			$primeQR = imagecreatefromstring(file_get_contents($primeQR));
			$logo = imagecreatefromstring(file_get_contents($logo));
			$QR_width = imagesx($primeQR);					//二维码图片宽度
			$QR_height = imagesy($primeQR);					//二维码图片高度
			$logo_width = imagesx($logo);					//logo图片宽度
			$logo_height = imagesy($logo);					//logo图片高度
			$logo_qr_width = $QR_width / 5;					//缩放后的logo图片宽度
			$scale = $logo_width/$logo_qr_width;
			$logo_qr_height = $logo_height/$scale;			//缩放后的logo图片高度
			
			//logo位置居中
			$from_width = ($QR_width - $logo_qr_width) / 2;
			$from_height = $from_width;
				
			//重新组合图片并调整大小
			imagecopyresampled($primeQR, $logo, $from_width, $from_height, 0, 0, $logo_qr_width,
			$logo_qr_height, $logo_width, $logo_height);
		}
		//生成带logo的二维码图片
		imagepng($primeQR, $logoQR);
		
		return $logoQR;
	}
	
	/**
	 * 生成邀请海报
	 *
	 * @param string $logoQR 带logo的二维码图片地址
	 * @return string 二维码图片地址
	 */
	public static function createInvitePosters($uid, $qrUrl)
	{
		try
		{
			//首先生成带有logo的二维码图片
			$logoQR = self::createInviteQRCode($uid, $qrUrl);
			
			//$scoreConf = ScoreProcess::getScoreConf(Yii::app()->params['scoreConf']['keyConf']['sourceDesc']);
			//$postersBGPic = $scoreConf[Yii::app()->params['scoreConf']['sourceConf']['user_invite']['source_id']]['invitePic']; //海报背景图
			$postersInfo = ScoreProcess::getPubPostersInfo();
			$postersBGPic = $postersInfo['inviter_posters_pic'];	//海报背景图
			
			if(empty($postersBGPic))
			{
				$postersBGPic = Yii::app()->params['qrConf']['postersBGPic'];
			}
			
			$postersBGPic = imagecreatefromstring(file_get_contents($postersBGPic));
			$logoQR = imagecreatefromstring(file_get_contents($logoQR));
			$ret = sprintf(Yii::app()->params['qrConf']['qrPath']['posters'], $uid);	//最终生成的海报图
			
			$posters_width = imagesx($postersBGPic);		//海报图片宽度
			$posters_height = imagesy($postersBGPic);		//海报图片高度
			$qr_width = imagesx($logoQR);					//二维码图片宽度
			$qr_height = imagesy($logoQR);					//二维码图片高度
			$qr_posters_width = $posters_width / 3.6;		//缩放后的二维码图片宽度 
			$scale = $qr_width/$qr_posters_width;
			$qr_posters_height = $qr_height/$scale;			//缩放后的二维码图片高度
			
			//二维码位置居中
			//$from_width = ($QR_width - $logo_qr_width) / 2;
			//$from_height = 540;
			
			//二维码位置居于左下角
			$from_width = 5;
			$from_height = $posters_height - $qr_posters_height - 2;
			
			//重新组合图片并调整大小
			/*
			$dst_image：新建的图片
			$src_image：需要载入的图片
			$dst_x：设定需要载入的图片在新图中的x坐标
			$dst_y：设定需要载入的图片在新图中的y坐标
			$src_x：设定载入图片要载入的区域x坐标
			$src_y：设定载入图片要载入的区域y坐标
			$dst_w：设定载入的原图的宽度（在此设置缩放）
			$dst_h：设定载入的原图的高度（在此设置缩放）
			$src_w：原图要载入的宽度
			$src_h：原图要载入的高度
			*/
			imagecopyresampled($postersBGPic, $logoQR, $from_width, $from_height, 0, 0, $qr_posters_width
			, $qr_posters_height, $qr_width, $qr_height);
	
			//生成带二维码的海报图片
			imagepng($postersBGPic, $ret);
			return $ret;
		}		
		catch(Exception $e)
		{
			return '';
		}
	}
	
	/**
	 * 获取邀请者uid（by openid）
	 *
	 * @param string $openid 被邀请者openid
	 * @return int 邀请者uid
	 */
	public static function getInviteUsersByOpenid($openid)
	{
		if(empty($openid))
		{
			return ErrorParse::getErrorNo('param_error');
		}
		
		$sql = sprintf("SELECT inviter_uid AS uid FROM {{s_invitelog}} WHERE invitee='%s' AND inviter_uid NOT IN(SELECT u.iUserID FROM {{e_userbaseinfo}} u,{{b_useroutpart}} p WHERE p.outPart='%s' AND p.sPhone=u.sPhone) ORDER BY lid DESC LIMIT 1", $openid, $openid);
		$logInfo = DbUtil::queryRow($sql);
		
		return empty($logInfo) ? '' : $logInfo['uid'];
	}
	/**
	 * 获取邀请者uid（by Invitee）
	 *
	 * @param string $invitee 被邀请者openid或uid
	 * @return int 邀请者uid
	 */
	public static function getInviterUidByInvitee($invitee)
	{
		if(empty($invitee))
		{
			return ErrorParse::getErrorNo('param_error');
		}
	
		$sql = sprintf("SELECT inviter_uid FROM {{s_invitelog}} WHERE invitee='%s' ORDER BY lid DESC LIMIT 1", $invitee);
		$logInfo = DbUtil::queryRow($sql);
	
		return empty($logInfo) ? '' : $logInfo['inviter_uid'];
	}
	/**
	 * 创建邀请记录
	 *
	 * @param int $uid 邀请者uid
	 * @param string $openid 被邀请者openid
	 * @return bool
	 */
	public static function createInviteRecord($uid, $openid)
	{
		if(empty($uid) or empty($openid))
		{
			return ErrorParse::getErrorNo('param_error');
		}
		
		$sql = sprintf("REPLACE INTO {{s_invitelog}} SET inviter_uid=%d, invitee='%s'", $uid, $openid);
		return DbUtil::execute($sql);
	}
	
	/**
	 * 获取用户信息（By openid）
	 *
	 * @param string $openid 用户openid
	 * @param array[] $fields 所要字段列表，一维数组，如果为空则获取全部
	 * @return array[] 用户信息，一维数组
	 */
	public static function getUInfoByOpenid($openid, $fields=array())
	{
		$uInfo = array();
		if(empty($openid))
		{
			return $uInfo;
		}
		$userDBObj = new E_UserbaseinfoDB();
	
		//过滤掉无效字段
		$fields = array_intersect($fields, $userDBObj->attributeNames());
		
		//第一步，如果userbaseinfo的openid存在，则直接获取
		if(is_array($fields) and !empty($fields))
		{
			$selStr = '`'.implode('`,`', $fields).'`';
			$sql = "SELECT ".$selStr." FROM {{e_userbaseinfo}} where iOpenID='".$openid."'";
		}else{
			$sql = "SELECT * FROM {{e_userbaseinfo}} where iOpenID='".$openid."'";
		}
		$uInfo = DbUtil::queryRow($sql);
		
		//第二步，如果userbaseinfo的openid为空，则通过tb_b_useroutpart获取
		if(empty($uInfo))
		{
			if(is_array($fields) and !empty($fields))
			{
				$selStr = 'u.`'.implode('`,u.`', $fields).'`';
				$sql = "SELECT ".$selStr." FROM {{e_userbaseinfo}} u,{{b_useroutpart}} o WHERE o.outPart='".$openid."' AND o.sPhone=u.sPhone";
			}else{
				$sql = "SELECT u.* FROM {{e_userbaseinfo}} u,{{b_useroutpart}} o WHERE o.outPart='".$openid."' AND o.sPhone=u.sPhone";
			}
		}
		$uInfo = DbUtil::queryRow($sql);
	
		return $uInfo;
	}
	
	/**
	 * 判断是否绑定了微信
	 *
	 * @param string $openid 用户openid
	 * @return int 0-未绑定；1-绑定
	 */
	public static function isWXBinding($openid)
	{
		$uInfo = self::getUInfoByOpenid($openid);
		return empty($uInfo) ? 0 : 1;
	}
	
	/**
	 * 获取关联的openid（By phone）
	 *
	 * @param string $phone 用户手机号
	 * @return string 用户openid
	 */
	public static function getOpenidByPhone($phone)
	{
		$sql = "SELECT outPart FROM {{b_useroutpart}} WHERE sPhone='".$phone."'";
		$opInfo = DbUtil::queryRow($sql);
	
		return empty($opInfo['outPart']) ? '' : $opInfo['outPart'];
	}
	
	/**
	 * 获取Openid
	 *
	 * @param string $retuUrl OAuth请求Openid后的返回Url（拿到openid的逻辑处理），可带参数
	 * @param int $setSession 是否需要设置Session（openid、iUserID、sPhone）
	 * @return void
	 */
	public static function getOpenidOnOAuth($retuUrl, $setSession=1)
	{
		$setSession = $setSession==0 ? 0 : 1;
		$wx = new Weixin();
		
		$retuUrl = str_replace('&', Yii::app()->params['wxConf']['getOpenid_RetuUrl_ParamSpace'], $retuUrl);
		$callbackUrl = Yii::app()->params['wxConf']['getOpenidUrl'].'&setSession='.$setSession.'&retuUrl='.$retuUrl;
		
		$wx->reqCodeOnOAuth(urlencode($callbackUrl));
	}

	/**
	 * 赠送E豆（E豆商城-邀请好友）
	 * 1、如果被邀请者微信端登录+已关注公众号+没有送豆记录，则赠送被邀请者相应E豆；
	 * 2、满足条件1、则赠送邀请者相应E豆
	 *
	 * @param string $invitee_uid 被邀请者uid
	 * @param string $openid 被邀请者openid
	 * @return int 操作结果		1：	     成功
	 * 			   				-901：操作异常
	 */
	public static function addScoreForInvite($invitee_uid, $openid)
	{
		$wx = new Weixin();
		try
		{
			//获取‘关注公众号’标识
			$uInfo = $wx->getUInfo($openid);
				
			if($uInfo['subscribe'])
			{
				$inviteeUInfo = self::getUInfoByOpenid($openid, array('sPhone'));
				if(FALSE==empty($inviteeUInfo) and FALSE==empty($inviteeUInfo['sPhone']))
				{
					//关注公众号+绑定微信号，被邀请者赠送E豆
					ScoreProcess::addScore($invitee_uid, 13);
				}
	
				//获取邀请者
				$inviterUid = self::getInviterUidByInvitee($invitee_uid);
				if(FALSE==empty($inviterUid))
				{
					//有邀请关系，邀请者赠送E豆
					ScoreProcess::addScore($inviterUid, 11, 1, '', $invitee_uid);
				}
			}
				
				
			/*
				//获取邀请者
			$inviterUid = UserProcess::getInviterUidByInvitee($invitee_uid);
			if(FALSE==empty($inviterUid))
			{
			//有邀请关系，获取‘关注公众号’标识
			$uInfo = Model_Base_Weixin::getUInfo($openid);
	
			if($uInfo['subscribe'])
			{
			//关注了公众号，可以送E豆了（addScore方法会判断是否已赠送）
				
			//被邀请者赠送E豆
			ScoreProcess::addScore($invitee_uid, 13);
				
			//邀请者赠送E豆
			ScoreProcess::addScore($inviterUid, 11, 1, '', $invitee_uid);
			}
			}
			*/
		}
		catch(Exception $e)
		{
			return ErrorParse::getErrorNo('process_exception');
		}
	
		return ErrorParse::getErrorNo('ok');
	}
	
	/**
	 * 添加收货地址
	 *
	 * @param array[] $addressInfo 收货地址信息
	 * @return int >=1：新插入的地址id；其：错误码
	 */
	public static function addUserAddress($addressInfo)
	{
		//过滤无效字段（将数据表中未定义的字段去除）
		$addressInfo = self::filterInputFields($addressInfo,S_UserAddressDB::model()->attributes);
		
		try
		{
			$uaDBObj = new S_UserAddressDB();
			
			reset($addressInfo);
			for($i=0; $i<count($addressInfo); $i++)
			{
				$cField = current($addressInfo);
				$key = key($addressInfo);
				$uaDBObj->$key = $cField;
				next($addressInfo);
			}
	
			if($uaDBObj->validate() and $uaDBObj->save())
			{
				return $uaDBObj->attributes['aid'];
			}				
		}
		catch(Exception $e)
		{
		}
		
		return ErrorParse::getErrorNo('unknown_err');
	}
	
	/**
	 * 获取用户收货地址数量
	 *
	 * @param int $uid 用户id
	 * @return int 用户收货地址数量
	 */
	public static function getUserAddressCount($uid)
	{
		if(empty($uid) or !is_numeric($uid))
		{
			return array();
		}
		$sql = sprintf('SELECT COUNT(aid) AS count FROM {{s_useraddress}} WHERE uid=%d', $uid);
		$addressInfo = DbUtil::queryRow($sql);
		return $addressInfo['count'];
	}
	
	/**
	 * 获取用户收货地址列表
	 *
	 * @param int $uid 用户id
	 * @return array[] 用户收货地址信息，一维数组
	 */
	public static function getUserAddressList($uid)
	{
		if(empty($uid) or !is_numeric($uid))
		{
			return array();
		}
		//$sql = sprintf('SELECT * FROM {{s_useraddress}} WHERE uid=%d ORDER BY is_def DESC, createtime DESC', $uid);
		$sql = sprintf('SELECT * FROM {{s_useraddress}} WHERE uid=%d ORDER BY createtime DESC', $uid);
		return DbUtil::queryAll($sql);
	}
	
	/**
	 * 获取用户默认收货地址信息
	 *
	 * @param int $uid 用户id
	 * @return array[] 用户收货地址信息，一维数组
	 */
	public static function getUserDefAddress($uid)
	{
		if(empty($uid) or !is_numeric($uid))
		{
			return array();
		}
		
		$sql = sprintf('SELECT * FROM {{s_useraddress}} WHERE uid=%d AND is_def=1', $uid);
		return DbUtil::queryRow($sql);
	}
	
	/**
	 * 修改用户收货地址
	 *
	 * @param int $aid 地址id
	 * @param array[] $updateAddressInfo 待修改的地址信息
	 * @return int 操作成功－1，其他－错误码
	 */
	public static function updateUserAddress($aid, $updateAddressInfo)
	{
		$uaDBObj = new S_UserAddressDB();
	
		//过滤掉无效字段（目前只开放‘收货人’、‘手机号’、‘所在地区’、‘详细地址’四个字段）
		$updateAddressInfo = self::filterInputFields($updateAddressInfo, array('receiver_name'=>'', 'receiver_phone'=>'', 'area'=>'', 'address'=>''));
	
		$condition = 'aid=:aid';
		$params = array(':aid'=>$aid);
		$uaDBObj->updateByCondition($updateAddressInfo, $condition, $params);
	
		return ErrorParse::getErrorNo('ok');
	}
	
	/**
	 * 获取用户收货地址信息（By aid）
	 *
	 * @param int $aid 地址id
	 * @return array[] 用户收货地址信息，一维数组
	 */
	public static function getUserAddressByAid($aid)
	{
		if(empty($aid) or !is_numeric($aid))
		{
			return array();
		}
		
		$sql = sprintf('SELECT * FROM {{s_useraddress}} WHERE aid=%d', $aid);
		return DbUtil::queryRow($sql);
	}
	
	/**
	 * 设置用户默认收货地址（只允许有一个默认地址）
	 *
	 * @param int $uid 用户id
	 * @param int $aid 地址id
	 * @return bool
	 */
	public static function setUserDefAddress($uid, $aid)
	{
		if(empty($uid) or !is_numeric($uid) or empty($aid) or !is_numeric($aid))
		{
			return FALSE;
		}
		
		try
		{
			$sql = sprintf('UPDATE {{s_useraddress}} SET is_def=0 WHERE uid=%d', $uid);
			DbUtil::execute($sql);
			
			$sql = sprintf('UPDATE {{s_useraddress}} SET is_def=1 WHERE aid=%d', $aid);
			DbUtil::execute($sql);
		}
		catch(Exception $e)
		{
			return ErrorParse::getErrorNo('unknown_err');
		}
		
		return ErrorParse::getErrorNo('ok');
	}
	
	/**
	 * 获取用户可兑换的商品数量
	 *
	 * @param int $uid 用户id
	 * @param int $gid 商品id
	 * @return int 可兑换的商品数量
	 */
	public static function getAllowExchangeNum($uid, $gid)
	{
		$goodsFields = array('starttime', 'endtime', 'total', 'exchanges', 'limit_day', 'limit_user', 'status', 'price');
		$goodsInfo = GoodsProcess::getGoodsInfo($gid, $goodsFields);
		
		$userFields = array('sPhone', 'iCurScore');
		$userInfo = self::getUInfo($uid, $userFields);
		
		if(empty($goodsInfo) or empty($userInfo))
		{
			return 0;
		}
		
		//今日可兑换的商品数量（今日商品放量多少）
		$goodsAllowExchangeNum = GoodsProcess::getAllowExchangeNum($gid);
		if($goodsAllowExchangeNum==0)
		{
			return 0;
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
		
		if($userAllowExchangeNum==0)
		{
			return 0;
		}
		
		//用户的当前E豆可以兑换的数量（用户当前E豆可兑换多少）
		$scoreExchangeNum = floor($userInfo['iCurScore']/$goodsInfo['price']);
		
		return min($goodsAllowExchangeNum, $userAllowExchangeNum, $scoreExchangeNum);
	}
	
	/**
	 * 修改用户信息
	 *
	 * @param int $gid 商品id
	 * @param array[] $updateGoodsInfo 待修改的商品信息
	 * @return int 操作成功－1，其他－错误码
	 */
	public static function updateUserInfo($uid, $updateUserInfo)
	{
		$userDBObj = new E_UserbaseinfoDB();
	
		//过滤掉无效字段（目前只开放‘总积分’、‘当前积分’两个字段）
		$updateUserInfo = self::filterInputFields($updateUserInfo, array('iCurScore'=>0, 'iTotalScore'=>0));
		
		$condition = 'iUserID=:iUserID';
		$params = array(':iUserID'=>$uid);
		$userDBObj->updateByCondition($updateUserInfo, $condition, $params);
	
		return ErrorParse::getErrorNo('ok');
	}
	
	/**
	 * 获取今日邀请成功的数量（V1.2）
	 *
	 * @param int $inviter 邀请者uid
	 * @return int 邀请成功的数量
	 */
	public static function getInviteCountToday($inviter)
	{
		if(empty($inviter))
		{
			return ErrorParse::getErrorNo('param_error');
		}
		
		$filter = sprintf("uid=%d AND source=%d AND LEFT(createtime,10)='%s'"
				, $inviter
				, 14
				, GeneralFunc::getCurDate());
		$inviteCount = ScoreProcess::getScoreLogCount($filter);
		return $inviteCount;
	}
	
	/**
	 * 签到操作
	 *
	 * @param int $uid 用户id
	 * @return int 1-成功；其他-错误码
	 */
	public static function signIn($uid)
	{
		$ret = ScoreProcess::addScore($uid, 6, 1);
		return $ret;
	}
	

	//查询余额
	public static function getmAccountMoney($iUserID) {

		$sql = sprintf("select mAccountMoney FROM {{e_useraccountinfo}} where iUserID=%d ", $iUserID);
		$result = DbUtil::queryRow($sql);
		if (empty($result)) {
			return 0;
		}
		return $result['mAccountMoney'];
	}

	//查询领取现金券状态
	public static function getCashFlag($iUserID) {

		$sql = sprintf("select getVoucherFlag FROM {{e_useraccountinfo}} where iUserID=%d ", $iUserID);
		$result = DbUtil::queryRow($sql);
		if (empty($result)) {
			return -1;
		}
		return $result['getVoucherFlag'];
	}


//更新余额
	public static function updateAccountPay($iUserID,$mAccountMoney) {

		$sql = sprintf("update {{e_useraccountinfo}} set mAccountMoney = mAccountMoney -'".$mAccountMoney."'  where iUserID=%d ", $iUserID);
		return DbUtil::execute($sql);
	}

//更新领取现金券状态
	public static function updeteCashFlag($iUserID,$getCashFlag) {

		if($getCashFlag == 1){
			$sql = sprintf("update {{e_useraccountinfo}} set getVoucherFlag = 1  where iUserID=%d ", $iUserID);
			return DbUtil::execute($sql);
		}else{
			return 0;
		}
	}


	//小程序用户登录
	public static function getLoginInfo($username,$password) {
		$pass = substr(trim($password), 0, 20);
		$sql = sprintf("select * FROM {{e_userbaseinfo}} where sPhone='%s' and sPassWord='%s'", mysql_escape_string($username), ($pass));
		$result = DbUtil::queryRow($sql);
		if (empty($result)) {
			return 0;
		}
		return $result;
	}

	//小程序用户注册
	public static function getRegisterInfo($username,$password = '') {
		if(!empty($password)){
			$sql = sprintf("select * FROM {{e_userbaseinfo}} where sPhone='%s'", mysql_escape_string($username));
			$result = DbUtil::queryRow($sql);
			if($result){
				$pass = substr(trim($password), 0, 20);
				$sql = sprintf("update {{e_userbaseinfo}} set sPassWord ='".$pass."'  where sPhone=%d ", $username);
				if(DbUtil::execute($sql)){
					return $result;
				}else{
					return 0;
				}
			}else{
				return 0;
			}
		}else{
			$sql = sprintf("select * FROM {{e_userbaseinfo}} where sPhone='%s'", mysql_escape_string($username));
			$result = DbUtil::queryRow($sql);
			if (empty($result)) {
				$subSql = array();
				$csubSql=array();
				$userbaseinfo = array(
					'sPhone' => $username,
					'sMail' => '',
					'sPassWord' => $password,
					'iUsertypeID'=>0,
					'iOperationNum'=>0,
					'dCreatTime'=>date('Y-m-d H:i:s'),
					'sNick'=>$username,
					'dPasswordLastChangeTime'=>'0000-00-00 00:00:00',
					'dPayPassWordLastChangeTime'=>'',
					'bMailCheck'=>'',
					'bPhoneCheck'=>'',
					'dVIPBeginTime'=>'',
					'dVIPEndTime'=>'',
					'iUserSourceID'=>76,
					'sUserCity'=>'',
					'iOpenID'=>'',
					'iCurScore'=>0,
					'iTotalScore'=>0,
					'iHuoDongID'=>0
				);
				foreach($userbaseinfo as $key =>$v)
				{
					$subSql[] = "'".mysql_escape_string($v)."'";
					$csubSql[]=$key;
				}
				$SQL = sprintf("insert into  {{e_userbaseinfo}} (%s) VALUES(%s)",implode(',', $csubSql),implode(',', $subSql));
				if(DbUtil::execute($SQL)){
					$sql = sprintf("select * FROM {{e_userbaseinfo}} where sPhone='%s'", mysql_escape_string($username));
					$result = DbUtil::queryRow($sql);
					$subSql = array();
					$csubSql=array();
					$userbaseinfo = array(
						'iUserID' => $result['iUserID'],
						'sPayPassWord' => '',
						'mAccountMoney' => '0.0000',
						'iRewardScore'=>0,
						'sUserIdentityID'=>'',
						'getVoucherFlag'=>0,
						'dCreateTime'=>date('Y-m-d H:i:s'),
					);
					foreach($userbaseinfo as $key =>$v)
					{
						$subSql[] = "'".mysql_escape_string($v)."'";
						$csubSql[]=$key;
					}
					$SQL = sprintf("insert into  {{e_useraccountinfo}} (%s) VALUES(%s)",implode(',', $csubSql),implode(',', $subSql));
					if(DbUtil::execute($SQL)) {
						return $result;
					}
					return 0;
				}
				return 0;
			}
		}
		return $result;
	}

	//小程序获取openid
	public static function getOpenid($sPhone,$openid = '') {
		if($openid == ''){
			$sql = sprintf("select * FROM {{b_isWeChatapplet}} where sPhone='%s'",$sPhone);
		}else{
			$sql = sprintf("select * FROM {{b_isWeChatapplet}} where openid='%s' and sPhone='%s'", $openid, ($sPhone));
		}
		$result = DbUtil::queryRow($sql);
		if (empty($result)) {
			if(!empty($openid)){
				$subSql = array();
				$csubSql=array();
				$arPaylog = array(
					'openid' => $openid,
					'sPhone' => $sPhone,
					'dCreateTime' => date('Y-m-d H:i:s')
				);
				foreach($arPaylog as $key =>$v)
				{
					$subSql[] = "'".mysql_escape_string($v)."'";
					$csubSql[]=$key;
				}
				$SQL = sprintf("insert into  {{b_isWeChatapplet}} (%s) VALUES(%s)",implode(',', $csubSql),implode(',', $subSql));
				$result = DbUtil::execute($SQL);
			}
		}
		return $result;
	}

	/**
	 * 通过手机号获取绑定的openid
	 * @param $sPhone
	 * @return array
	 */
	public static function getOpenidBysPhone($sPhone,$fields=array()){
		$UseroutpartDBObj = new B_UseroutpartDB();
		$fields = array_intersect($fields, $UseroutpartDBObj->attributeNames());
		$selStr = (is_array($fields) and !empty($fields)) ? '`'.implode('`,`', $fields).'`' : '*';
		$sql = sprintf("select ".$selStr." FROM {{b_useroutpart}} where sPhone='%s'",$sPhone);
		$result = DbUtil::queryRow($sql);
		return $result;
	}

	/**
	 * 游戏添加用户浏览记录
	 *
	 * @param array[] $userBrownLog 浏览记录信息
	 * @return int >=1：新插入的地址id；其：错误码
	 */
	public static function addUserBrowseLog($userBrownLog)
	{
		//过滤无效字段（将数据表中未定义的字段去除）
		$addressInfo = self::filterInputFields($userBrownLog,G_UserBrowseLogDB::model()->attributes);

		try
		{
			$uaDBObj = new G_UserBrowseLogDB();

			reset($addressInfo);
			for($i=0; $i<count($addressInfo); $i++)
			{
				$cField = current($addressInfo);
				$key = key($addressInfo);
				$uaDBObj->$key = $cField;
				next($addressInfo);
			}

			if($uaDBObj->validate() and $uaDBObj->save())
			{
				return $uaDBObj->attributes['aid'];
			}
		}
		catch(Exception $e)
		{
		}

		return ErrorParse::getErrorNo('unknown_err');
	}

	/**
	 * 获取用户信息
	 *
	 * @param int $uid 用户id
	 * @param array[] $fields 所要字段列表，一维数组，如果为空则获取全部
	 * @return array[] 用户信息，一维数组
	 */
	public static function getUInfoBysPhone($sPhone, $fields = array())
	{
		if(empty($sPhone))
		{
			return array();
		}
		$userDBObj = new E_UserbaseinfoDB();

		//过滤掉无效字段
		$fields = array_intersect($fields, $userDBObj->attributeNames());
		if(is_array($fields) and !empty($fields))
		{
			$selStr = '`'.implode('`,`', $fields).'`';
			$sql = 'SELECT '.$selStr.' FROM {{e_userbaseinfo}} where sPhone='.$sPhone;
		}else{
			$sql = 'SELECT * FROM {{e_userbaseinfo}} where sPhone='.$sPhone;
		}

		return DbUtil::queryRow($sql);
	}

	//小程序用户注册
	public static function ThUserRegister($username,$iUserSourceID,$password = '') {
		$subSql = array();
		$csubSql=array();
		$userbaseinfo = array(
			'sPhone' => $username,
			'sMail' => '',
			'sPassWord' => $password,
			'iUsertypeID'=>0,
			'iOperationNum'=>0,
			'dCreatTime'=>date('Y-m-d H:i:s'),
			'sNick'=>$username,
			'dPasswordLastChangeTime'=>'0000-00-00 00:00:00',
			'dPayPassWordLastChangeTime'=>'',
			'bMailCheck'=>'',
			'bPhoneCheck'=>'',
			'dVIPBeginTime'=>'',
			'dVIPEndTime'=>'',
			'iUserSourceID'=>$iUserSourceID,
			'sUserCity'=>'',
			'iOpenID'=>'',
			'iCurScore'=>0,
			'iTotalScore'=>0,
			'iHuoDongID'=>0
		);
		foreach($userbaseinfo as $key =>$v)
		{
			$subSql[] = "'".mysql_escape_string($v)."'";
			$csubSql[]=$key;
		}
		$SQL = sprintf("insert into  {{e_userbaseinfo}} (%s) VALUES(%s)",implode(',', $csubSql),implode(',', $subSql));
		if(DbUtil::execute($SQL)){
			$sql = sprintf("select * FROM {{e_userbaseinfo}} where sPhone='%s'", mysql_escape_string($username));
			$result = DbUtil::queryRow($sql);
			$subSql = array();
			$csubSql=array();
			$userbaseinfo = array(
				'iUserID' => $result['iUserID'],
				'sPayPassWord' => '',
				'mAccountMoney' => '0.0000',
				'iRewardScore'=>0,
				'sUserIdentityID'=>'',
				'getVoucherFlag'=>0,
				'dCreateTime'=>date('Y-m-d H:i:s'),
			);
			foreach($userbaseinfo as $key =>$v)
			{
				$subSql[] = "'".mysql_escape_string($v)."'";
				$csubSql[]=$key;
			}
			$SQL = sprintf("insert into  {{e_useraccountinfo}} (%s) VALUES(%s)",implode(',', $csubSql),implode(',', $subSql));
			if(DbUtil::execute($SQL)) {
				return $result;
			}
		}
	}

}