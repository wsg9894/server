<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'积分商城',

	// preloading 'log' component
	'preload'=>array('log'),

	// autoloading model and component classes
	'import'=>array(
		'application.models.*',
		'application.components.*',
		'application.extensions.*',
		'application.models.data.*',
		'application.models.data.db.*',
		'application.models.process.*',
		'application.models.process.lib.*',
		'application.models.process.interface.*',
		'application.models.process.base.*',
		'application.modules.srbac.controllers.SBaseController',		
	),

	'modules'=>array(
		// uncomment the following to enable the Gii tool

		'gii'=>array(
			'class'=>'system.gii.GiiModule',
			'password'=>'123456',
		 	// If removed, Gii defaults to localhost only. Edit carefully to taste.
			'ipFilters'=>array('127.0.0.1','::1'),
		),
		//积分商城
		'scoreStore'=>array(),

		//微信小程序
		'wxAPP'=>array(),
		
		//下游合作方接入
		'partner'=>array(),

		//接口输出给下游
		'output'=>array(),

		//影院系统
		'cinemaSystem'=>array(),

		//运营系统
        'market'=>array(),

		'srbac'=>array(
			'userclass'=>'User',//default: User      对应用户的model
			'userid'=>'admin_user_id',//default: userid     用户表标识位对应字段
			'username'=>'admin_user_email',//default:username  用户表中用户名对应字段
			'delimeter'=>'@',//default:-      item分隔符
			'debug'=>true,//default :false           调试模式，true则所有用户均开放，可以随意修改权限控制
			'pageSize'=>10,// default : 15
			'superUser'=>'Authority', //default: Authorizer    超级管理员，这个账号可以不受权限控制的管理，对所有页面均有访问权限
			'css'=>'srbac.css',//default: srbac.css        样式文件
			'layout'=>'application.views.layouts.main',//default: application.views.layouts.main,must be an existing alias
			'notAuthorizedView'=>'srbac.views.authitem.unauthorized',// default:srbac.views.authitem.unauthorized, must be an existing alias
			'alwaysAllowed'=>array(//default: array()  总是允许访问的动作
			'gui','SiteLogin','SiteLogout','SiteWelcome','SiteIndex'
			),
			'userActions'=>array(
			'Show','View','List'
			),//default: array()
			'listBoxNumberOfLines'=>15,//default : 10 
			'imagesPath'=>'srbac.images',// default: srbac.images 
			'imagesPack'=>'noia',//default: noia 
			'iconText'=>true, // default : false 
			'header'=>'srbac.views.authitem.header',//default : srbac.views.authitem.header,must be an existing alias 
			'footer'=>'srbac.views.authitem.footer',//default: srbac.views.authitem.footer,must be an existing alias 
			'showHeader'=>true,// default: false 
			'showFooter'=>true,// default: false 
			'alwaysAllowedPath'=>'srbac.components',// default: srbac.components,must be an existing alias 
		),
	),

	// application components
	'components'=>array(
		'user'=>array(
			// enable cookie-based authentication
			'allowAutoLogin'=>true,
		),
		'session'=>array(
			'timeout'=>3600,
		),

		'authManager'=>array(
			'class'=>'CDbAuthManager',// Manager 的类型
			'connectionID'=>'db',// The database component used
			'itemTable'=>'authitem',// The itemTable name (default:authitem)       授权项表  
			'assignmentTable'=>'authassignment',// The assignmentTable name (default:authassignment)    权限分配表
			'itemChildTable'=>'authitemchild',// The itemChildTable name (default:authitemchild)  任务对应权限表
		),
		// uncomment the following to enable URLs in path-format
		/*
		'urlManager'=>array(
			'urlFormat'=>'path',
			'rules'=>array(
				'<controller:\w+>/<id:\d+>'=>'<controller>/view',
				'<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
				'<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
			),
		),
		*/

		// uncomment the following to use a MySQL database
		/*
		'db'=>array(
			'connectionString' => 'mysql:host=127.0.0.1;dbname=epiaowang_main',
			'emulatePrepare' => true,
			'username' => 'root',
			'password' => 'epwzswyyxc10041004',
			'charset' => 'utf8',
			'tablePrefix' => 'tb_',
		),*/
		'db'=>array(
			'connectionString' => 'mysql:host=127.0.0.1;dbname=epiaowang_new',
			'emulatePrepare' => true,
			'username' => 'root',
			'password' => 'root',
			'charset' => 'utf8',
			'tablePrefix' => 'tb_',
			//'enableProfiling'=>YII_DEBUG,
			//'enableParamLogging'=>YII_DEBUG,
		),
		// 'db'=>array(
		// 	'connectionString' => 'mysql:host=101.200.72.120;dbname=epiaowang_new',
		// 	'emulatePrepare' => true,
		// 	'username' => 'root',
		// 	'password' => '9qyisSVxeXTN',
		// 	'charset' => 'utf8',
		// 	'tablePrefix' => 'tb_',
		// 	//'enableProfiling'=>YII_DEBUG,
		// 	//'enableParamLogging'=>YII_DEBUG,
		// ),
		
		'errorHandler'=>array(
			// use 'site/error' action to display errors
            'errorAction'=>'site/error',
        ),
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning',
				),
				// uncomment the following to show log messages on web pages
				/*
				array(
					'class'=>'CWebLogRoute',
				),
				*/
				// array(  
    //                 'class'=>'CWebLogRoute',  
    //                 'levels'=>'trace',//提示的级别  
    //                 'categories'=>'system.db.*',
    //             ), 
			),
		),
		
		'smarty'=>array(
			'class'=>'application.extensions.CSmarty',
		),

		'redis'=>array(
			'class'=>'ext.redis.CRedisCache',
			'servers'=>array(
				array(
					'host'=>'101.200.72.120',
					'port'=>6379,
				),
			),
		),
			
	),

	// application-level parameters that can be accessed
	// using Yii::app()->params['paramName']
	'params'=>array(
		// this is used in contact page
		'adminEmail'=>'webmaster@example.com',
		'baseUrl' => 'http://m.epiaowang.com:8080/',
		'testUrl' => 'https://apitest.epiaowang.com',
		'interfPW_WXAPP' => '37510bbba88f4d346af54817b63e1162',		//微信小程序接口内部通用口令
		
		'interfPW_Partner' => array(				//下游合作方接入内部通用口令
			'KouL' => '1df62bbbc5c1956454897b163e1c67d5',
		),
		//太和接口
		'taihe_Partner' => 'dba2911b628e925c46dad10736b8a7d8',
		
		//微信配置
		'wxConf'=>array(
// 			'WXHomeUrl' => 'https://mp.weixin.qq.com/mp/profile_ext?action=home&__biz=MjM5MDI3NTg2MA==&scene=110#wechat_redirect',	//微信Home（2016-09-22微信更新6.3.27版本后，此功能已经被屏蔽）
//			'WXHomeUrl'=> 'http://dwz.cn/4lA0T9',	//借助第三方的生成平台
			'WXHomeUrl'=> 'http://m.epiaowang.com/wx/index.php?s=/home/Index/leaflets/token/gh_3c71eac0546b.html',	//我们自有的推广页面
			'getOpenidUrl'=> 'http://m.epiaowang.com/project/index.php?r=Tools/GetWXOpenID',//获取Openid的code接受Url（一定要m.epiaowang.com域名）
			'getOpenid_RetuUrl_ParamSpace'=> '$$',	//获取openid时，如果returnUrl中需要传入参数，需用此分隔符代替‘&’
		),
		
		//积分选项配置
		'scoreConf' => array(
			
			'keyConf' => array(						//积分配置key（用于表字段的读取）
					'wxShare' => 'wx_share',				//微信分享	
					'sourcePoint' => 'source_point',		//积分来源分值	
					'sourceDesc' => 'source_desc',			//积分来源描述	
					'sourcePrize' => 'source_prize',		//积分来源奖品
					
					//V1.2 add at 20161229
					'tip' => 'store_tip',					//小E说
					'recommend' => 'store_recommend',		//商品推荐位
					'banner' => 'store_banner',				//商品banner位
			),

			'sourceConf' => array(				//积分来源配置
					'user_invite' => array('name' => '邀请好友', 'source_id' => 1),			
					'buy_ticket_online' => array('name' => '在线选座订单', 'source_id' => 2),
					'banding_wx' => array('name' => '绑定有礼', 'source_id' => 3),
					'join_act' => array('name' => '活动券订单', 'source_id' => 4),
//					'system_opera' => array('name' => '系统操作', 'source_id' => 5),
					'sign_in' => array('name' => '签到奖励', 'source_id' => 6),
			),
		),
		
		//二维码生成配置
		'qrConf' => array(
			
			'eLogo' => 'images/logo.jpg',					//e票网logo地址
//			'postersBGPic' => 'images/haibao_03.png',		//海报背景图片地址
			'errorCorrectionLevel' => 'H',					//容错级别（L级可纠正约7%错误、M级可纠正约15%错误、Q级可纠正约25%错误、H级可纠正约30%错误）
			'matrixPointSize' => 6,							//生成图片大小
			'qrPath' => array(								//二维码生成地址
					'prime' => 'images/qr/%d.png',			//二维码原图地址
					'logo' => 'images/qr/%d_logo.png',		//带E票网的Logo的二维码地址
					'posters' => 'images/qr/%d_posters.png',		//带E票网的Logo的二维码地址
			),
		),
		
		//分页配置
		'pageInfo' => array(
			'pageSize' => 10,					//每页数量（通用）
			'pageSize_goodsList' => 50,			//每页数量（前台商品列表）
		),
		
		//批次号配置
		'batchInfo' => array(
			'scoreStore' => array(				//积分商城（卡券类型：电影卡-dyk；现金券-xjq）
				'invite' => 'sc%s%s',						//邀请好友（sc+卡券类型+券id）
				'exchange_goods' => 'sc%d%s%s',				//兑换商品（sc+商品id+卡券类型+券id）
				'wechat_favorable'=>'xcx%s%s',			//小程序领优惠券（小程序首字母+现金券/电影卡首字母+券id）
			),
		),
		
		//电影类型
		'movieType' => array(
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
		),
		
		//支付配置
		'payType' => array(
			'weixin' => '400001',				//微信支付
			'alipay' => '400002',				//支付宝支付
			'account' => '400003',				//账户余额支付
		),

		'provinceConf' => array(
			'101' => '北京市',
			'102' => '天津市',
			'103' => '河北省',
			'104' => '山西省',
			'105' => '内蒙古',
			'106' => '辽宁省',
			'107' => '吉林省',
			'108' => '黑龙江省',
			'109' => '上海市',
			'110' => '江苏省',
			'111' => '浙江省',
			'112' => '安徽省',
			'113' => '福建省',
			'114' => '江西省',
			'115' => '山东省',
			'116' => '河南省',
			'117' => '湖北省',
			'118' => '湖南省',
			'119' => '广东省',
			'120' => '广西自治区',
			'121' => '海南省',
			'122' => '重庆市',
			'123' => '四川省',
			'124' => '贵州省',
			'125' => '云南省',
			'126' => '西藏自治区',
			'127' => '陕西省',
			'128' => '甘肃省',
			'129' => '青海省',
			'130' => '宁夏回族自治区',
			'131' => '新疆维吾尔自治区',
			'132' => '香港特别行政区',
			'133' => '澳门特别行政区',
			'134' => '台湾省'),
	),
);