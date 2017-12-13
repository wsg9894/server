<?php
// 1-100为系统级错误
$ERROR_TYPES = array('ERROR_TYPE_UNEXPECTED' => 0
    , 'ERROR_TYPE_FATAL' => 1
    , 'ERROR_TYPE_LOCKED' => 2
    
    // 影院相关错误
    ,'ERROR_TYPE_CINEMA_CITYERROR'=>101,
    //order error
    'ERROR_TYPE_ORDER_NOORDERERROR'=>201,
    'ERROR_TYPE_ORDER_EXPIRED'=>202,
    
    'ERROR_TYPE_USER_NOLOGIN'=>301,
    'ERROR_TYPE_USER_NOORDER'=>302,
    
    
    'ERROR_TYPE_ORDER_NO' =>401
    
);
foreach ($ERROR_TYPES as $k => $v) {
    define($k, $v);
}

$PROCESSED_STATUS = array
(
    "PROCESSED_STATUS_Unknown" => 0,
    "PROCESSED_STATUS_Succeed" => 1,
    "PROCESSED_STATUS_Failed" => 2,
    "PROCESSED_STATUS_NotProcessed" => 3,
);

foreach ($PROCESSED_STATUS as $k => $v) {
    define($k, $v);
}

$ORDER_STATUS = array(
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
);

foreach ($ORDER_STATUS as $k => $v) {
    define($k, $v);
}

$VoucherStatus = array(
    "VoucherStatus_None"=>1,//未使用
    "VoucherStatus_Band"=>2,//已绑定
    "VoucherStatus_Use"=>3,//已使用
    "VoucherStatus_Invalid"=>4,//已失效
);
foreach ($VoucherStatus as $k => $v) {
    define($k, $v);
}
$InterfaceType = array
(
    "InterfaceType_Himovie" =>3,
    "InterfaceType_YML" =>4,
    "InterfaceType_Spider" =>5,
    "InterfaceType_Lashou" =>6,
    "InterfaceType_Hipiao" =>7,
    "InterfaceType_Wangpiao" =>8,
    "InterfaceType_Ipiao" =>9,
);

foreach ($InterfaceType as $k => $v) {
    define($k, $v);
}

$InterfaceName = array(
    "4" =>'影迷乐',
    "5" =>'蜘蛛网',
    "6" =>'拉手网',
    "7" =>'哈票网',
    "8" =>'网票网',
    "9" =>'票量网',
);
//铠甲勇士
$Kaijia = array('201610602265','26303','001106472016','*00110647201');

$LogType = array(
    "LogType_DownLoad" =>"DownLoadFromInterface",
);

foreach ($LogType as $k => $v) {
    define($k, $v);
}


define('global_PageSize',50);
define('global_PageSizes',2);


define('global_Order_Time',900);

$arProvice = array("101"=>"北京市",
                   "102"=>"天津市",
                   "103"=>"河北省",
                   "104"=>"山西省",
                   "105"=>"内蒙古",
                   "106"=>"辽宁省",
                   "107"=>"吉林省",
                   "108"=>"黑龙江省",
                   "109"=>"上海市",
                   "110"=>"江苏省",
                   "111"=>"浙江省",
                   "112"=>"安徽省",
                   "113"=>"福建省",
                   "114"=>"江西省",
                   "115"=>"山东省",
                   "116"=>"河南省",
                   "117"=>"湖北省",
                   "118"=>"湖南省",
                   "119"=>"广东省",
                   "120"=>"广西自治区",
                   "121"=>"海南省",
                   "122"=>"重庆市",
                   "123"=>"四川省",
                   "124"=>"贵州省",
                    "125"=>"云南省",
                    "126"=>"西藏自治区",
                    "127"=>"陕西省",
                    "128"=>"甘肃省",
                    "129"=>"青海省",
                    "130"=>"宁夏回族自治区",
                    "131"=>"新疆维吾尔自治区",
                    "132"=>"香港特别行政区",
                    "133"=>"澳门特别行政区",
                    "134"=>"台湾省",
                    "135"=>"其它");

$arMovieState = array(
  
    '1' => "即将上映",
    '2' => "正在热映",
    '3' => "停止放映"
);

$arMovieType = array(
    '1' => '动作',
    '2' => '喜剧',
    '3' => '爱情',
    '4' => '科幻',
    '5' => '灾难',
    '6' => '恐怖',
    '7' => '悬疑',
    '8' => '魔幻',
    '9' => '战争',
    '10'=> '罪案',
    '11'=> '惊悚',
    '12'=> '动画',
    '13'=> '伦理',
    '14'=> '纪录',
    '15'=> '犯罪',
    '16'=> '剧情',
    '17'=> '历史',
    '18'=> '奇幻',
    '19'=> '冒险',
    '20'=> '武侠',
    '21'=> '古装',
    '22'=> '传记',
);

$userType = array(
    '1'=>'普通用户',	
	'2'=>'VIP用户	',
	'3'=>'E起电影用户'	
);

$vocherType = array(
    '1' => '活动券',
    '2' => '现金券',
    '3' => '团购券',
    '4' => '电影卡团购券',
    '5' => '通券卡团购券'
);

    // 支付状态
    class PayStatus
    {
        const orderNoPay = "10101"; //订单未支付
        const orderPay = "10102"; // 订单已支付
        const orderAsynSeatBegin = "10103";  //订单在线选座异步开始 
        const orderSucess = "10104"; // 订单已成功
        const orderAsynSeatSucess="10105"; // 订单在线选座异步完成
        
        const orderEnd = "10206"; // 订单已结束
        const orderClose = "10207"; // 订单已关闭
        const orderOver = "10208"; // 订单已过期
        const orderAsynSeatFail="10209"; //订单在线选座异步失败
        const orderAsynSeatEnd="10210"; //订单在线选座异步退款成功
    }

    $arPayStatus = array(
        PayStatus::orderNoPay => '订单未支付',
        PayStatus::orderPay => '订单已支付',
        PayStatus::orderAsynSeatBegin => '订单在线选座异步开始',
        PayStatus::orderSucess => '订单已成功',
        PayStatus::orderAsynSeatSucess => '订单在线选座异步完成',
        
        PayStatus::orderEnd => '订单已结束',
        PayStatus::orderClose => '订单已关闭',
        PayStatus::orderOver => '订单已过期',
        PayStatus::orderAsynSeatFail =>'订单在线选座异步失败',
        PayStatus::orderAsynSeatEnd =>'订单在线选座异步退款成功'
    );
    
    // 订单类型
     class OrderType
    {
        const onlineSeatOrder= "100001"; //E票网在线选座订单
        const accountRechargeOrder = "100002";//账户充值订单
        const cardRechargeOrder = "100003";//电影卡充值订单
        const huodongCardOrder = "200001"; //活动电影卡订单
        const huodongSeatOrder = "200002"; //活动在线选座订单
         const advanceTicketOrder = "200003"; //活动预售券订单
        const thirdSeatOrder = "300001"; //第三方在线选座订单
        const cardSaleOrder = "400001"; //后台销售卡记录 
        const cardCashOrder = "400002"; // 现金券购买电影卡
        const UMPAYRechargeOrder = '500001';// 联动优势充值订单
    }

    $arOrderType = array(
        OrderType::onlineSeatOrder => 'E票网在线选座订单',
        OrderType::accountRechargeOrder => '账户充值订单',
        OrderType::cardRechargeOrder => '电影卡充值订单',
        OrderType::huodongCardOrder => '活动电影卡订单',
        OrderType::huodongSeatOrder => '活动在线选座订单',
        OrderType::advanceTicketOrder => '预售券活动订单',
        OrderType::thirdSeatOrder => '第三方在线选座订单',
        OrderType::cardSaleOrder => '后台销售卡记录',
        OrderType::cardCashOrder => '现金券购买电影卡',
        OrderType::UMPAYRechargeOrder =>'联动优势充值订单'
    );
    //活动类型
    class HuoDongType{
        //购票
        const BuyCard = 1;
        //刮奖
        const Scratch = 2;
    }
    
    // 支付类型
    class PayType
    {
        const backCardPay = '100001'; // 后台开卡
        const weixinPay = "400001"; // 微信支付
        const ailyPay = "400002"; // 支付宝支付
        const accountPay = "400003";//账户余额支付
        const cardPay = "400004"; //电影卡支付
        const cashPay = "400005";    //现金券抵值
        const qihooPay = "400006";     //360支付
        const backpay = '400007'; // E票网后台支付
        const umpay   = '400008'; // 联动优势支付
        const jhtlpay = '400009'; // 金环天朗充值
        const  third = '400010'; //第三方
        const specially = '400011'; //特惠购票
    }
    //秒杀的活动控制，暂时写在配置里面（后期优化）,如果多个活动同时在线的话，就累加
    class Seckill{
        const start_time = '20:00:00';
        const end_time='21:00:00';
    }
    //血战钢锯岭
    class HuoDong{
        const start_time="20:00:00";
        const end_time="21:00:00";
        const HuoDongNo = 'bloody_hacksaw_ridge';
    }
    //活动和在线选座配置
    class onlineHuodong{
        const iHuoDongID = 111;
        const iHuoDongItemID = 170;
        const Mprices = 100;
        const lowMprice = 19.9;
    }
    //少年活动
    class YoungBoy{
        const HuoDongNo = 'young_boy';
    }
    $arPayType = array(
        PayType::backCardPay => '后台开卡',
        PayType::weixinPay => '微信支付',
        PayType::ailyPay => '支付宝支付',
        PayType::accountPay => '账户余额支付',
        PayType::cardPay => '电影卡支付',
        PayType::cashPay => '现金券抵值',
        PayType::qihooPay => '360支付',
        PayType::backpay => 'E票网后台支付',
        PayType::umpay =>'联动优势支付',
        PayType::jhtlpay =>'金环天朗充值',
        PayType::third=>'第三方',
        PayType::specially=>'特惠购票',
    );
    
    $arUmPay = array(
        'E票网10元电影券' =>10,
        'E票网30元电影券' =>30,
        'E票网50元电影券' =>50,
        'E票网100元电影券' =>100,
    );

$UserLimit = array(
    '权限' => 8,
    '技术' => 7,
    '产品' => 6,
    '财务' => 5,
    '市场' => 4,
    '运营' => 3,
    '客服' => 2
);
$MainMenu = array(
    '文章管理' => 1,
    '影院影厅管理' => 4,
    '订座接口管理' => 8,
    '点卡券管理' => 14,
    '活动管理' => 24,
    '客服管理' => 26,
    '合作商管理' => 28,
    '订单管理' => 35,
    '权限管理' => 49,
	'积分商城管理' => 63
);


//积分选项配置
$scoreConf = array(

	'keyConf' => array(					//积分配置key（用于表字段的读取）
		'wxShare' => 'wx_share',				//微信分享
		'sourcePoint' => 'source_point',		//积分来源分值
		'sourceDesc' => 'source_desc',			//积分来源描述
		'sourcePrize' => 'source_prize',		//积分来源奖品
	),

	'sourceConf' => array(				//积分来源配置
		'user_invite' => array('name' => '邀请好友', 'source_id' => 1),
		'buy_ticket_online' => array('name' => '在线选座订单', 'source_id' => 2),
		'banding_wx' => array('name' => '绑定有礼', 'source_id' => 3),
		'join_act' => array('name' => '活动券订单', 'source_id' => 4),
//		'system_opera' => array('name' => '系统操作', 'source_id' => 5),
	),
);


$MainMenuID = array(1,4,8,14,24,26,28,35,49);
//活动默认路径
define('EPIAOWANG_HUODONG_PATH_ACTIVITY','/act/activity/advanceticket/index.php');//预售券活动固定路径
define('INT_RELATIVE_ACTIVITYID_NUM',553);//预售券活动id相对参数值

