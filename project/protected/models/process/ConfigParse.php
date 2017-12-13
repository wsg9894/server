<?php

/**
 * ConfigParse - 业务层配置码说明
 * @author anqing
 * @version V1.0
 */

class ConfigParse
{
	//微信支付配置（此处做为配置使用）
	private static $_weixinApliy = array(
		'appId'=> "wxa12cb24eb59eabb0",
		'package'=> "prepay_id=",
		'key'=> "1dyok0kQ68V9W8c9VaEZRzOsy0FcOMt2",
		'mch_id'=>'1427736402',
		'secret'=>'93b4bc2174d24cebc6dd5a3d021cdabb',
		'grant_type'=>''
	);

    //接口
	private static $InterfaceType = array
	(
		"InterfaceType_Spider" =>5,
		"InterfaceType_Hipiao" =>7,
		"InterfaceType_Wangpiao" =>8,
		"InterfaceType_Ipiao" =>9,
		"InterfaceType_GWL" =>10,
		"InterfaceType_MaoYan" =>11,
	);

	//订单类型定义（此处做为配置使用）
	private static $_orders = array(
		'onlineSeatOrder'=> "100001", //E票网在线选座订单
		'accountRechargeOrder' => "100002",//账户充值订单
		'cardRechargeOrder' => "100003",//电影卡充值订单
		'huodongCardOrder' => "200001", //活动电影卡订单
		'huodongSeatOrder' => "200002", //活动在线选座订单
		'advanceTicketOrder' => "200003", //活动预售券订单
		'thirdSeatOrder' => "300001", //第三方在线选座订单
		'cardSaleOrder' => "400001", //后台销售卡记录
		'cardCashOrder' => "400002", // 现金券购买电影卡
		'UMPAYRechargeOrder' => "500001",// 联动优势充值订单
		'noOrder' => "-1",// 联动优势充值订单
	);

	//在线选座订单（此处做为配置使用）
	private static $_OrderSeatInfo = array(
		'order_seatId'=> "order_seatId",
		'outerOrderId'=> "outerOrderId",
		'sCinemaInterfaceNo'=> "sCinemaInterfaceNo",
		'sRoomInterfaceNo'=> "sRoomInterfaceNo",
		'sSeatInfo'=> "sSeatInfo",
		'mFee'=> "mFee",
		'sInterfaceOrderNo'=> "sInterfaceOrderNo",
		'sInterfaceValidCode'=> "sInterfaceValidCode",
		'dCreateTime'=> "dCreateTime",
		'sCinemaName'=> "sCinemaName",
		'iRoomID'=> "iRoomID",
		'sRoomName'=> "sRoomName",
		'sMovieName'=> "sMovieName",
		'roomMovieNo'=> "iRoomMovieID",
		'sMovieID'=> "iMovieID",
		'iCinemaId'=> "iCinemaID",
		'sRoomMovieInterfaceNo'=> "sRoomMovieInterfaceNo",
		'iInterfaceID'=> "iInterfaceID",
		'sPhone'=> "sPhone",
		'IMax'=> "sIMax",
		'sDimensional'=>'sDimensional',
        'sLanguage'=> 'sLanguage',
        'iRoommovieID'=> 'iRoomMovieID',
        'sMovieInterfaceNo'=> 'sMovieInterfaceNo',
        'userId'=> 'iUserId',
        'price'=> 'mPrice',
        'status'=>'status',
        'dPlayTime'=>'dPlayTime',
        'mSettingPrice'=>'mSettingPrice',
        'lowValue'=>'lowValue',
	);

	//支付状态（此处做为配置使用）
	private static $_PayStatus = array(
		'orderNoPay'=> "10101", //订单未支付
		'orderPay'=> "10102", // 订单已支付
		'orderAsynSeatBegin'=> "10103",  //订单在线选座异步开始
		'orderSucess'=> "10104", // 订单已成功
		'orderAsynSeatSucess'=>"10105", // 订单在线选座异步完成
		 //17-2-23根据格瓦拉的订单支付通知增加了这个状态
        'orderPayNoticeFail' => "10106", //通知格瓦拉订单支付接口失败（格瓦拉）（原来订单状态是10211）
        'orderAsynGetSeatFail' => "10107", //格瓦拉超出17分钟还没出票（原来订单状态是10212）
        'orderEnd'=> "10206", // 订单已结束
        'orderClose'=> "10207", // 订单已关闭
        'orderOver'=> "10208", // 订单已过期
        'orderAsynSeatFail'=>"10209", //订单在线选座异步失败
        'orderAsynSeatEnd'=>"10210", //订单在线选座异步退款成功
	);
	// 支付类型
	private static $_PayType = array(
		'backCardPay' => '100001', // 后台开卡
		'weixinPay' => "400001", // 微信支付
		'ailyPay'=> "400002", // 支付宝支付
		'accountPay' => "400003",//账户余额支付
		'cardPay' => "400004", //电影卡支付
		'cashPay' => "400005",    //现金券抵值
		'qihooPay' => "400006",     //360支付
		'backpay' => '400007', // E票网后台支付
		'umpay'=> '400008', // 联动优势支付
		'jhtlpay'=> '400009', // 金环天朗充值
		'third'=> '400010', // 第三方
		'specially'=> '400011', // 特惠购票
		'shanghaiBank'=> '400012', // 上海银行
	);

	// 支付类型
	private static $_PayName = array(
		'100001' => '后台开卡', // 后台开卡
		'400001' => "微信支付", // 微信支付
		'400002'=> "支付宝支付", // 支付宝支付
		'400003' => "账户余额支付",//账户余额支付
		'400004' => "电影卡支付", //电影卡支付
		'400005' => "现金券抵值",    //现金券抵值
		'400006' => "360支付",     //360支付
		'400007' => 'E票网后台支付', // E票网后台支付
		'400008'=> '联动优势支付', // 联动优势支付
		'400009'=> '金环天朗充值', // 金环天朗充值
		'400010'=> '第三方', // 第三方
		'400011'=> '特惠购票', // 特惠购票
		'400012'=> '上海银行', // 上海银行
	);

   //订单状态
	private static $ORDER_STATUS = array(
		"ORDER_STATUS_Unknown" => 0,
		"ORDER_STATUS_WaitForPay" => 1,
		"ORDER_STATUS_PaySuccessed" => 2,
		"ORDER_STATUS_PayFailed" => 3,
		"ORDER_STATUS_RefundProcessing" => 4,
		"ORDER_STATUS_RefundSuccessed" => 5,
		"ORDER_STATUS_RefundFailed" => 6,
		"ORDER_STATUS_Successed" => 7,
		"ORDER_STATUS_Failed" => 8,
		"ORDER_STATUS_Overdue" => 9,
		'PROCESSED_STATUS_NotProcessed'=>10
	);

    //微信支付配置
	public static function getWeixinApi()
	{
		return ConfigParse::$_weixinApliy;
	}

	/**
	 * 接口
	 */
	public static function getInterface()
	{
		return ConfigParse::$InterfaceType;
	}

	/**
	 * 获取订单列表
	 *
	 * @return array[] 订单列表
	 */
	public static function getOrders()
	{
		return ConfigParse::$_orders;
	}

	/**
	 * 获取在线选座订单
	 *
	 * @return array[] 订单列表
	 */
	public static function getOrderSeatInfo()
	{
		return ConfigParse::$_OrderSeatInfo;
	}

	/**
	 * 获取订单支付状态
	 *
	 * @return array[] 订单列表
	 */
	public static function getPayStatus()
	{
		return ConfigParse::$_PayStatus;
	}

	/**
	 * 获取订单支付类型
	 *
	 * @return array[] 订单列表
	 */
	public static function getPayType()
	{
		return ConfigParse::$_PayType;
	}

	/**
	 * 获取订单支付类型
	 *
	 * @return array[] 订单列表
	 */
	public static function getPayName()
	{
		return ConfigParse::$_PayName;
	}

	/**
	 * 获取订单状态
	 *
	 * @return array[] 订单列表
	 */
	public static function getOrderStatus()
	{
		return ConfigParse::$ORDER_STATUS;
	}

	public static function getWeixinKey($weixinKey)
	{
		if(array_key_exists($weixinKey,ConfigParse::getWeixinApi()))
		{
			return ConfigParse::$_weixinApliy[$weixinKey];
		}
	}

	public static function getInterfaceKey($InterfaceKey)
	{
		if(array_key_exists($InterfaceKey,ConfigParse::getInterface()))
		{
			return ConfigParse::$InterfaceType[$InterfaceKey];
		}
	}

	/**
	 * 获取订单key值
	 *
	 * @param string $orderKey 错误key;
	 * @return int 错误NO
	 */
	public static function getOrdersKey($orderKey)
	{
		if(array_key_exists($orderKey,ConfigParse::getOrders()))
		{
			return ConfigParse::$_orders[$orderKey];
		}
	}

	/**
	 * 获取在线选座订单key值
	 *
	 * @param string $getOrderSeatInfoKey 错误key;
	 * @return int 错误NO
	 */
	public static function getOrderSeatInfoKey($getOrderSeatInfoKey)
	{
		if(array_key_exists($getOrderSeatInfoKey,ConfigParse::getOrderSeatInfo()))
		{
			return ConfigParse::$_OrderSeatInfo[$getOrderSeatInfoKey];
		}
	}

	/**
	 * 获取订单支付状态key值
	 *
	 * @param string $getPayStatusKey 错误key;
	 * @return int 错误NO
	 */
	public static function getPayStatusKey($getPayStatusKey)
	{
		if(array_key_exists($getPayStatusKey,ConfigParse::getPayStatus()))
		{
			return ConfigParse::$_PayStatus[$getPayStatusKey];
		}
	}

	/**
	 * 获取订单类型key值
	 *
	 * @param string $getPayStatusKey 错误key;
	 * @return int 错误NO
	 */
	public static function getPayTypeKey($getPayTypeKey)
	{
		if(array_key_exists($getPayTypeKey,ConfigParse::getPayType()))
		{
			return ConfigParse::$_PayType[$getPayTypeKey];
		}
	}

	/**
	 * 获取订单类型key值
	 *
	 * @param string $getPayStatusKey 错误key;
	 * @return int 错误NO
	 */
	public static function getPayNameKey($getPayNameKey)
	{
		if(array_key_exists($getPayNameKey,ConfigParse::getPayName()))
		{
			return ConfigParse::$_PayName[$getPayNameKey];
		}
	}

	/**
	 * 获取订单状态key值
	 *
	 * @param string $getPayStatusKey 错误key;
	 * @return int 错误NO
	 */
	public static function getOrderStatusKey($getOrderStatusKey)
	{
		if(array_key_exists($getOrderStatusKey,ConfigParse::getOrderStatus()))
		{
			return ConfigParse::$ORDER_STATUS[$getOrderStatusKey];
		}
	}
}