<?php

/**
 * TestController - 专用于测试
 * @author luzhizhong
 * @version V1.0
 */

Yii::import('application.models.data.db.*');
Yii::import('application.modules.scoreStore.models.data.*');
Yii::import('application.modules.scoreStore.models.process.*');

class TestController extends Controller
{
	/**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array(
		);
	}
		
	public function actionIndex()
	{
		$orderNo = 'SS-BC562F938B91F7884462D787';
		$tradeNo = '2017012421001003590260736685';
		$ret = GOrderProcess::paySuccess($orderNo, $tradeNo);
		echo $ret;
		exit(0);
		$areaList = CityProcess::getRegionList(103);
		print_r($areaList);
		//runtime目录
		//echo Yii::app()->getRuntimePath();
		//工程目录
		//echo Yii::app()->basePath;
	}
	/**
	 * 二维码测试
	 */
	public function actionQR()
	{
		$logoQR = 'images/qr/1212007_logo.png';
		$ret = UserProcess::createInvitePosters($logoQR);
		echo $ret;
		exit(0);
		//简单二维码
		//QRcode::png('http://m.epiaowang.com/project/index.php?r=scoreStore/Test/WX');
		
		$value = 'http://m.epiaowang.com/project/index.php?r=scoreStore/Test/WX'; //二维码Url
		$errorCorrectionLevel = 'L';//容错级别
		$matrixPointSize = 6;//生成图片大小
		
		//生成二维码图片
		$logo = Yii::app()->basePath.'/../../public/images/logo.jpg';	
		$QR = Yii::app()->getRuntimePath().'/qrcode.png';	//已经生成的原始二维码图
		$QR_logo = Yii::app()->getRuntimePath().'/qrcode_logo.png';	//已经生成的原始二维码图
		
		QRcode::png($value, $QR, $errorCorrectionLevel, $matrixPointSize, 2);
		
		if ($logo !== FALSE)
		{
			$QR = imagecreatefromstring(file_get_contents($QR));
			$logo = imagecreatefromstring(file_get_contents($logo));
			$QR_width = imagesx($QR);//二维码图片宽度
			$QR_height = imagesy($QR);//二维码图片高度
			$logo_width = imagesx($logo);//logo图片宽度
			$logo_height = imagesy($logo);//logo图片高度
			$logo_qr_width = $QR_width / 5;
			$scale = $logo_width/$logo_qr_width;
			$logo_qr_height = $logo_height/$scale;
			$from_width = ($QR_width - $logo_qr_width) / 2;
			
			//重新组合图片并调整大小
			imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width,
			$logo_qr_height, $logo_width, $logo_height);
		}
		//输出图片
		imagepng($QR, $QR_logo);
		
		echo '<img src="helloweba.png">';
	}
	
	/**
	 * 微信操作测试-回调（获取openid）
	 */
	public function actionWX_Back()
	{
		print_r($_SESSION);
		//echo '/';
		//echo 'uid：'.$uid;
		
	}
	/**
	 * 微信操作测试
	 */
	public function actionWX()
	{
		$wx = new Weixin();
/*		$data = array(
				'first' => array('value'=>'看电影有了新伙伴，恭喜您获得分享优惠券！', 'color'=>'#173177'),
				'keyword1' => array('value'=>'test1', 'color'=>'#173177'),
				'keyword2' => array('value'=>'test2', 'color'=>'#173177'),
				'keyword3' => array('value'=>'test3', 'color'=>'#173177'),
				'remark' => array('value'=>'已绑定至您的账户，快去兑换电影票啦！', 'color'=>'#173177')
		);
		$wx->sendTemplateMsg('oFLd4jn-siudirZAi_CBTumTIQbY'
				,'Abry2F50FogkqLsFWMZOR8sb67mygQ0CUxVY-bAVywA'
				,Yii::app()->params['baseUrl'].'cinema/movieselect.php'
				,$data);
		echo 'ok';
		exit(0);
		
		$url = 'http://m.epiaowang.com';

		$data = array(
				'first' => array('value'=>'您好，本期板凳订阅活动已推送！', 'color'=>'#173177'),
				'orderMoneySum' => array('value'=>30, 'color'=>'#173177'),
				'orderProductName' => array('value'=>'通兑券', 'color'=>'#173177')
		);
		
		
		$ret = $wx->sendTemplateMsg('oFLd4js7tKv5ePqXBadJz1c80T9c','kbIjKIXqLy-T_giMqqQYTPl9rfVIzsglgLUev6ZKWZg',$url,$data);
		echo $ret;
		exit(0);
		//是否为微信访客
		if(Weixin::isWxVisitor())
		{
			echo 'Yes';
		}else{
			echo 'No';
		}
	
		//获取Access Token
		$token =  $wx->getAccessToken();
		echo $token;
		

		//获取openid
		$retuUrl = 'http://m.epiaowang.com/project/index.php?r=scoreStore/Test/WX_Back';
		UserProcess::getOpenidOnOAuth($retuUrl);
		exit(0);

*/
				
 	
		//获取用户信息
		$openid = 'oFLd4js7tKv5ePqXBadJz1c80T9c';
		$uInfo = $wx->getUInfo($openid);
		$uInfo['subscribe_time'] = date('Y-m-d H:i:s', $uInfo['subscribe_time']);
		print_r($uInfo);
/* 		
		//是否关注了我们的服务号
		if($wx->isFocus($openid))
		{
			echo 'Yes';
		}else{
			echo 'No';
		}
*/
	
	}
	/**
	 * 积分操作测试
	 */
	public function actionScore()
	{		
		
		$wxShareConf = ScoreProcess::getScoreConf(Yii::app()->params['scoreConf']['keyConf']['wxShare']);
		print_r($wxShareConf);
		exit(0);
		$uid = 1212007;
		echo ScoreProcess::addScore($uid, 3, 1);
		exit(0);
		
		//关注微信，给邀请者加积分
		$openid = 'oFLd4js7tKv5ePqXBadJz1c80T9c';
		$uid = UserProcess::getInviteUsersByOpenid($openid);
		if(!empty($uid))
		{
			$ret = ScoreProcess::addScore($uid, 11, 1, '', $openid);
			echo $ret;
		}
		//结束
		
		echo 'inot';
		exit(0);		

		
// 		echo UserProcess::createInviteRecord(1212007, 'sf8wer9828934');
// 		exit(0);
// 		$voucherId = 162;
// 		$vcBaseInfo = VoucherProcess::getVoucherBaseInfoByVoucherID($voucherId);
// 		print_r($vcBaseInfo);
// 		exit(0);
		//$couponInfo = array("sCheckNo"=>'7190927762',"sPassWord"=>'977570');
		//echo CouponProcess::insertCouponSalesInfo($couponInfo);
		
		//$endTime = date("Y-m-d H:i:s",strtotime("+30 day"));
		//$cpBaseInfo = CouponProcess::getBaseCouponInfoByCouponID(353, array('mSalePrice'));
		//echo CouponProcess::createCouponSalesInfo(353, 1, $endTime, 1212007, $cpBaseInfo['mSalePrice']);
		
		echo GoodsProcess::isExchange(1212007, 5);
		exit(0);
		//include(Yii::app()->basePath.'/../../core/CoreFuncs.php');
		//include(Yii::app()->basePath.'/../../inc/db/coupon/DB_Coupon_Info.php');
		//include(Yii::app()->basePath.'/../../inc/db/DB_Base.php');
		//include(Yii::app()->basePath.'/../../inc/db/DB.php');
		//include('E://web/EPiao/server/core/CoreFuncs.php');
		//include (Yii::app()->basePath.'/../../admin/admininit.php');
		
// 		$uid = $_REQUEST['uid'];
// 		$source = $_REQUEST['source'];
// 		$union_id = $_REQUEST['union_id'];
		//$ret = ScoreProcess::addScore(1212007, 5, 1, '奖励你的，哈哈哈，123456', '', 10);
		$ret = ScoreProcess::addScore(1212007, 4, 1, '', '67', 19.9);
		//$ret = ScoreProcess::addScore(1212007, 2, 1, '', 'DD-1208-106D6FFB74C17D53C91CA51B');
		echo $ret;
		
// 		$score = array('point'=>array('94'=>11,'93'=>9,'92'=>8,'91'=>18));
// 		$score = addslashes(json_encode($score));
// 		$sql = 'update {{s_scoresourceconf}} set score="'.$score.'" where sid=4';
// 		DbUtil::execute($sql);
// 		echo 'ok';
// 		$uInfo = UserProcess::getUInfo(1212007, array('sPhone','sMail','sPassWord','iScore'));
// 		print_r($uInfo);
		
// 		$userDBObj = new E_UserbaseinfoDB();
// 		$test = $userDBObj->attributeNames();
// 		print_r($test);
		exit(0);
		
		$res = ScoreProcess::addScoreLog(1212007, '13621083819', 1, 10, 10020, '邀请13621081212');
		echo $res;
	}
	
	/**
	 * 其他
	 */
	public function actionOthers()
	{
		$wx = new Weixin();
		$openid = $_REQUEST['openid'];
		
		$uSessInfo = UserProcess::getOpenidSessionInfo();
		if(empty($openid))
		{
			$retuUrl = 'http://m.epiaowang.com/project/index.php?r=scoreStore/Test/Others$$uid=1212007';
			$callbackUrl = 'http://m.epiaowang.com/project/index.php?r=Tools/GetWXOpenID&setSession=1&retuUrl='.$retuUrl;
			
			$wx->reqCodeOnOAuth(urlencode($callbackUrl));
		}else{
			echo 'ok';
		}
		
		echo $_REQUEST['uid'].'/'.$_REQUEST['setSession'];
		print_r($_SESSION);
		exit(0);		
		
		
		$a_openid = 'oFLd4jvT4aHQdRSom2Nqvmo-m8CI';
		$reqUrl = sprintf("http://m.epiaowang.com/project/index.php?r=scoreStore/Ajax/GetInviteUsers&openid=%s",$a_openid);
		$uid = file_get_contents($reqUrl);
		if(!empty($uid))
		{
			$reqUrl = sprintf("http://m.epiaowang.com/project/index.php?r=scoreStore/Ajax/AddScore_Focus&openid=%s&uid=%d&key=%s",$a_openid,$uid,md5($uid.date('Y-m-d').$a_openid));
			$ret = file_get_contents($reqUrl);
			echo $ret;
		}
		
		exit(0);
// 		$uid = 1281654;
// 		$wx = new Weixin();
// 		$callbackUrl = 'http://m.epiaowang.com/project/index.php?r=scoreStore/Site/WXCodeBack&uid='.$uid;
// 		$wx->reqCodeOnOAuth(urlencode($callbackUrl));	//一定要urlencode，否则微信不会返回所带参数
		
		$url = "https://mp.weixin.qq.com/mp/profile_ext?action=home&__biz=MjM5MDI3NTg2MA==&scene=110#wechat_redirect";
		//$url = "https%3A//mp.weixin.qq.com/mp/profile_ext%3Faction%3Dhome%26__biz%3DMjM5MDI3NTg2MA%3D%3D%26scene%3D110%23wechat_redirect";
		//$url = "https://m.baidu.com/";
		//$url = "https://mp.weixin.qq.com/mp/profile_ext?action=home&__biz=MjM5MDI3NTg2MA==#wechat_redirect";
		//$url = "https://mp.weixin.qq.com/mp/profile_ext?action=home&__biz=MjM5MDI3NTg2MA==&mid==#wechat_redirect";
		//$url = "http://dwz.cn/4lA0T9";
		GeneralFunc::gotoUrl($url);
		
		//Header("Content-type: text/html; charset=gb2312");
		//Header("HTTP/1.1 303 See Other");
		//Header("Location: ".$url);
		
// 		echo "<script language='javascript' type='text/javascript'>";
// 		echo "window.location.href='$url'";
// 		echo "</script>";
		
	}
	
	/**
	 * 删除Session
	 */
	public function actionDelSess()
	{
		$sessObj = new Session();
		$sessObj->delete('iUserID');
		$sessObj->delete('sPhone');
		$sessObj->delete('openid');
		echo 'ok';
	}
	/**
	 * redis测试
	 */
	public function actionRedis()
	{
		
/* 		//获取token
		$reqUrl = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wx159b42037d6ebd28&secret=dbddf0ff60de752473d51bff06142107";
		
		$curl = curl_init();
		curl_setopt($curl,CURLOPT_URL, $reqUrl);
		curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		$res = curl_exec($curl);
		curl_close($curl);
		$result = json_decode($res, TRUE);
		print_r($result);
		exit(0);
*/
		Yii::app()->redis->set('name','张三123123456456',15);
		//Yii::app()->redis->delete('name');
		echo Yii::app()->redis->get('name');
		
		//Yii::app()->redis->delete('recreationinfo1_1309050');
		exit(0);
	}

	/**
	 * 短信测试
	 */
	public function actionMsg()
	{
// 		$arPara = array('prize'=>'33');
// 		$ret = SMSProcess::sendDayuSMS('13621083819', $arPara, 'SMS_25305344');
// 		echo $ret;
// 		$phone = $_GET['phone'];
// 		$date = empty($_GET['date']) ? date('Ymd') : $_GET['date'];
		
		$ret = SMSProcess::getDayuSMSLog('18366196213', '20170311');
		print_r($ret);
	}
	/**
	 * 位置服务测试
	 */
	public function actionLBS()
	{
		//财满街
		$lat1 = '39.9170824286';
		$lng1 = '116.5361860776';
		$cartInfo1 = array('x'=>4420425, 'y'=>1460345);
		
		//梨园云景北里
		$lat3 = '39.8824808791';
		$lng3 = '116.6644681067';
		$cartInfo3 = array('x'=>4416534, 'y'=>1471299);
		
		//沧州黄骅华贸商业广场
		$lat2 = '38.3653150879';
		$lng2 = '117.3501344165';
		$cartInfo2 = array('x'=>4251373, 'y'=>2268396);
		
		//沧州德力达电影院
		$lat4 = '38.064485';
		$lng4 = '117.253492';
		$cartInfo4 = array('x'=>4218219, 'y'=>2258956);
		
		//转为平面坐标
		$info = LBS::geodeticToCartesian($lat4,$lng4);
		print_r($info);
		
		$disp = ROUND(SQRT(($cartInfo3['x']-$cartInfo4['x'])*($cartInfo3['x']-$cartInfo4['x'])+($cartInfo3['y']-$cartInfo4['y'])*($cartInfo3['y']-$cartInfo4['y'])));
		echo $disp/1000;
		exit(0);
		
		
		//获取经纬度的距离
		$dist = LBS::getDistince($lat1,$lng1,$lat2,$lng2);
		echo $dist/1000;
		exit(0);
		
		
		//通过经纬度信息获取影院列表（由近及远）
		$sql = sprintf("SELECT iCinemaID,sCinemaName,ROUND(SQRT((iCartesianX-%d)*(iCartesianX-%d)+(iCartesianY-%d)*(iCartesianY-%d))) AS dist FROM {{b_cinema}} WHERE iCityID=1 ORDER BY dist"
				,$info['x'],$info['x'],$info['y'],$info['y']);
		$cinemaList = DbUtil::queryAll($sql);
		
		print_r($cinemaList);
	}
	
	/**
	 * 用户测试
	 */
	public function actionUser()
	{
		/*
		$uAddressInfo = array('uid'=>1212007,'receiver_name'=>'逯致中2','receiver_phone'=>'13621083819','city_id'=>1
				,'area'=>'北京市朝阳区','address'=>'222云景北里34号楼1123','is_def'=>0);
		
		$ret = UserProcess::addUserAddress($uAddressInfo);
		echo $ret;
		*/
		
		$ret = UserProcess::setUserDefAddress(1212007, 2);
		echo $ret;
	}
	/**
	 * 微信支付测试
	 */
	public function actionWXPay()
	{		
		$openid = 'oFLd4js7tKv5ePqXBadJz1c80T9c';
		
		//统一下单
		$input = new WxPayUnifiedOrder();
		
		echo 'into';
		exit(0);
		$input->SetBody("5个E豆");
		$input->SetAttach("");
		$input->SetOut_trade_no("20170105123456");
		$input->SetTotal_fee("0.01");
		$input->SetTime_start(date("YmdHis"));
		$input->SetTime_expire(date("YmdHis", time() + 600));
		$input->SetNotify_url(WEBURL."wxpays/pay/notify.php");
		$input->SetTrade_type("JSAPI");
		$input->SetOpenid($openid);
		$order = WxPayApi::unifiedOrder($input);
	}
	
	/**
	 * 二维码测试
	 */
	public function actionTest()
	{		
		$ret = ScoreProcess::getBindingSuccessData(1212007);
		print_r($ret);
		exit(0);
		
		$openId = 'oFLd4js7tKv5ePqXBadJz1c80T9c';
		$ret = ScoreProcess::bindingOpera($openId);
		if($ret)
		{
			echo 'yes';
		}else{
			echo 'no';
		}
		exit(0);
		
		
				
// 		$str = "http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url=http%3A%2F%2Fm.epiaowang.com%2Fproject%2Findex.php%3Fr%3DscoreStore%2FSite%2FInviteRespond_Step1%26uid%3D1211718&title=%E6%BF%80%E6%83%85%E7%88%86%E5%8F%91%EF%BC%8C%E3%80%8A%E6%9E%81%E9%99%90%E7%89%B9%E5%B7%A5%EF%BC%9A%E7%BB%88%E6%9E%81%E5%9B%9E%E5%BD%92%E3%80%8B%E6%88%98%E6%96%97%E4%B8%8D%E6%AD%A2&desc=&summary=&site=&pics=http%3A%2F%2Fm.epiaowang.com%2Fproject%2Fimages%2Fposters_bg%2F20170209104746.jpg";
// 		$str = urldecode($str);
// 		echo $str;

		$phonenumber = '12312345678';
		if(preg_match("/^1[34578]{1}\d{9}$/",$phonenumber)){
			echo "是手机号码";
		}else{
			echo "不是手机号码";
		}
		exit(0);

		$TicketID = '验证码92269427';	//单码
		//$TicketID = '序列号845281验证码723315';	//
		$tmpFetchArr = explode('验证码', $TicketID);
		$tmpFetchArr[0] = str_replace('序列号', '', $tmpFetchArr[0]);

		$testArr = array();
		if(empty($tmpFetchArr[0]))
		{
			$testArr = $tmpFetchArr[1];
		}else{
			$testArr = $tmpFetchArr[0].'|'.$tmpFetchArr[1];
		}
		
		print_r($testArr);

	}
	
	/**
	 * 搜索测试
	 */
	public function actionSearch()
	{
		$searchStr = $_REQUEST['searchStr'];
		
		$cinemaList = array();
		if(!empty($searchStr))
		{
			$sql = sprintf("SELECT iCinemaID,sCinemaName,toAllPinyin(sCinemaName) AS allP,toFirstPinyin(sCinemaName) AS firP FROM tb_b_cinema WHERE icityid=1 AND (sCinemaName LIKE '%%%s%%' OR toAllPinyin(sCinemaName) LIKE '%%%s%%' OR toFirstPinyin(sCinemaName) LIKE '%%%s%%')",$searchStr, $searchStr, $searchStr);
			$cinemaList = DbUtil::queryAll($sql);
		}
		//print_r($cinemaList);
		
		Yii::app()->smarty->assign('SEARCHSTR',$searchStr);
		Yii::app()->smarty->assign('CINEMALIST',$cinemaList);
		Yii::app()->smarty->assign('CINEMACOUNT',count($cinemaList));
		Yii::app()->smarty->display('search.html');
	}
	
	/**
	 * 微信号关注测试
	 */
	public function actionSubscribe()
	{
		$url = "https://mp.weixin.qq.com/mp/profile_ext?action=home&__biz=MjM5MDI3NTg2MA==&scene=110#wechat_redirect";
		//echo "<script>window.location='".$url."'</script>";
		
		//echo "<script language='javascript' type='text/javascript'>window.location.href='".$url."'</script>";
		//echo '<script language="javascript">self.location="'.$url.'"</script> ';
		
		//echo '<script language="javascript">top.location="'.$url.'";</script>';
		
		header('Location: https://mp.weixin.qq.com/mp/profile_ext?action=home&__biz=MjM5MDI3NTg2MA==&scene=110#wechat_redirect');
	}
}