<?php

/**
 * SiteController - 通用控制器
 * @author ylp
 * @version V1.0
 */

Yii::import('application.modules.output.models.process.*');
class SiteController extends Controller
{

	/**
	 * 影片列表
	 */
	public function actionIndex()
	{
		$isWXVisitor = Weixin::isWxVisitor();
		if($isWXVisitor){
			//获取微信JS-SDK权限验证签名
			$wx = new Weixin();
			$signPackage = $wx->getSignPackage();
			if(empty($signPackage))
			{
				//如果微信签名包获取异常，则依然使用百度开放接口
				$isWXVisitor = 0;
			}
			Yii::app()->smarty->assign('SIGNPACKAGE',$signPackage);
		}
		Yii::app()->smarty->assign('ISWXVISITOR',$isWXVisitor);
		Yii::app()->smarty->display('output/index.html');
	}

	/**
	 * 搜索影片
	 */
	public function actionSearchCinema(){
		Yii::app()->smarty->display('output/cinemasearch.html');
	}

	/**
	 * 影片详情
	 */
	public function actionMovie(){
		$isWXVisitor = Weixin::isWxVisitor();
		if($isWXVisitor){
			//获取微信JS-SDK权限验证签名
			$signPackage = Model_Base_Weixin::getSignPackage();
			if(empty($signPackage))
			{
				//如果微信签名包获取异常，则依然使用百度开放接口
				$isWXVisitor = 0;
			}
			Yii::app()->smarty->assign('SIGNPACKAGE',$signPackage);
		}
		$arMovieType = Yii::app()->params['movieType'];
		Yii::app()->smarty->assign('ISWXVISITOR',$isWXVisitor);
		Yii::app()->smarty->assign('movieId',$_REQUEST['movieId']);
		Yii::app()->smarty->assign('type',$_REQUEST['type']);
		Yii::app()->smarty->assign('arMovieType',$arMovieType);
		Yii::app()->smarty->display('output/movie.html');
	}

	/**
	 * 影片上映影院列表
	 */
	public function actionMovieCinema(){
		Yii::app()->smarty->display('output/moviecinema.html');
	}

	/**
	 * 城区
	 */
	public function actionCityList(){
		Yii::app()->smarty->display('output/city.html');
	}

	/**
	 * 排期页
	 */
	public function actionArrangeSelect(){
		$companyId = (isset($_REQUEST['companyId']) && !empty($_REQUEST['companyId']))?$_REQUEST['companyId']:Yii::app()->params['shanghang'];
		$sessObj = new Session();
		$sessObj->add('companyId',$companyId);
		if(isset($_REQUEST['backurl'])){
			$backurl = $_REQUEST['backurl'];
			$sessObj->add('backurl',$backurl);
		}else{
			$backurl = $sessObj->get('backurl');
		}
		$showDate = empty($_REQUEST['showDate'])?date('Y-m-d'):$_REQUEST['showDate'];
		$arMovieType = Yii::app()->params['movieType'];
		Yii::app()->smarty->assign('arMovieType',$arMovieType);
		Yii::app()->smarty->assign('backurl',$backurl);
		Yii::app()->smarty->assign('showDate',$showDate);
		Yii::app()->smarty->display('output/arrangeselect.html');
	}

	/**
	 * 选座页
	 */
	public function actionSeatIndex(){
		Yii::app()->smarty->display('output/seatindex.html');
	}

	/**
	 * 订单展示页
	 */
	public function actionSeatPay(){
		Yii::app()->smarty->display('output/seatpay.html');
	}

	/**
	 * 支付页
	 */
	public function actionSeatSubmit(){
		$uSessInfo = UserProcess::getLoginSessionInfo();
		if(empty($uSessInfo)){
			GeneralFunc::writeLog('getLoginSessionInfo为空', Yii::app()->getRuntimePath().'/H5yii/');
		}
		$payType = 2;
		GeneralFunc::writeLog('SeatSubmit'.$payType, Yii::app()->getRuntimePath().'/H5yii/');
		switch ($payType)
		{
			case 1:
				$payType = ConfigParse::getPayTypeKey('accountPay');
				break;
			case 2:
				$payType = ConfigParse::getPayTypeKey("shanghaiBank");
				break;
			default:
				GeneralFunc::alert("非法参数，页面将关闭");
				break;
		}
		//判断用户订单是否存在
		$outerOrderId = isset($_REQUEST['orderId'])?$_REQUEST['orderId']:0;
		if(empty($outerOrderId))
		{
			GeneralFunc::alert('非法参数');
			exit(0);
		}
		$ret = OOrderProcess::paySeatOnlineOrder($outerOrderId, $payType);
		if (!$ret['ok'])
		{
			if(isset($ret['flag'])&&$ret['flag']=="orderStatus"){
				GeneralFunc::alert('非法参数');
				exit(0);
			}else{
				GeneralFunc::alert($ret['msg']);
			}

		}
	}


	public function actionPayNotify(){
		$outerOrderId = empty($_REQUEST['merOrderNum'])?'':$_REQUEST['merOrderNum'];
		$merOrderAmt = empty($_REQUEST['merOrderAmt'])?0:$_REQUEST['merOrderAmt'];
		GeneralFunc::writeLog('PayNotify:'.print_r($_REQUEST,true), Yii::app()->getRuntimePath().'/H5yii/');
		$outerOrderId = OOrderProcess::getThSubOrderInfo($outerOrderId)['outerOrderId'];
		$orderInfo = OOrderProcess::getOrderSeatInfoByOuterOrderId($outerOrderId);
		if(empty($orderInfo)){
			GeneralFunc::writeLog('PayNotify,订单不存在'.$outerOrderId.",".$merOrderAmt, Yii::app()->getRuntimePath().'/H5yii/');
			GeneralFunc::getJson(array('resultCode'=>1,'resultDesc'=>'订单不存在','resultData'=>array()));
			return false;
		}
		GeneralFunc::writeLog('PayNotify：'.$outerOrderId.",".$merOrderAmt.','.print_r($orderInfo,true), Yii::app()->getRuntimePath().'/H5yii/');
		if(abs($merOrderAmt-$orderInfo['totalPrice']*100) > 0.03){
			GeneralFunc::writeLog('PayNotify,订单金额校验错误'.$outerOrderId.",".$merOrderAmt, Yii::app()->getRuntimePath().'/H5yii/');
			GeneralFunc::getJson(array('resultCode'=>1,'resultDesc'=>'订单金额校验错误','resultData'=>array()));
			return false;
		}

		if($orderInfo['orderStatus']!=ConfigParse::getPayStatusKey('orderNoPay')){
			GeneralFunc::writeLog('PayNotify,订单状态错误'.$outerOrderId.",".$merOrderAmt.$orderInfo['orderStatus'], Yii::app()->getRuntimePath().'/H5yii/');
			GeneralFunc::getJson(array('resultCode'=>1,'resultDesc'=>'订单状态错误','resultData'=>array()));
			return false;
		}

		$updateOrderInfo['orderStatus']=  ConfigParse::getPayStatusKey('orderPay');
		$updateOrderInfo['closeTime']=  date('Y-m-d H:i:s');
		$updateOrderInfo['orderInfo']=$orderInfo['orderInfo']."订单支付";
		$updateOrderInfo['outerOrderId']=$outerOrderId;
		OOrderProcess::updateUserOrderInfo($updateOrderInfo);
		GeneralFunc::writeLog('PayNotify1,修改成功'.$outerOrderId.",".$merOrderAmt, Yii::app()->getRuntimePath().'/H5yii/');
		$updateSeatOrder['iUserId']=$orderInfo['iUserId'];
		$updateSeatOrder['outerOrderId']=$outerOrderId;
		$updateSeatOrder['status']=ConfigParse::getPayStatusKey('orderPay');
		OOrderProcess::updateOrderSeatByOrderInfo($orderInfo['iUserId'],$updateSeatOrder);
		GeneralFunc::writeLog('PayNotify2,修改成功'.$outerOrderId.",".$merOrderAmt, Yii::app()->getRuntimePath().'/H5yii/');
		//修改太和订单
		ThOrderProcess::updateThOrder($outerOrderId,ThOrderProcess::$arrOrderSratus['payOrder']);
		GeneralFunc::writeLog('PayNotify3,修改成功'.$outerOrderId.",".$merOrderAmt.','.ThOrderProcess::$arrOrderSratus['payOrder'], Yii::app()->getRuntimePath().'/H5yii/');

		OOrderProcess::confirmThSeatOnlineOrder($orderInfo['iUserId'], $outerOrderId,$orderInfo['sendPhone']);

	}

	/**
	 * 成功页
	 */
	public function actionSuccess(){
		Yii::app()->smarty->display('output/success.html');
	}

	public function actionTest(){
		Yii::app()->smarty->display('output/test.html');
	}

	/**
	 * 上行和e票网用户逻辑
	 */
	public function actionGetLoginInfo(){
		$cipher = $_REQUEST['cipher'];
		GeneralFunc::writeLog('GetLoginInfo秘钥：'.$cipher, Yii::app()->getRuntimePath().'/H5yii/');
		$arr = array('cipher'=>$cipher);
		$ret = ThOrderProcess::getUserInfo($arr);
		GeneralFunc::writeLog('GetLoginInfo获取结果：'.print_r($ret,true), Yii::app()->getRuntimePath().'/H5yii/');
		if($ret['resultCode']==0){
			$sPhone = json_decode($ret['resultData'],true)['mobileNo'];
			$UserInfo = UserProcess::getUInfoBysPhone($sPhone);
			if(empty($UserInfo)){
				//注册
				$UserInfo = UserProcess::ThUserRegister($sPhone,82);
				UserProcess::addLoginSessionInfo($UserInfo);
				GeneralFunc::getJson(array('ok'=>true,'sPhone'=>$sPhone));
			}else{
				//登录
				UserProcess::addLoginSessionInfo($UserInfo);
				GeneralFunc::getJson(array('ok'=>true,'sPhone'=>$sPhone));
			}
		}else{
			GeneralFunc::getJson(array('ok'=>false,'msg'=>'获取用户信息失败'));
		}
	}

	public function actionGetUserInfo(){
		$cipher = $_REQUEST['cipher'];
		$arr = array('cipher'=>$cipher);
		$url = 'http://101.201.79.224:6080/resDecode.do';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER,0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $arr);
//        echo $arr;die;
		$response = curl_exec($ch);
//        print_r($response);die;
		curl_close($ch);
		echo $response;exit(0);
	}


}