<?php

/**
 * SiteController - 通用控制器
 * @author luzhizhong
 * @version V1.0
 */

Yii::import('application.modules.scoreStore.models.process.*');
Yii::import('application.models.process.lib.alipay.lib.*');
Yii::import('application.models.process.lib.alipay.Alipay');

/**
 * SiteFilter - 过滤器
 */
class SiteFilter extends CFilter
{
	/**
	 * 动作被执行之前应用的逻辑
	 */
	protected function preFilter($filterChain)
	{
		
// 		echo ($filterChain->action->id);
// 		exit(0);
		GLOBAL $uSessInfo;
		
		//登录验证
		$uSessInfo = UserProcess::getLoginSessionInfo();
		if(empty($uSessInfo) or empty($uSessInfo['iUserID']))
		{
			GeneralFunc::alert('您还没有登录呦');
			GeneralFunc::gotoUrl('/usercenter/login.html?go='.Yii::app()->params['baseUrl'].'project/index.php?r=scoreStore/Site/Tasks&iUserSourceID=77');
			exit(0);
		}
		return true;
	}

	/**
	 * 动作被执行之后应用的逻辑
	 */
	protected function postFilter ($filterChain)
	{
	}
}//end class

class SiteController extends Controller
{
	public function filters()
	{
		return array(
			array(
				'application.filters.SiteFilter + Test',
			),
		);
	}
	public function filterAccessControl($filterChain)
	{
		$filterChain->run();
	}
	
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
		}

		//banner位列表
		$selFields = array('bid', 'name', 'pic', 'url');
		$filter = sprintf("is_del=0 AND `status`=1 AND starttime<=NOW() AND endtime>=NOW()");
		$bannerList = BannerProcess::getBannerList($selFields, $filter);
		
		//推荐位（2个）
		$recommendInfo = ScoreProcess::getScoreConf(Yii::app()->params['scoreConf']['keyConf']['recommend']);
		
		//小E说
		$tipInfo = ScoreProcess::getScoreConf(Yii::app()->params['scoreConf']['keyConf']['tip']);
		
		//商品列表
		$selFields = array('gid', 'list_name', 'list_pic', 'price', 'extra_price');
		$filter = sprintf('status=1 AND gid NOT IN(%d,%d)', $recommendInfo['firstGoodsID'], $recommendInfo['secondGoodsID']);
		$ret = GoodsProcess::getGoodsList($selFields, $filter, $curPage, Yii::app()->params['pageInfo']['pageSize_goodsList']);
		foreach ($ret['goodsList'] as $index => &$goodsInfo)
		{
			$goodsInfo['allow_day'] = GoodsProcess::getAllowExchangeNum($goodsInfo['gid']);	//今日可兑换数量
			$goodsInfo['extra_price'] = floatval($goodsInfo['extra_price']);				//去掉价格的后缀0
		}
		
		//获取微信JS-SDK权限验证签名，用于‘分享’操作
		$wx = new Weixin();
		$signPackage = $wx->getSignPackage();
		
		Yii::app()->smarty->assign('BASEURL',Yii::app()->params['baseUrl']);
		Yii::app()->smarty->assign('UINFO',$uInfo);
		Yii::app()->smarty->assign('BANNERLIST',$bannerList);
		Yii::app()->smarty->assign('RECOMMENDINFO',$recommendInfo);
		Yii::app()->smarty->assign('TIPINFO',$tipInfo);
		Yii::app()->smarty->assign('GOODSLIST',$ret['goodsList']);
		Yii::app()->smarty->assign('PAGEINFO',$ret['pageInfo']);
		Yii::app()->smarty->assign('SIGNPACKAGE',$signPackage);
		
		Yii::app()->smarty->display('scoreStore/index.html');
	}

	/**
	 * 商品详情
	 */
	public function actionGoodsDetail()
	{
		//用户信息
		$gid = empty($_REQUEST['gid']) ? 0 : $_REQUEST['gid'];
		$uSessInfo = UserProcess::getLoginSessionInfo();
		Yii::app()->smarty->assign('USESSINFO',$uSessInfo);
		
		//商品信息
		$goodsInfo = GoodsProcess::getGoodsInfo($gid);
		$goodsInfo['desc'] = str_replace("\n", "<br>", $goodsInfo['desc']);
		$goodsInfo['explain'] = str_replace("\n", "<br>", $goodsInfo['explain']);
		$goodsInfo['extra_price'] = floatval($goodsInfo['extra_price']);		//去掉价格的后缀0
		Yii::app()->smarty->assign('GOODSINFO',$goodsInfo);
		
		//兑换错误信息
		$uid = empty($uSessInfo['iUserID']) ? 0 : $uSessInfo['iUserID'];
		$errNo = GoodsProcess::isExchange($uid, $gid);
		$exchangeErrInfo = GoodsProcess::getExchangeErrInfo($gid, $errNo);
		Yii::app()->smarty->assign('EXCHANGEERRINFO',$exchangeErrInfo);
		
		//是否为虚拟商品
		$isVirGoods = GoodsProcess::isVirGoods($goodsInfo);
		Yii::app()->smarty->assign('ISVIRGOODS',$isVirGoods);
		
		Yii::app()->smarty->assign('BASEURL',Yii::app()->params['baseUrl']);
		Yii::app()->smarty->display('scoreStore/goodsDetail.html');
	}
	
	/**
	 * 商品订单详情
	 */
	public function actiongoodsOrder()
	{
		$gid = empty($_REQUEST['gid']) ? 0 : $_REQUEST['gid'];
		$aid = empty($_REQUEST['aid']) ? 0 : $_REQUEST['aid'];		//收货地址id
		$uSessInfo = UserProcess::getLoginSessionInfo();
		if(empty($uSessInfo) or empty($uSessInfo['iUserID']))
		{
			GeneralFunc::alert('您还没有登录呦');
			GeneralFunc::gotoUrl('/usercenter/login.html?go='.Yii::app()->params['baseUrl'].'project/index.php?r=scoreStore/Site/goodsOrder1&gid='.$gid.'&iUserSourceID=77');
			exit(0);
		}
		$uInfo = array('iUserID'=>$uSessInfo['iUserID']
				, 'mAccountMoney'=>UserProcess::getmAccountMoney($uSessInfo['iUserID']));
		Yii::app()->smarty->assign('UINFO',$uInfo);
		
		//是否为微信端访问
		$isWXVisitor = Weixin::isWxVisitor();
		Yii::app()->smarty->assign('ISWXVISITOR',$isWXVisitor);
		
		//商品信息
		$goodsInfo = GoodsProcess::getGoodsInfo($gid);
		$goodsInfo['extra_price'] = floatval($goodsInfo['extra_price']);		//去掉价格的后缀0
		Yii::app()->smarty->assign('GOODSINFO',$goodsInfo);
		
		//收货地址
		$userAddress = array();
		if(!empty($aid))
		{
			$userAddress = UserProcess::getUserAddressByAid($aid);
			if($userAddress['uid']!=$uSessInfo['iUserID'])
			{
				$userAddress = UserProcess::getUserDefAddress($uSessInfo['iUserID']);
			}
		}else{
			$userAddress = UserProcess::getUserDefAddress($uSessInfo['iUserID']);
		}
		Yii::app()->smarty->assign('USERADDRESS',$userAddress);
		
		//用户可购买的商品数量
		$userAllowExchangeNum = UserProcess::getAllowExchangeNum($uSessInfo['iUserID'], $gid);
		Yii::app()->smarty->assign('USERALLOWEXCHANGENUM',$userAllowExchangeNum);
		
		Yii::app()->smarty->display('scoreStore/goodsOrder1.html');
	}
	
	/**
	 * 商品订单详情
	 */
	public function actiongoodsOrder1()
	{
		$gid = empty($_REQUEST['gid']) ? 0 : $_REQUEST['gid'];
		$aid = empty($_REQUEST['aid']) ? 0 : $_REQUEST['aid'];		//收货地址id

		$uSessInfo = UserProcess::getLoginSessionInfo();
		if(empty($uSessInfo) or empty($uSessInfo['iUserID']))
		{
			GeneralFunc::alert('您还没有登录呦');
			GeneralFunc::gotoUrl('/usercenter/login.html?go='.Yii::app()->params['baseUrl'].'project/index.php?r=scoreStore/Site/goodsOrder1&gid='.$gid.'&iUserSourceID=77');
			exit(0);
		}
		$uInfo = array('iUserID'=>$uSessInfo['iUserID']
				, 'mAccountMoney'=>UserProcess::getmAccountMoney($uSessInfo['iUserID']));
		Yii::app()->smarty->assign('UINFO',$uInfo);
	
		//是否为微信端访问
		$isWXVisitor = Weixin::isWxVisitor();
		Yii::app()->smarty->assign('ISWXVISITOR',$isWXVisitor);

		//商品信息
		$goodsInfo = GoodsProcess::getGoodsInfo($gid);
		$goodsInfo['extra_price'] = floatval($goodsInfo['extra_price']);		//去掉价格的后缀0
		$goodsInfo['attribute'] = json_decode($goodsInfo['attribute'], true);	//商品类别

		$goodsInfo['attrValueNum'] = array(0, 0, 0);							//商品子类别数量
		if(isset($goodsInfo['attribute'])&&!empty($goodsInfo['attribute'])){
			foreach ($goodsInfo['attribute'] as $key => $attrInfo)
			{
				$goodsInfo['attrValueNum'][$key] = count($attrInfo['value']);
			}
		}

		Yii::app()->smarty->assign('GOODSINFO',$goodsInfo);
		
		//收货地址
		$userAddress = array();
		if(!empty($aid))
		{
			$userAddress = UserProcess::getUserAddressByAid($aid);
			if($userAddress['uid']!=$uSessInfo['iUserID'])
			{
				$userAddress = UserProcess::getUserDefAddress($uSessInfo['iUserID']);
			}
		}else{
			$userAddress = UserProcess::getUserDefAddress($uSessInfo['iUserID']);
		}
		Yii::app()->smarty->assign('USERADDRESS',$userAddress);
	
		//用户可购买的商品数量
		$userAllowExchangeNum = UserProcess::getAllowExchangeNum($uSessInfo['iUserID'], $gid);
		Yii::app()->smarty->assign('USERALLOWEXCHANGENUM',$userAllowExchangeNum);

		Yii::app()->smarty->display('scoreStore/goodsOrder1.html');
	}
	/**
	 * 收货地址列表
	 */
	public function actionUserAddressList()
	{
		$gid = empty($_REQUEST['gid']) ? 0 : $_REQUEST['gid'];
		$aid = empty($_REQUEST['aid']) ? 0 : $_REQUEST['aid'];
		
		$uSessInfo = UserProcess::getLoginSessionInfo();
		if(empty($uSessInfo) or empty($uSessInfo['iUserID']))
		{
			GeneralFunc::alert('您还没有登录呦');
			GeneralFunc::gotoUrl('/usercenter/login.html?go='.Yii::app()->params['baseUrl'].'project/index.php?r=scoreStore/Site/goodsOrder1&gid='.$gid.'&iUserSourceID=77');
			exit(0);
		}
		
		//收货地址列表
		$userAddressList = UserProcess::getUserAddressList($uSessInfo['iUserID']);
		Yii::app()->smarty->assign('USERADDRESSLIST', $userAddressList);
		
		Yii::app()->smarty->assign('GID',$gid);
		Yii::app()->smarty->assign('AID',$aid);
		Yii::app()->smarty->display('scoreStore/userAddressList.html');
	}
	
	/**
	 * 新增地址列表
	 */
	public function actionAddAddress()
	{
		$gid = empty($_REQUEST['gid']) ? 0 : $_REQUEST['gid'];
		//$uid = empty($_REQUEST['uid']) ? 0 : $_REQUEST['uid'];
		
		$uSessInfo = UserProcess::getLoginSessionInfo();
		if(empty($uSessInfo) or empty($uSessInfo['iUserID']))
		{
			GeneralFunc::alert('您还没有登录呦');
			GeneralFunc::gotoUrl('/usercenter/login.html?go='.Yii::app()->params['baseUrl'].'project/index.php?r=scoreStore/Site/goodsOrder1&gid='.$gid.'&iUserSourceID=77');
			exit(0);
		}
		
		if(!empty($_REQUEST['subFlag']) and $_REQUEST['subFlag']==1)
		{
			if(!isset($_REQUEST['receiver_name']) or !isset($_REQUEST['receiver_phone']) or !isset($_REQUEST['areaInfo']) or !isset($_REQUEST['address']))
			{
				GeneralFunc::gotoUrl('/project/index.php?r=scoreStore/Site/goodsOrder1&gid='.$gid);
				exit(0);
			}
				
			//表单提交，新增地址入库
			$addressInfo = array('uid' => $uSessInfo['iUserID']
					,'receiver_name' => $_REQUEST['receiver_name']
					,'receiver_phone' => $_REQUEST['receiver_phone']
					,'area' => $_REQUEST['areaInfo']
					,'address' => $_REQUEST['address']
			);
			$aid = UserProcess::addUserAddress($addressInfo);
			
			if(isset($_REQUEST['is_def']) or 1==UserProcess::getUserAddressCount($uSessInfo['iUserID']))
			{
				//设置默认地址
				UserProcess::setUserDefAddress($uSessInfo['iUserID'], $aid);
			}
			
			GeneralFunc::gotoUrl('/project/index.php?r=scoreStore/Site/UserAddressList&gid='.$gid);
			exit(0);
		}
		
		//省份列表
		$provinceList = array();
		foreach (Yii::app()->params['provinceConf'] as $key => $value)
		{
			$provinceList[] = array('id'=>$key, 'name'=>$value);
		}
		Yii::app()->smarty->assign('PROVINCELIST',$provinceList);
		
		Yii::app()->smarty->assign('GID',$gid);
		Yii::app()->smarty->display('scoreStore/addAddress.html');
	}
	
	/**
	 * 修改地址列表
	 */
	public function actionUpdateAddress()
	{
		$gid = empty($_REQUEST['gid']) ? 0 : $_REQUEST['gid'];
		$aid = empty($_REQUEST['aid']) ? 0 : $_REQUEST['aid'];
	
		$uSessInfo = UserProcess::getLoginSessionInfo();
		if(empty($uSessInfo) or empty($uSessInfo['iUserID']))
		{
			GeneralFunc::alert('您还没有登录呦');
			GeneralFunc::gotoUrl('/usercenter/login.html?go='.Yii::app()->params['baseUrl'].'project/index.php?r=scoreStore/Site/goodsOrder1&gid='.$gid.'&iUserSourceID=77');
			exit(0);
		}
	
		if(!empty($_REQUEST['subFlag']) and $_REQUEST['subFlag']==1)
		{
			//表单提交，修改地址
			$updateAddressInfo = array('receiver_name' => $_REQUEST['receiver_name']
					,'receiver_phone' => $_REQUEST['receiver_phone']
					,'area' => $_REQUEST['areaInfo']
					,'address' => $_REQUEST['address']
			);
			UserProcess::updateUserAddress($aid, $updateAddressInfo);
				
			if(isset($_REQUEST['is_def']))
			{
				//设置默认地址
				UserProcess::setUserDefAddress($uSessInfo['iUserID'], $aid);
			}
				
			GeneralFunc::gotoUrl('/project/index.php?r=scoreStore/Site/UserAddressList&gid='.$gid);
			exit(0);
		}
		//地址信息
		$addressInfo = UserProcess::getUserAddressByAid($aid);
		Yii::app()->smarty->assign('ADDRESSINFO',$addressInfo);
		
		//省份列表
		$provinceList = array();
		foreach (Yii::app()->params['provinceConf'] as $key => $value)
		{
			$provinceList[] = array('id'=>$key, 'name'=>$value);
		}
		Yii::app()->smarty->assign('PROVINCELIST',$provinceList);
	
		Yii::app()->smarty->assign('GID',$gid);
		Yii::app()->smarty->display('scoreStore/updateAddress.html');
	}
	
	/**
	 * 商品兑换
	 */

	public function object_array($array) {
		if(is_object($array)) {
			$array = (array)$array;
		} if(is_array($array)) {
			foreach($array as $key=>$value) {
				$array[$key] = self::object_array($value);
			}
		}
		return $array;
	}

	public function actionGoodsExchange()
	{
		$gid = empty($_REQUEST['gid']) ? 0 : $_REQUEST['gid'];
		$aid = empty($_REQUEST['aid']) ? 0 : $_REQUEST['aid'];
		$payNum = empty($_REQUEST['payNum']) ? 1 : $_REQUEST['payNum'];		//兑换数量
		$payType = empty($_REQUEST['payType']) ? '' : $_REQUEST['payType'];		//支付类型
		$payPrice = empty($_REQUEST['payPrice']) ? '' : $_REQUEST['payPrice'];		//实际支付的金额
		$sourceType = empty($_REQUEST['sourceType']) ? '' : trim($_REQUEST['sourceType'],",");		//商品类型

		if(!empty($_REQUEST['subFlag']) and $_REQUEST['subFlag']!=1)
		{

			GeneralFunc::gotoUrl('/project/index.php?r=scoreStore/Site/goodsOrder1&gid='.$gid.'&aid='.$aid);
			exit(0);
		}
		//第一步、验证用户登录状态
		$uSessInfo = UserProcess::getLoginSessionInfo();
		if(empty($uSessInfo) or empty($uSessInfo['iUserID']))
		{
			GeneralFunc::alert('您还没有登录呦');
			GeneralFunc::gotoUrl('/usercenter/login.html?go='.Yii::app()->params['baseUrl'].'project/index.php?r=scoreStore/Site/goodsOrder1&gid='.$gid.'&iUserSourceID=77');
			exit(0);
		}
//
//		//第二步、判断是否可兑换商品
		$isExchangeRet = GoodsProcess::isExchange($uSessInfo['iUserID'], $gid, $payNum);
		if(1!=$isExchangeRet)
		{
			GeneralFunc::alert('兑换失败');
			GeneralFunc::gotoUrl(Yii::app()->params['baseUrl'].'project/index.php?r=scoreStore/Site/goodsOrder1&gid='.$gid);
			exit(0);
		}
//
		$orderNo = '';
		$goodsInfo = GoodsProcess::getGoodsInfo($gid);
		//减商品类别库存
		if(isset($goodsInfo['attribute'])&&!empty($goodsInfo['attribute'])){
			$goodType = explode(',',$sourceType);
			$goodsInfo['attribute'] = json_decode($goodsInfo['attribute'],true);
			foreach($goodsInfo['attribute'] as &$v){
				foreach($v['value'] as &$val){
					foreach($goodType as $val1){
						if($val['size'] == $val1){
							$val['num'] = $val['num'] - $payNum;
						}
					}
				}
			}
			$goodsInfo['attribute'] = json_encode($goodsInfo['attribute'],true);
		}

		$userInfo = UserProcess::getUInfo($uSessInfo['iUserID'], array('iUserID', 'iCurScore', 'sPhone'));
		$addressInfo = UserProcess::getUserAddressByAid($aid);		//收货地址
		//v2.5版本订单增加商品类型字段attribute-anqing
		$orderInfo = array('gid'=>$gid, 'goods_price'=>$goodsInfo['price'], 'goods_extra_price'=>$payPrice
				, 'goods_num'=>$payNum, 'goods_tprice'=>$goodsInfo['price']*$payNum, 'goods_extra_tprice'=>$payPrice*$payNum
				, 'order_amount'=>($payPrice*$payNum)+$goodsInfo['send_cost'], 'order_name'=>$goodsInfo['name']
				, 'uid'=>$uSessInfo['iUserID'], 'user_addressid'=>$aid, 'order_address'=>$addressInfo['area'].' '.$addressInfo['address'], 'order_pay_type'=>$payType,'attribute'=>$sourceType
		);
//
//		//第三步、商品兑换处理
		if(GoodsProcess::isVirGoods($goodsInfo))
		{
			//虚拟商品（关联电影卡、现金券、娱乐券），直接兑换
			GoodsProcess::exchangeVirGoods($userInfo, $goodsInfo, $orderInfo);
		}else{
			//实物商品
			GoodsProcess::exchangePhyGoods($userInfo, $goodsInfo, $orderInfo, $payType);
		}
//
		GeneralFunc::gotoUrl(Yii::app()->params['baseUrl'].'project/index.php?r=scoreStore/Site/myScore');
	}
	
	/**
	 * 取消订单
	 */
	public function actionCancelOrder()
	{
		$oid = empty($_REQUEST['oid']) ? 0 : $_REQUEST['oid'];
	
		//第一步、验证用户登录状态
		$uSessInfo = UserProcess::getLoginSessionInfo();
		if(empty($uSessInfo) or empty($uSessInfo['iUserID']))
		{
			GeneralFunc::alert('您还没有登录呦');
			GeneralFunc::gotoUrl('/usercenter/login.html?go='.Yii::app()->params['baseUrl'].'project/index.php?r=scoreStore/Site&iUserSourceID=77');
			exit(0);
		}
	
		//第二步、判断是否为订单用户本人
		$orderInfo = GOrderProcess::getOrderInfoByOid($oid, array('uid'));
		if(empty($orderInfo) or $orderInfo['uid']!=$uSessInfo['iUserID'])
		{
			GeneralFunc::alert('您无权操作呦');
			GeneralFunc::gotoUrl('/project/index.php?r=scoreStore/Site/myScore');
			exit(0);
		}
		
		//第三步、订单取消处理
		$ret = GOrderProcess::cancelOrder($oid);
		GeneralFunc::gotoUrl('/project/index.php?r=scoreStore/Site/myScore');
	}
	
	/**
	 * 支付交易返回-支付宝
	 */
	public function actionReturnUrlOnAlipay()
	{
		$alipayNotify = new AlipayNotifyMobile(Alipay::getBaseConfig());
		$verifyResult = $alipayNotify->verifyReturn();
		
		if($verifyResult)
		{
			$orderNo = $_REQUEST['out_trade_no'];    	//商户订单号
			$tradeNo = $_REQUEST['trade_no'];			//支付宝交易号
			$result = $_REQUEST['result'];				//交易状态
			
			if($result=="success")
			{
				//写入日志
				GeneralFunc::writeLog('Alipay Return Success...', Yii::app()->getRuntimePath().'/pay/');
				GeneralFunc::writeLog('orderNo:'.$orderNo, Yii::app()->getRuntimePath().'/pay/');
			}else{
				GeneralFunc::writeLog('Alipay Return Failed...', Yii::app()->getRuntimePath().'/pay/');
			}
		}
		
		GeneralFunc::gotoUrl('/project/index.php?r=scoreStore/Site/myScore');
	}
	/**
	 * 支付交易通知-支付宝
	 */
	public function actionNotifyUrlOnAliPay()
	{
		$alipayNotify = new AlipayNotifyMobile(Alipay::getBaseConfig());
		$verifyResult = $alipayNotify->verifyNotify();
		if($verifyResult)
		{
			$notifyData = @simplexml_load_string($_POST['notify_data'],NULL,LIBXML_NOCDATA);
			$notifyDataArrs = json_decode(json_encode($notifyData),true);
			
			if (!empty($notifyDataArrs['payment_type']))
			{
				$orderNo = $notifyDataArrs['out_trade_no'];		//商户订单号
				$tradeNo = $notifyDataArrs['trade_no'];			//支付宝交易号
				$tradeStatus = $notifyDataArrs['trade_status'];	//交易状态
				$totalFee = $notifyDataArrs['total_fee'];
				if($tradeStatus == 'TRADE_FINISHED' or $tradeStatus == 'TRADE_SUCCESS')
				{
					GeneralFunc::writeLog('Alipay Notify Success...', Yii::app()->getRuntimePath().'/pay/');
					GeneralFunc::writeLog('orderNo:'.$orderNo, Yii::app()->getRuntimePath().'/pay/');
					GeneralFunc::writeLog('tradeNo:'.$tradeNo, Yii::app()->getRuntimePath().'/pay/');

					//支付成功，1、修改订单状态；2、发放卡/券；3、添加商品订单日志
					GOrderProcess::paySuccess($orderNo, $tradeNo);
					
					echo "success";
					exit(0);
				}
			}
		}
		
		GeneralFunc::writeLog('Alipay Notify Failed...', Yii::app()->getRuntimePath());
		echo "fail";
	}
	
	/**
	 * 任务列表
	 */
	public function actionTasks_bak()
	{
		$uSessInfo = UserProcess::getLoginSessionInfo();
		if(empty($uSessInfo) or empty($uSessInfo['iUserID']))
		{
			GeneralFunc::alert('您还没有登录呦');
			GeneralFunc::gotoUrl('/usercenter/login.html?go='.Yii::app()->params['baseUrl'].'project/index.php?r=scoreStore/Site/Tasks&iUserSourceID=77');
			exit(0);
		}

		//是否为微信端访问
		$isWXVisitor = Weixin::isWxVisitor();
		Yii::app()->smarty->assign('ISWXVISITOR',$isWXVisitor);

		//如果是微信用户，则要获取Openid
		if($isWXVisitor and empty($uSessInfo['openid']))
		{
			$retuUrl = Yii::app()->params['baseUrl'].'project/index.php?r=scoreStore/Site';
			UserProcess::getOpenidOnOAuth($retuUrl);
			exit(0);
		}

		//分享邀请
		$postersInfo = ScoreProcess::getPubPostersInfo();		//发布中的海报信息
		$inviteCountToday = UserProcess::getInviteCountToday($uSessInfo['iUserID']);	//今日邀请成功数量
		Yii::app()->smarty->assign('INVITERDAYLIMIT',$postersInfo['inviter_point_daylimit']);		//邀请每日上限
		Yii::app()->smarty->assign('INVITECOUNTTODAY',$inviteCountToday);
		
		//购票消费（今日通过购票的获豆数量）
		$filter = sprintf("uid=%d AND (source=%d or source=%d) AND LEFT(createtime,10)='%s'"
				, $uSessInfo['iUserID']
				, Yii::app()->params['scoreConf']['sourceConf']['buy_ticket_online']['source_id']
				, Yii::app()->params['scoreConf']['sourceConf']['join_act']['source_id']
				, GeneralFunc::getCurDate());
		
		$costScoreToday = ScoreProcess::getScoreSum($filter);
		Yii::app()->smarty->assign('COSTSCORETODAY',$costScoreToday);
		Yii::app()->smarty->assign('COSTSCORELIMIT',(floor($costScoreToday/100)+1)*100);
		
		//绑定
		$isBinding = 0;
		if($isWXVisitor and !empty($uSessInfo['openid']))
		{
			//微信访客
			$isBinding = UserProcess::isWXBinding($uSessInfo['openid']);
		}elseif(!empty($uSessInfo['sPhone'])){
			//浏览器访客
			$openID = UserProcess::getOpenidByPhone($uSessInfo['sPhone']);
			if(!empty($openID))
			{
				$isBinding = 1;
			}
		}
		Yii::app()->smarty->assign('ISBINDING',$isBinding);
		
		//签到
		$isSignInToday = ScoreProcess::isSignInToday($uSessInfo['iUserID']);
		Yii::app()->smarty->assign('ISSIGNINTODAY',$isSignInToday);
		if(FALSE==$isSignInToday)
		{
			//签到得豆配置
			$signInConf = ScoreProcess::getSourcePoint(Yii::app()->params['scoreConf']['sourceConf']['sign_in']['source_id']);
			Yii::app()->smarty->assign('SIGNINCONF',$signInConf);
				
			//用户签到记录（累计多少天、累计多少豆）
			$userSignInCount = ScoreProcess::getUserSignInCount($uSessInfo['iUserID']);
			Yii::app()->smarty->assign('USERSIGNINCOUNT',$userSignInCount);
		}
		
		//用户信息
		$uInfo = UserProcess::getUInfo($uSessInfo['iUserID'], array('iUserID', 'iCurScore'));
		Yii::app()->smarty->assign('UINFO',$uInfo);

		Yii::app()->smarty->display('scoreStore/tasks.html');
	}
	
	/**
	 * 我的E豆
	 */
	public function actionMyScore()
	{
		$uSessInfo = UserProcess::getLoginSessionInfo();
		if(empty($uSessInfo) or empty($uSessInfo['iUserID']))
		{
			GeneralFunc::alert('您还没有登录呦');
			GeneralFunc::gotoUrl('/usercenter/login.html?go='.Yii::app()->params['baseUrl'].'project/index.php?r=scoreStore/Site/myScore&iUserSourceID=77');
			exit(0);
		}
				
		//我的信息
		$uInfo = UserProcess::getUInfo($uSessInfo['iUserID'], array('iUserID','sPhone','iTotalScore','iCurScore'));
		Yii::app()->smarty->assign('UINFO',$uInfo);
		
		//我的订单
		$myOrderList = GOrderProcess::getOrderListByUid($uSessInfo['iUserID']
				, array('oid', 'order_name', 'gid', 'goods_num', 'goods_tprice', 'order_status', 'express_no', 'createtime'));
		foreach ($myOrderList as $index => &$orderInfo)
		{
			$goodsFields = array('coupon_id', 'voucher_id', 'third_id');
			$orderInfo['goodsInfo'] = GoodsProcess::getGoodsInfo($orderInfo['gid'], $goodsFields);
			$orderInfo['countDown'] = (time()-strtotime($orderInfo['createtime']))>=900 ? 0 : 900-(time()-strtotime($orderInfo['createtime']));
		}
		
		Yii::app()->smarty->assign('MYORDERLIST',$myOrderList);

		//我的积分日志
		$selFields = array('lid', 'score', 'desc', 'union_id', 'createtime');
		$myScoreLogList = ScoreProcess::getScoreLogList($selFields, 'uid='.$uInfo['iUserID'].' AND source>0', 1, 100);
		Yii::app()->smarty->assign('MYSCORELOGLIST',$myScoreLogList['sLogList']);
		
		Yii::app()->smarty->assign('BASEURL',Yii::app()->params['baseUrl']);
		Yii::app()->smarty->display('scoreStore/myScore.html');
	}
	
	/**
	 * 我的订单详情
	 */
	public function actionOrderDetail()
	{
		$oid = empty($_REQUEST['oid']) ? 0 : $_REQUEST['oid'];
		$uSessInfo = UserProcess::getLoginSessionInfo();
		if(empty($uSessInfo) or empty($uSessInfo['iUserID']))
		{
			GeneralFunc::alert('您还没有登录呦');
			GeneralFunc::gotoUrl('/usercenter/login.html?go='.Yii::app()->params['baseUrl'].'project/index.php?r=scoreStore/Site/MyScore&iUserSourceID=77');
			exit(0);
		}
	
		//订单信息
		$orderInfo = GOrderProcess::getOrderInfoByOid($oid);
		if(empty($orderInfo))
		{
			GeneralFunc::alert('订单不存在');
			GeneralFunc::gotoUrl('/project/index.php?r=scoreStore/Site/MyScore');
			exit(0);
		}
		$orderInfo['attribute'] = explode(',',$orderInfo['attribute']);
		Yii::app()->smarty->assign('ORDERINFO',$orderInfo);
		
		//商品信息
		$goodsInfo = GoodsProcess::getGoodsInfo($orderInfo['gid']);

		Yii::app()->smarty->assign('GOODSINFO',$goodsInfo);
		
		//收货地址
		$addressInfo = UserProcess::getUserAddressByAid($orderInfo['user_addressid']);
		Yii::app()->smarty->assign('ADDRESSINFO',$addressInfo);
		
		Yii::app()->smarty->display('scoreStore/orderDetail.html');
	}
	
	/**
	 * 订单支付（仅用于个人中心的‘二次支付’）
	 */
	public function actionOrderPay()
	{
		$oid = empty($_REQUEST['oid']) ? 0 : $_REQUEST['oid'];
		$uSessInfo = UserProcess::getLoginSessionInfo();
		if(empty($uSessInfo) or empty($uSessInfo['iUserID']))
		{
			GeneralFunc::alert('您还没有登录呦');
			GeneralFunc::gotoUrl('/usercenter/login.html?go='.Yii::app()->params['baseUrl'].'project/index.php?r=scoreStore/Site/MyScore&iUserSourceID=77');
			exit(0);
		}
		
		$orderInfo = GOrderProcess::getOrderInfoByOid($oid);
		if(empty($orderInfo) or $orderInfo['uid']!=$uSessInfo['iUserID'])
		{
			GeneralFunc::alert('非法操作');
			GeneralFunc::gotoUrl('/project/index.php?r=scoreStore/Site/MyScore');
			exit(0);
		}
		
		if($orderInfo['order_status']!=1)
		{
			//只有在‘待付款’状态下才可以支付
			GeneralFunc::alert('您的订单已超时');
			GeneralFunc::gotoUrl('/project/index.php?r=scoreStore/Site/MyScore');
			exit(0);
		}
		
		//是否为微信端访问
		$isWXVisitor = Weixin::isWxVisitor();
		
		//支付处理
		if($isWXVisitor)
		{
			//微信支付
			$url = "/wxpays/pay/jsapi_ss.php?orderNo=".$orderInfo['order_no'];
			header("Location: ".$url);
		}else{
			//支付宝支付
			$alipay = new Alipay();
			$alipay->doSubmitMobile($orderInfo['order_no'], $orderInfo['order_name'], $orderInfo['order_amount']);
		}
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
			GeneralFunc::gotoUrl('/usercenter/login.html?go='.Yii::app()->params['baseUrl'].'project/index.php?r=scoreStore/Site/Invite&iUserSourceID=77');
			exit(0);
		}
		
		//生成海报图片
		$postersPic = UserProcess::getInvitePostersPath($uSessInfo['iUserID']);
		if(empty($postersPic))
		{
			//邀请Url
			//$inviteUrl = Yii::app()->params['baseUrl']."project/index.php?r=scoreStore/Site/InviteRespond_Step1&uid=".$uSessInfo['iUserID']."&times=".time();
			$inviteUrl = Yii::app()->params['baseUrl']."project/index.php?r=scoreStore/Site/InviteRespond&uid=".$uSessInfo['iUserID']."&times=".time();

			//重新生成
			$postersPic = UserProcess::createInvitePosters($uSessInfo['iUserID'], $inviteUrl);
		}
	
		//获取微信JS-SDK权限验证签名，用于‘分享’操作
		$wx = new Weixin();
		$signPackage = $wx->getSignPackage();
		Yii::app()->smarty->assign('SIGNPACKAGE',$signPackage);
		
		//微信分享配置
		$pubPostersInfo = ScoreProcess::getPubPostersInfo();
		Yii::app()->smarty->assign('PUBPOSTERSINFO',$pubPostersInfo);
		
		//邀请说明图
		$postersInfo = ScoreProcess::getPubPostersInfo();
		Yii::app()->smarty->assign('INVITEREXPLAINPIC',$postersInfo['inviter_explain_pic']);
	
		Yii::app()->smarty->assign('UID',$uSessInfo['iUserID']);
		Yii::app()->smarty->assign('BASEURL',Yii::app()->params['baseUrl']);
		Yii::app()->smarty->assign('ISWXVISITOR',Weixin::isWxVisitor());
		Yii::app()->smarty->assign('POSTERSPIC',$postersPic);
		Yii::app()->smarty->assign('BGURL_BG_SHARE',urlencode(Yii::app()->params['baseUrl'].'project/index.php?r=scoreStore/Site/InviteRespond_Step1&uid='.$uSessInfo['iUserID']));	//非微信端分享专用
		Yii::app()->smarty->display('scoreStore/invite.html');
	}
	
	/**
	 * 邀请好友（用于非微信端分享）
	 */
	public function actionInviteShare()
	{
		$uid = empty($_REQUEST['uid']) ? '' : $_REQUEST['uid'];
				
		//邀请者海报
		$postersPic = UserProcess::getInvitePostersPath($uid);
		if(empty($postersPic))
		{
			GeneralFunc::alert('邀请海报不存在...');
			GeneralFunc::gotoUrl('index.php?r=scoreStore/Site');
			exit(0);
		}
		
		Yii::app()->smarty->assign('POSTERSPIC',$postersPic);
		Yii::app()->smarty->display('scoreStore/inviteShare.html');
	}
	/**
	 * 邀请好友回应（V2.1）
	 * 1、引导至关注公众号
	 * 2、邀请者加券：被邀请者点击触发
	 *    邀请者加豆：被邀请者绑定微信号（前提需关注）
	 *    被邀请者加券、加豆：完成绑定有礼（绑定微信号，前提需关注）
	 */
	public function actionInviteRespond()
	{
		$uid = empty($_REQUEST['uid']) ? '' : $_REQUEST['uid'];
		$inviterUInfo = UserProcess::getUInfo($uid, array('iUserID', 'sPhone', 'iOpenID'));
		if(empty($inviterUInfo))
		{
			GeneralFunc::alert('非法操作...');
			GeneralFunc::gotoUrl('index.php?r=scoreStore/Site');
			exit(0);
		}
		
		//第一步、记录邀请关联
		$uSessInfo = UserProcess::getLoginSessionInfo();
		if(Weixin::isWxVisitor())
		{
			if(empty($uSessInfo['openid']))
			{
				//如果是微信用户+没有记录Openid，则要获取Openid
				$retuUrl = Yii::app()->params['baseUrl'].'project/index.php?r=scoreStore/Site/InviteRespond&uid='.$uid;
				UserProcess::getOpenidOnOAuth($retuUrl);
				exit(0);
			}else{
				//记录邀请关联
				UserProcess::createInviteRecord($uid, $uSessInfo['openid']);
			}
		}
		
		//第二步、给邀请者加奖品
		$postersInfo = ScoreProcess::getPubPostersInfo();
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
		
		//第三步，跳转至‘关注公众号’
		GeneralFunc::gotoUrl(Yii::app()->params['wxConf']['WXHomeUrl']);
	}
	
	/**
	 * 邀请好友回应-Step1（V2.0流程）
	 * 1、给邀请者加奖品
	 * 2、验证登录状态
	 */
	public function actionInviteRespond_Step1()
	{
// 		GeneralFunc::alert('该活动已结束...');
// 		GeneralFunc::gotoUrl('index.php?r=scoreStore/Site');
// 		exit(0);
	
		//跳转至新的邀请回应流程
		$uid = empty($_REQUEST['uid']) ? '' : $_REQUEST['uid'];
		GeneralFunc::gotoUrl('index.php?r=scoreStore/Site/InviteRespond&uid='.$uid);
		exit(0);
		
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
	
		//第三步、验证是否领取过奖品（二次进入、自己领取自己则直接跳转‘领取成功’页）
		if(FALSE==empty($uSessInfo['iUserID'])
			and ($uSessInfo['iUserID']==$uid or PrizeProcess::getSendPrizeFlag($uSessInfo['iUserID'], $postersInfo['pid'], $prizeType=2)))
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
		Yii::app()->smarty->display('scoreStore/inviteRespond_Step1.html');
	}
	
	/**
	 * 邀请好友回应-Step2（V2.0流程）
	 */
	public function actionInviteRespond_Step2()
	{
// 		GeneralFunc::alert('该活动已结束...');
// 		GeneralFunc::gotoUrl('index.php?r=scoreStore/Site');
// 		exit(0);

		//跳转至新的邀请回应流程
		$uid = empty($_REQUEST['uid']) ? '' : $_REQUEST['uid'];
		GeneralFunc::gotoUrl('index.php?r=scoreStore/Site/InviteRespond&uid='.$uid);
		exit(0);
		
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
		$sendPrizeFlag = FALSE;		//是否已兑换
// 		$isNewUser = FALSE;			//是否新用户
		
		//lzz update at 2017-03-09，增加注册验证门槛（MD5(phone+当前日期+userid)）
		if($uid!=$uSessInfo['iUserID'])
		{
			$sendPrizeFlag = PrizeProcess::getSendPrizeFlag($uSessInfo['iUserID'], $postersInfo['pid'], $prizeType=2);
			if(FALSE == $sendPrizeFlag)
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
			
// 			//第四步、如果是新注册，给邀请者加E豆
// 			ScoreProcess::addScore($uid, 14, 1, '', $uSessInfo['iUserID']);
// 			$isNewUser = TRUE;
		}
	
		if(empty($prizeList))
		{
			$prizeList = PrizeProcess::getPrizeListByVoucherIDs($postersInfo['invitee_prize']);
		}
		
		//现金券总金额
		$voucherTAmount = PrizeProcess::getPrizeTotalAmountByVoucherIDs($postersInfo['invitee_prize']);
		Yii::app()->smarty->assign('VOUCHERTAMOUNT',$voucherTAmount);
		
		//推荐商品（两个）
		$recommendInfo = ScoreProcess::getScoreConf(Yii::app()->params['scoreConf']['keyConf']['recommend']);	//推荐位
		$selFields = array('gid', 'list_name', 'list_pic', 'price');
		$filter = sprintf('status=1 AND gid NOT IN(%d,%d)', $recommendInfo['firstGoodsID'], $recommendInfo['secondGoodsID']);
		$ret = GoodsProcess::getGoodsList($selFields, 'status=1', 1, 2);
		Yii::app()->smarty->assign('GOODSLIST',$ret['goodsList']);
		
		Yii::app()->smarty->assign('SENDPRIZEFLAG',$sendPrizeFlag);
// 		Yii::app()->smarty->assign('ISNEWUSER',$isNewUser);
		Yii::app()->smarty->assign('PRIZELIST',$prizeList);
		Yii::app()->smarty->display('scoreStore/inviteRespond_Step2.html');
	}
	
	/**
	 * 专题
	 */
	public function actionZT()
	{
		$t = empty($_REQUEST['t']) ? '38' : $_REQUEST['t'];
		switch($t)
		{
			case '38':			//38节专题
				$viewHtml = 'scoreStore/zt/38.html';
				break;
			case '520':			//520专题
				$viewHtml = 'scoreStore/zt/520.html';
				break;
			case 'ffrw':		//非凡任务
				if(!empty($_REQUEST['p']))
				{
					$viewHtml = sprintf('scoreStore/zt/ffrw/%s.html', $_REQUEST['p']);
				}else{
					$viewHtml = 'scoreStore/zt/ffrw/index.html';
				}
				break;
			case 'apes':		//猩球崛起3 add at 20170830
				$viewHtml = 'scoreStore/zt/apes.html';
				break;
			case 'term':		//开学季大酬宾  add at 20170830
				$viewHtml = 'scoreStore/zt/term.html';
				break;
			case '815':			//2017中秋节  add at 20170930
				$viewHtml = 'scoreStore/zt/815.html';
				break;
			default:
				$viewHtml = 'scoreStore/zt/38.html';
				break;
		}
		
		//获取微信JS-SDK权限验证签名，用于‘分享’操作
		$wx = new Weixin();
		$signPackage = $wx->getSignPackage();
		Yii::app()->smarty->assign('SIGNPACKAGE',$signPackage);
		
		Yii::app()->smarty->assign('BASEURL',Yii::app()->params['baseUrl']);
		Yii::app()->smarty->display($viewHtml);
	}
	
	/**
	 * 任务列表
	 */
	public function actionTasks()
	{
		$uSessInfo = UserProcess::getLoginSessionInfo();
		if(empty($uSessInfo) or empty($uSessInfo['iUserID']))
		{
			GeneralFunc::alert('您还没有登录呦');
			GeneralFunc::gotoUrl('/usercenter/login.html?go='.Yii::app()->params['baseUrl'].'project/index.php?r=scoreStore/Site/Tasks&iUserSourceID=77');
			exit(0);
		}
	
		//是否为微信端访问
		$isWXVisitor = Weixin::isWxVisitor();
		Yii::app()->smarty->assign('ISWXVISITOR',$isWXVisitor);
	
		//如果是微信用户，则要获取Openid
		if($isWXVisitor and empty($uSessInfo['openid']))
		{
			$retuUrl = Yii::app()->params['baseUrl'].'project/index.php?r=scoreStore/Site';
			UserProcess::getOpenidOnOAuth($retuUrl);
			exit(0);
		}
	
		//分享邀请
		$postersInfo = ScoreProcess::getPubPostersInfo();		//发布中的海报信息
		$inviteCountToday = UserProcess::getInviteCountToday($uSessInfo['iUserID']);	//今日邀请成功数量
		Yii::app()->smarty->assign('INVITERDAYLIMIT',$postersInfo['inviter_point_daylimit']);		//邀请每日上限
		Yii::app()->smarty->assign('INVITECOUNTTODAY',$inviteCountToday);
	
		//购票消费（今日通过购票的获豆数量）
		$filter = sprintf("uid=%d AND (source=%d or source=%d) AND LEFT(createtime,10)='%s'"
				, $uSessInfo['iUserID']
				, Yii::app()->params['scoreConf']['sourceConf']['buy_ticket_online']['source_id']
				, Yii::app()->params['scoreConf']['sourceConf']['join_act']['source_id']
				, GeneralFunc::getCurDate());
	
		$costScoreToday = ScoreProcess::getScoreSum($filter);
		Yii::app()->smarty->assign('COSTSCORETODAY',$costScoreToday);
		Yii::app()->smarty->assign('COSTSCORELIMIT',(floor($costScoreToday/100)+1)*100);
	
		//绑定
		$isBinding = 0;				//是否绑定
		$isAccountBinding = 0;		//是否绑定此账号（手机号）
		if($isWXVisitor and !empty($uSessInfo['openid']))
		{
			//微信访客
			//$isBinding = UserProcess::isWXBinding($uSessInfo['openid']);
			$tempUInfo = UserProcess::getUInfoByOpenid($uSessInfo['openid'], array('sPhone'));
			if(!empty($tempUInfo))
			{
				$isBinding = 1;
				$isAccountBinding = $uSessInfo['sPhone']==$tempUInfo['sPhone'] ? 1 : 0;
			}
		}elseif(!empty($uSessInfo['sPhone'])){
			//浏览器访客
			$openID = UserProcess::getOpenidByPhone($uSessInfo['sPhone']);
			if(!empty($openID))
			{
				$isBinding = 1;
			}
		}
		Yii::app()->smarty->assign('ISBINDING',$isBinding);
		Yii::app()->smarty->assign('ISACCOUNTBINDING',$isAccountBinding);
	
		//签到
		$isSignInToday = ScoreProcess::isSignInToday($uSessInfo['iUserID']);
		Yii::app()->smarty->assign('ISSIGNINTODAY',$isSignInToday);
		if(FALSE==$isSignInToday)
		{
			//签到得豆配置
			$signInConf = ScoreProcess::getSourcePoint(Yii::app()->params['scoreConf']['sourceConf']['sign_in']['source_id']);
			Yii::app()->smarty->assign('SIGNINCONF',$signInConf);
	
			//用户签到记录（累计多少天、累计多少豆）
			$userSignInCount = ScoreProcess::getUserSignInCount($uSessInfo['iUserID']);
			Yii::app()->smarty->assign('USERSIGNINCOUNT',$userSignInCount);
		}
	
		//用户信息
		$uInfo = UserProcess::getUInfo($uSessInfo['iUserID'], array('iUserID', 'iCurScore'));
		Yii::app()->smarty->assign('UINFO',$uInfo);
	
		Yii::app()->smarty->display('scoreStore/tasks.html');
	}
	
}