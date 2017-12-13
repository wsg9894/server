<?php

/**
 * SiteController - 通用控制器
 * @author luzhizhong
 * @version V1.0
 */

Yii::import('application.modules.scoreStore.models.process.*');

class SiteController extends Controller
{
	/**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array(
		);
	}
	
	/**
	 * 商品列表-积分商城入口
	 */
	public function actionIndex()
	{
		$curPage = isset($_REQUEST['cur_page']) ? $_REQUEST['cur_page'] : 1;	//当前页
		$tab = (isset($_REQUEST['tab']) and in_array($_REQUEST['tab'],array(0,1))) ? $_REQUEST['tab'] : 0;	//0-商城页；1-赚取页
		$uSessInfo = UserProcess::getLoginSessionInfo();
		
		//如果是微信用户，则要获取Openid
		if(Weixin::isWxVisitor() and empty($uSessInfo['openid']))
		{
			$retuUrl = Yii::app()->params['baseUrl'].'project/index.php?r=scoreStore/Site';
			UserProcess::getOpenidOnOAuth($retuUrl);
			exit(0);
		}
		
		//用户信息
		$uInfo = array();
		if(!empty($uSessInfo['iUserID']))
		{
			$uInfo = UserProcess::getUInfo($uSessInfo['iUserID'], array('iUserID','sPhone','iTotalScore','iCurScore'));
			
			if(!empty($uSessInfo['openid']))
			{
				//邀请用户E豆赠送处理
				UserProcess::addScoreForInvite($uSessInfo['iUserID'], $uSessInfo['openid']);
			}
		}
		
		//商品列表
		$selFields = array('gid', 'name', 'starttime', 'endtime', 'price', 'total', 'exchanges', 'list_pic', 'limit_day');
		$ret = GoodsProcess::getGoodsList($selFields, 'status=1', $curPage, Yii::app()->params['pageInfo']['pageSize_goodsList']);
		foreach ($ret['goodsList'] as $index => &$goodsInfo)
		{
			$goodsInfo['startline'] = strtotime($goodsInfo['starttime']);
			$goodsInfo['endline'] = strtotime($goodsInfo['endtime']);
			
			//今日已兑换数量
			$filter = sprintf("source=-1 AND union_id=%d AND LEFT(createtime,10)='%s'", $goodsInfo['gid'], GeneralFunc::getCurDate());
			$goodsInfo['sales_day'] = ScoreProcess::getScoreLogCount($filter);
			
			//今日可兑换数量
			$remainInventory = $goodsInfo['total']-$goodsInfo['exchanges'];	//剩余库存
			if($goodsInfo['limit_day']>0)
			{
				$todayAllows = $goodsInfo['limit_day']-$goodsInfo['sales_day'];
				$goodsInfo['allow_day'] = $remainInventory < $todayAllows ? $remainInventory : $todayAllows;
			}else{
				$goodsInfo['allow_day'] = $remainInventory;
			}
/* 
			if($goodsInfo['allow_day']<=0)
			{
				//如果商品抢光，前端显示‘抢兑结束’，此处为临时处理，后续需优化
				//$goodsInfo['endline'] = $goodsInfo['startline'] + 1;
			}
 */			
		}
		
		//微信分享配置
		$wxShareConf = ScoreProcess::getScoreConf(Yii::app()->params['scoreConf']['keyConf']['wxShare']);

		//获取微信JS-SDK权限验证签名，用于‘分享’操作
		$wx = new Weixin();
		$signPackage = $wx->getSignPackage();
		
		Yii::app()->smarty->assign('BASEURL',Yii::app()->params['baseUrl']);
		Yii::app()->smarty->assign('UINFO',$uInfo);
		Yii::app()->smarty->assign('GOODSLIST',$ret['goodsList']);
		Yii::app()->smarty->assign('PAGEINFO',$ret['pageInfo']);
		Yii::app()->smarty->assign('TAB',$tab);
		Yii::app()->smarty->assign('WXSHARECONF',$wxShareConf);
		Yii::app()->smarty->assign('SIGNPACKAGE',$signPackage);
		
		Yii::app()->smarty->display('scoreStore/index.html');
	}
	
	/**
	 * 商品详情
	 */
	public function actionGoodsDetail()
	{
		$gid = empty($_REQUEST['gid']) ? 0 : $_REQUEST['gid'];
		$uSessInfo = UserProcess::getLoginSessionInfo();

		$goodsInfo = GoodsProcess::getGoodsInfo($gid);
		
		$goodsInfo['desc'] = str_replace("\n", "<br>", $goodsInfo['desc']);
		$goodsInfo['explain'] = str_replace("\n", "<br>", $goodsInfo['explain']);
		
		Yii::app()->smarty->assign('BASEURL',Yii::app()->params['baseUrl']);
		Yii::app()->smarty->assign('GOODSINFO',$goodsInfo);
		Yii::app()->smarty->assign('USESSINFO',$uSessInfo);
		Yii::app()->smarty->display('scoreStore/goodsDetail.html');
	}
	
	/**
	 * 我的E豆
	 */
	public function actionMyScore()
	{
		$uid = empty($_REQUEST['uid']) ? 0 : $_REQUEST['uid'];
		$uSessInfo = UserProcess::getLoginSessionInfo();
		
		if(empty($uSessInfo) or empty($uSessInfo['iUserID']))
		{
			GeneralFunc::alert('请重新登录...');
			GeneralFunc::gotoUrl(Yii::app()->params['baseUrl'].'usercenter/login.html?go='.Yii::app()->params['baseUrl'].'project/index.php?r=scoreStore/Site');
		}
		if($uid != $uSessInfo['iUserID'])
		{
			GeneralFunc::alert('非法操作...');
			GeneralFunc::gotoUrl('index.php?r=scoreStore/Site');
		}
		
		$uInfo = UserProcess::getUInfo($uSessInfo['iUserID'], array('iUserID','sPhone','iTotalScore','iCurScore'));
		
		//我的兑换记录
		$selFields = array('lid', 'score', 'desc', 'union_id', 'createtime');
		$myExchangeLogList = ScoreProcess::getScoreLogList($selFields, 'uid='.$uid.' AND source=-1', 1, 100);
		foreach ($myExchangeLogList['sLogList'] as $index => &$logInfo)
		{
			$goodsFields = array('coupon_id', 'voucher_id', 'third_id');
			$logInfo['goodsInfo'] = GoodsProcess::getGoodsInfo($logInfo['union_id'], $goodsFields);
		}
		
		//我的积分日志
		$selFields = array('lid', 'score', 'desc', 'union_id', 'createtime');
		$myScoreLogList = ScoreProcess::getScoreLogList($selFields, 'uid='.$uid.' AND source>0', 1, 100);
		
		Yii::app()->smarty->assign('BASEURL',Yii::app()->params['baseUrl']);
		Yii::app()->smarty->assign('UINFO',$uInfo);
		Yii::app()->smarty->assign('MYEXCHANGELOGLIST',$myExchangeLogList['sLogList']);
		Yii::app()->smarty->assign('MYSCORELOGLIST',$myScoreLogList['sLogList']);
		Yii::app()->smarty->display('scoreStore/myScore.html');
	}

	/**
	 * 邀请好友（V1.0，at 20161117弃用）
	 */
	public function actionInvite_V10()
	{
		GeneralFunc::gotoUrl('index.php?r=scoreStore/Site/Invite');
		exit(0);
	}
	
	/**
	 * 邀请好友回应（此入口被投诉，已暂停使用，at 20161026）
	 */
	public function actionInviteRespond()
	{
		$uid = empty($_REQUEST['uid']) ? '' : $_REQUEST['uid'];
		GeneralFunc::gotoUrl('index.php?r=scoreStore/Site/InviteRespond_Step1&uid='.$uid);
	}
	
	/**
	 * 邀请好友回应（之前的邀请回应Url被投诉，所以重新设置一个）
	 */
	public function actionInviteRespond_New()
	{
		$uid = empty($_REQUEST['uid']) ? '' : $_REQUEST['uid'];
		GeneralFunc::gotoUrl('index.php?r=scoreStore/Site/InviteRespond_Step1&uid='.$uid);
	}
	/**
	 * 微信操作回调-好友邀请回应（获取openid、记录邀请关联、跳转至公众号）
	 */
	public function actionWXCodeBack_InviteRespond()
	{
		$code = $_GET['code'];
		$uid = $_GET['uid'];
		
		$wx = new Weixin();
		$openid = $wx->reqOpenidOnOAuth($code);
		$sessObj = new Session();
		$sessObj->add('openid', $openid);
		
		if(!empty($uid) and !empty($openid))
		{
			//记录邀请关联
			UserProcess::createInviteRecord($uid, $openid);
		}
		
		//跳转至公众号
		//$url = Yii::app()->params['wxConf']['WXHomeUrl'];
		$url = 'index.php?r=scoreStore/Site/Subscribe';
		GeneralFunc::gotoUrl($url);
	}

	/**
	 * 在线选座订单
	 */
	public function actionBuyTicket()
	{
		$scoreConf = ScoreProcess::getScoreConf(Yii::app()->params['scoreConf']['keyConf']['sourceDesc']);
		$btScoreConf = $scoreConf[Yii::app()->params['scoreConf']['sourceConf']['buy_ticket_online']['source_id']];
		$btScoreConf['actExplain'] = str_replace("\n", "<br>", $btScoreConf['actExplain']);
		$btScoreConf['actProcess'] = str_replace("\n", "<br>", $btScoreConf['actProcess']);
		
		Yii::app()->smarty->assign('BASEURL',Yii::app()->params['baseUrl']);
		Yii::app()->smarty->assign('SCORECONF',$btScoreConf);
		Yii::app()->smarty->display('scoreStore/buyTicket.html');
	}
	/**
	 * 绑定有礼
	 */
	public function actionBandingWX()
	{
		$scoreConf = ScoreProcess::getScoreConf(Yii::app()->params['scoreConf']['keyConf']['sourceDesc']);
		$btScoreConf = $scoreConf[Yii::app()->params['scoreConf']['sourceConf']['banding_wx']['source_id']];
		$btScoreConf['actExplain'] = str_replace("\n", "<br>", $btScoreConf['actExplain']);
		$btScoreConf['actProcess'] = str_replace("\n", "<br>", $btScoreConf['actProcess']);
	
		Yii::app()->smarty->assign('BASEURL',Yii::app()->params['baseUrl']);
		Yii::app()->smarty->assign('SCORECONF',$btScoreConf);
		Yii::app()->smarty->display('scoreStore/bandingWX.html');
	}
	/**
	 * 活动券订单
	 */
	public function actionJoinAct()
	{
		$scoreConf = ScoreProcess::getScoreConf(Yii::app()->params['scoreConf']['keyConf']['sourceDesc']);
		$btScoreConf = $scoreConf[Yii::app()->params['scoreConf']['sourceConf']['join_act']['source_id']];
		$btScoreConf['actExplain'] = str_replace("\n", "<br>", $btScoreConf['actExplain']);
		$btScoreConf['actProcess'] = str_replace("\n", "<br>", $btScoreConf['actProcess']);
	
		Yii::app()->smarty->assign('BASEURL',Yii::app()->params['baseUrl']);
		Yii::app()->smarty->assign('SCORECONF',$btScoreConf);
		Yii::app()->smarty->display('scoreStore/joinAct.html');
	}
	
	/**
	 * 关注公众号
	 */
	public function actionSubscribe()
	{
		Yii::app()->smarty->display('scoreStore/subscribe.html');
	}
	
	/**
	 * 邀请好友
	 */
	public function actionInvite()
	{
		$uSessInfo = UserProcess::getLoginSessionInfo();
		
		if(empty($uSessInfo) or empty($uSessInfo['iUserID']))
		{
			GeneralFunc::alert('您还没有登录呦');
			GeneralFunc::gotoUrl(Yii::app()->params['baseUrl'].'usercenter/login.html?go='.Yii::app()->params['baseUrl'].'project/index.php?r=scoreStore/Site/Invite');
			exit(0);
		}
		//生成海报图片
		$postersPic = UserProcess::getInvitePostersPath($uSessInfo['iUserID']);
		
		if(empty($postersPic))
		{
			//重新生成
			$qrUrl = Yii::app()->params['baseUrl']."project/index.php?r=scoreStore/Site/InviteRespond_Step1&uid=".$uSessInfo['iUserID']."&times=".time();
			$postersPic = UserProcess::createInvitePosters($uSessInfo['iUserID'], $qrUrl);
		}
	
		//获取微信JS-SDK权限验证签名，用于‘分享’操作
		$wx = new Weixin();
		$signPackage = $wx->getSignPackage();
		Yii::app()->smarty->assign('SIGNPACKAGE',$signPackage);
		
		//微信分享配置
		$wxShareConf = ScoreProcess::getScoreConf(Yii::app()->params['scoreConf']['keyConf']['wxShare']);
		Yii::app()->smarty->assign('WXSHARECONF',$wxShareConf);
		
		//邀请说明图
		$postersInfo = ScoreProcess::getPubPostersInfo();
		Yii::app()->smarty->assign('INVITEREXPLAINPIC',$postersInfo['inviter_explain_pic']);
		
		Yii::app()->smarty->assign('UID',$uSessInfo['iUserID']);
		Yii::app()->smarty->assign('BASEURL',Yii::app()->params['baseUrl']);
		Yii::app()->smarty->assign('ISWXVISITOR',Weixin::isWxVisitor());
		Yii::app()->smarty->assign('POSTERSPIC',$postersPic);
		Yii::app()->smarty->display('scoreStore/invite.html');
	}
	
	/**
	 * 邀请好友回应-Step1
	 * 1、给邀请者加奖品
	 * 2、验证登录状态
	 */
	public function actionInviteRespond_Step1()
	{
		$uid = empty($_REQUEST['uid']) ? '' : $_REQUEST['uid'];
		$inviterUInfo = UserProcess::getUInfo($uid, array('iUserID', 'sPhone', 'iOpenID'));
		if(empty($inviterUInfo))
		{
			GeneralFunc::alert('非法操作...');
			GeneralFunc::gotoUrl('index.php?r=scoreStore/Site');
			exit(0);
		}
		
		$postersInfo = ScoreProcess::getPubPostersInfo();
		
		//第一步、给邀请者加奖品
		if(FALSE == PrizeProcess::getSendPrizeFlag($uid, $postersInfo['pid']))
		{
			$voucherInfo = VoucherProcess::createVoucher($postersInfo['inviter_prize']
					, $uid
					, sprintf(Yii::app()->params['batchInfo']['scoreStore']['invite'], 'xjq', $postersInfo['inviter_prize']));
			//微信/短信通知
			$inviterUInfo['iOpenID'] = empty($inviterUInfo['iOpenID']) ? UserProcess::getOpenidByPhone($inviterUInfo['sPhone']) : $inviterUInfo['iOpenID'];
			
			if(empty($inviterUInfo['iOpenID']))
			{
				//短信通知
				SMSProcess::sendDayuSMS($inviterUInfo['sPhone'], array('prize'=>$voucherInfo['sVoucherName']), 'SMS_25305344');
			}else{
				//微信通知
				$wx = new Weixin();
				$data = array(
						'first' => array('value'=>'看电影有了新伙伴，恭喜您获得分享优惠券！', 'color'=>'#173177'),
						'keyword1' => array('value'=>$voucherInfo['sVoucherName'], 'color'=>'#173177'),
						'keyword2' => array('value'=>$voucherInfo['sVoucherPassWord'], 'color'=>'#173177'),
						'keyword3' => array('value'=>substr($voucherInfo['dVaildEndTime'], 0, 10), 'color'=>'#173177'),
						'remark' => array('value'=>'已绑定至您的账户，快去兑换电影票啦！', 'color'=>'#173177')
				);
				$wx->sendTemplateMsg($inviterUInfo['iOpenID']
									,'Abry2F50FogkqLsFWMZOR8sb67mygQ0CUxVY-bAVywA'
									,Yii::app()->params['baseUrl'].'cinema/movieselect.php'
									,$data);
			}
			//记录奖品日志
			PrizeProcess::addPrizeLog($uid, $postersInfo['pid'], $postersInfo['inviter_prize']);
		}
		
		//第二步、验证被邀请者登录状态
		$uSessInfo = UserProcess::getLoginSessionInfo();
		if(empty($uSessInfo) or empty($uSessInfo['iUserID']))
		{
			//未登录，完成登录操作（如果是微信访客，直接通过获取openid获取用户信息，从而完成登录）
			if(Weixin::isWxVisitor() and empty($uSessInfo['openid']))
			{
				//微信用户，未获取openid,需重新获取（此过程会自动完成登录）
				$retuUrl = Yii::app()->params['baseUrl'].'project/index.php?r=scoreStore/Site/InviteRespond_Step1&uid='.$uid;
				UserProcess::getOpenidOnOAuth($retuUrl);
			}
		}
		
		//第三步、验证是否领取过奖品（二次进入则直接跳转‘领取成功’页）
		if(FALSE==empty($uSessInfo['iUserID'])
				and PrizeProcess::getSendPrizeFlag($uSessInfo['iUserID'], $postersInfo['pid'], $prizeType=2))
		{
			GeneralFunc::gotoUrl('index.php?r=scoreStore/Site/InviteRespond_Step2&uid='.$uid);
			exit(0);
		}
		
		//海报图片
		Yii::app()->smarty->assign('POSTERSPIC',$postersInfo['invitee_posters_pic']);
		
		//现金券总金额
		$voucherTAmount = PrizeProcess::getPrizeTotalAmountByVoucherIDs($postersInfo['invitee_prize']);
		Yii::app()->smarty->assign('VOUCHERTAMOUNT',$voucherTAmount);
		
		Yii::app()->smarty->assign('INVITERUINFO',$inviterUInfo);
		Yii::app()->smarty->assign('USESSINFO',$uSessInfo);
		Yii::app()->smarty->assign('BASEURL',Yii::app()->params['baseUrl']);
		Yii::app()->smarty->display('scoreStore/inviteRespond_Step1.html');
	}
	
	/**
	 * 邀请好友回应-Step2
	 */
	public function actionInviteRespond_Step2()
	{
		$uid = empty($_REQUEST['uid']) ? '' : $_REQUEST['uid'];
		if(empty($uid) or FALSE==UserProcess::isRegByUid($uid))
		{
			GeneralFunc::alert('非法操作...');
			GeneralFunc::gotoUrl('index.php?r=scoreStore/Site');
			exit(0);
		}
		
		$uSessInfo = UserProcess::getLoginSessionInfo();
		if(empty($uSessInfo) or empty($uSessInfo['iUserID']))
		{
			GeneralFunc::alert('请登录...');
			GeneralFunc::gotoUrl('index.php?r=scoreStore/Site/InviteRespond_Step1&uid='.$uid);
			exit(0);
		}
		
		$postersInfo = ScoreProcess::getPubPostersInfo();
		
		$prizeList = array();
		if($uid!=$uSessInfo['iUserID'])
		{
			if(FALSE == PrizeProcess::getSendPrizeFlag($uSessInfo['iUserID'], $postersInfo['pid'], $prizeType=2))
			{
				//第一步、给被邀请者加奖品
				$inviteeVoucherArr = explode(',', $postersInfo['invitee_prize']);
				foreach ($inviteeVoucherArr as $index => $voucherID)
				{
					$ret = VoucherProcess::createVoucher($voucherID
							, $uSessInfo['iUserID']
							, sprintf(Yii::app()->params['batchInfo']['scoreStore']['invite'], 'xjq', $voucherID));
					$prizeList[] = array('iVoucherID'=>$voucherID, 'sVoucherName'=>$ret['sVoucherName'], 'mVoucherMoney'=>round($ret['mVoucherMoney']));
				}
				
				//第二步、记录奖品日志
				PrizeProcess::addPrizeLog($uSessInfo['iUserID'], $postersInfo['pid'], $postersInfo['invitee_prize'], $prizeType=2);
			}
				
			//第三步、记录邀请关联
			UserProcess::createInviteRecord($uid, $uSessInfo['iUserID']);
		}
		
		if(empty($prizeList))
		{
			$prizeList = PrizeProcess::getPrizeListByVoucherIDs($postersInfo['invitee_prize']);
		}
		
		Yii::app()->smarty->assign('BASEURL',Yii::app()->params['baseUrl']);
		Yii::app()->smarty->assign('PRIZELIST',$prizeList);
		Yii::app()->smarty->display('scoreStore/inviteRespond_Step2.html');
	}
	
}