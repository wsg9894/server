<?php

/**
 * GameController - 游戏合作控制器（‘流量变现’尝试）
 * @author yanglipeng
 * @version V1.0
 */
Yii::import('application.modules.partner.models.process.*');
date_default_timezone_set('PRC');
/**
 * SiteFilter - 过滤器
 */
class SiteFilter extends CFilter
{
	public  static $gid;
	public  static $pid;
	function __construct()
	{
		$a = func_get_args();
		$i = func_num_args();
		if (method_exists($this,$f='__construct'.$i)) {
			call_user_func_array(array($this,$f),$a);
		}
	}

	function __construct1($gid)
	{
		//游戏id
		self::$gid = $gid;
	}
	function __construct2($gid,$pid)
	{
		//游戏id
		self::$gid = $gid;
		//礼包id
		self::$pid = $pid;
	}
	/**
	 * 动作被执行之后应用的逻辑
	 */
	protected function postFilter ($filterChain)
	{
		$pageInfo = UserProcess::getPageSessionInfo();
		$sUserIP = GeneralFunc::getIP();
		//显示访问用户的浏览器信息
		$sTermBrowser = GeneralFunc::determinebrowser();
		//获取设备信息
		$sTermType = GeneralFunc::deviceMess();
		//获取版本信息
		$version = GeneralFunc::versionPhone();
		$sTermOS = $version[0];
		$sTermOSVersion = $version[1];
		//获取用户设定的操作系统的语言
		$sTermOSLanguage = GeneralFunc::language();
		//根据页面的id获取页面的配置
		if(isset($_REQUEST['from'])&&!empty($_REQUEST['from'])){
			//来源页面
			$iFromCid=$_REQUEST['from'];
			$fromPage = GameProcess::getPageInfo($iFromCid);
		}
		if($pageInfo){
			//当前页面
			$iCurCid=$pageInfo['iCurCid'];
			$thisPage = GameProcess::getPageInfo($iCurCid);
		}
		$gid = 0;
		if(self::$gid){
			$gid = self::$gid;
		}
		$pid = 0;
		if(self::$pid){
			$pid = self::$pid;
		}
		if($pid!=0&&$gid==0&&$iFromCid==8){
			$gid = GameProcess::getGamePackageBypid($pid,array('gid'))['gid'];
		}
		if($fromPage&&$thisPage){
			$sCurPConfName = $thisPage['sName'];
			$sFromPConfName = $fromPage['sName'];
			GameProcess::addUserPvLog($iCurCid,$sCurPConfName,$iFromCid,$sFromPConfName,$gid,$pid,$sTermOS,$sTermOSVersion,$sTermOSLanguage,$sTermType,$sTermBrowser,$sUserIP);
		}

	}
}//end class

class GameController extends Controller
{
	public function filters()
	{
		return array(
			array(
				'application.filters.SiteFilter',
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
	 * 游戏入口
	 */
	public function actionIndex()
	{
		//用户信息
		$uSessInfo = UserProcess::getLoginSessionInfo();
//存页面的配置id   iCurCid
		UserProcess::setPageSessionInfo(3);
		//如果是微信用户，则要获取Openid
		if(Weixin::isWxVisitor() and empty($uSessInfo['openid']))
		{
			$retuUrl = Yii::app()->params['baseUrl'].'project/index.php?r=partner/Game/Index';
			UserProcess::getOpenidOnOAuth($retuUrl);
			exit(0);
		}else{
			//存用户访问记录，如果用户是从微信进入并且是自动登录的情况下
			if(!empty($uSessInfo['sPhone'])){
				GameProcess::addUserAccessLog($uSessInfo['sPhone'],GeneralFunc::deviceMess());
			}
		}
		//首页banner位列表
		$selFields = array('bid', 'name', 'pic', 'url');
		$filter = sprintf("is_del=0 AND `status`=1 AND `cate_id`=1 AND starttime<=NOW() AND endtime>=NOW()");
		$bannerList = BannerProcess::getBannerList($selFields, $filter);
		//print_r($bannerList);
		$from=0;
		$selFields = array('gid', 'gname', 'describe','introduce', 'img','cate','virtual_people','num_people','is_install','androidApk','iphoneApk','h5Url');
		if(isset($_REQUEST['from'])){
			$from=$_REQUEST['from'];
			if($_REQUEST['from']==21){
				//下载榜
				$filter = sprintf("is_del=0 AND `status`=1 and `is_install`=1 and `isDownList`=1 ORDER BY (`num_people`+`virtual_people`) DESC");
			}else if($_REQUEST['from']==22){
				//新品榜
				$filter = sprintf("is_del=0 AND `status`=1 AND `is_install`=1 and `isNewList`=1 ORDER BY `new_sort_num` DESC");
			}else if($_REQUEST['from']==23){
				//影视同期榜
				$filter = sprintf("is_del=0 AND `status`=1 AND `is_install`=1 and `isMoviesList`=1 ORDER BY `movie_sort_num` DESC");
			}else{
				//火热游戏
				$filter = sprintf("is_del=0 AND `status`=1 AND `is_install`=1 and `is_hot`=1 ORDER BY `index_sort_num` DESC");
			}
		}else{
			//火热游戏
			$filter = sprintf("is_del=0 AND `status`=1 AND `is_install`=1 and `is_hot`=1 ORDER BY `index_sort_num` DESC");
		}
		$hotGameList = GameProcess::getHotGameList($selFields, $filter);
		$GameList = array();
		foreach($hotGameList as &$v){
			if(mb_strlen($v['describe'],'utf-8')>19){
				$v['describe'] = mb_substr($v['describe'],0,19,'utf-8').'...';
			}
			if($v['is_install'] == 1){
				$v['install'] = '安装';
				//判断人数格式
				$v['num_people'] = GeneralFunc::peopleFormat($v['num_people']+$v['virtual_people']).'人下载';
				if(GeneralFunc::isIphone()){
					if($v['iphoneApk'] != ""){
						$GameList[] = $v;
					}
				}else{
					if($v['androidApk'] != ""){
						$GameList[] = $v;
					}
				}
			}else{
				$v['install'] = '打开';
				$v['num_people'] = GeneralFunc::peopleFormat($v['num_people']+$v['virtual_people']).'人在玩';
				if($v['h5Url'] != ""){
					$GameList[] = $v;
				}
			}

		}
		Yii::app()->smarty->assign('BANNERLIST',$bannerList);
		Yii::app()->smarty->assign('HOTBANNERLIST',$GameList);
		Yii::app()->smarty->assign('FROM',$from);
		Yii::app()->smarty->display('movieGame/index.html');
	}

	//安装或者打开操作
	public function actionInstall(){
		$gid = empty($_REQUEST['gid'])?0:$_REQUEST['gid'];
		$GameDetails = GameProcess::getGameDetails($gid);

		if($gid == 0 || empty($GameDetails))
		{
			echo json_encode(array('ERRORGAME'=>0,'HREF'=>'/project/index.php?r=partner/Game/Index'));exit();
		}
		$iUserID = 0;
		$userInfo = UserProcess::getLoginSessionInfo();
		if(!empty($userInfo['iUserID'])){
			$iUserID = $userInfo['iUserID'];
		}
		//增加下载量
		GameProcess::updateGameClicks($gid);
		//增加下载量
		//GameProcess::updateGameDown($gid);
		//表单提交，新增用户浏览记录入库
		$userBrownLog = array(
			'iUserID'=>$iUserID,
			'gid'=>$gid
		);
		$filter = sprintf(" `iUserID`=%d AND `gid`=%d ",$iUserID,$gid);
		if($iUserID != 0){
			if(!GameProcess::getBrowseLog(array(),$filter)){
				$aid = UserProcess::addUserBrowseLog($userBrownLog);
			}
		}
		if($GameDetails['is_install']){
			if(GeneralFunc::isIphone()){
				//增加各个页面不同游戏的下载量
				if(GameProcess::addPageLoadByGid($gid)){
					echo json_encode(array('ERRORGAME'=>'isIphone','HREF'=>$GameDetails['iphoneApk']));
				}
				exit();
			}else{
				if(GeneralFunc::isWeixinBrowser()){
					echo json_encode(array('ERRORGAME'=>'isWeixin','HREF'=>""));exit();
				}else{
					if(GameProcess::addPageLoadByGid($gid)) {
						echo json_encode(array('ERRORGAME' => 'isAndroid', 'HREF' => $GameDetails['androidApk']));
					}
					exit();
				}
			}
		}else{
			if(GameProcess::addPageLoadByGid($gid)) {
				echo json_encode(array('ERRORGAME' => 'h5Url', 'HREF' => $GameDetails['h5Url']));
			}
			exit();
		}
	}

	//免安装游戏列表
	public function actionNoInsert()
	{
		//用户信息
		$uSessInfo = UserProcess::getLoginSessionInfo();
//存页面的配置id   iCurCid
		UserProcess::setPageSessionInfo(11);
		//如果是微信用户，则要获取Openid
		if(Weixin::isWxVisitor() and empty($uSessInfo['openid']))
		{
			$retuUrl = Yii::app()->params['baseUrl'].'project/index.php?r=partner/Game';
			UserProcess::getOpenidOnOAuth($retuUrl);
			exit(0);
		}
		//免安装banner位列表
		$selFields = array('bid', 'name', 'pic', 'url');
		$filter = sprintf("is_del=0 AND `status`=1 AND `cate_id`=2 AND starttime<=NOW() AND endtime>=NOW()");
		$bannerList = BannerProcess::getBannerList($selFields, $filter);
		//免安装游戏
		$selFields = array('gid', 'gname', 'describe','introduce', 'img','cate','virtual_people','num_people','is_install','androidApk','iphoneApk','h5Url');
		$filter = sprintf("is_del=0 AND `status`=1 AND `is_install` = 0 ORDER BY `uninstall_sort_num` DESC");
		$hotGameListList = GameProcess::getHotGameList($selFields, $filter);
		if($bannerList){
			Yii::app()->smarty->assign('BANNERLIST',$bannerList);
		}else{
			Yii::app()->smarty->assign('BANNERLIST',"");
		}

		foreach($hotGameListList as &$v){
			$v['num_people'] = GeneralFunc::peopleFormat($v['num_people']+$v['virtual_people']).'人在玩';
		}

		Yii::app()->smarty->assign('HOTBANNERLIST',$hotGameListList);
		Yii::app()->smarty->display('movieGame/noinstall.html');
	}

	//影视同期榜
	public function actionRanking()
	{
		//用户信息
		$uSessInfo = UserProcess::getLoginSessionInfo();
		//存页面的配置id   iCurCid
		UserProcess::setPageSessionInfo(4);
		//如果是微信用户，则要获取Openid
		if(Weixin::isWxVisitor() and empty($uSessInfo['openid']))
		{
			$retuUrl = Yii::app()->params['baseUrl'].'project/index.php?r=partner/Game';
			UserProcess::getOpenidOnOAuth($retuUrl);
			exit(0);
		}

		//游戏排行榜
		$selFields = array('gid', 'gname', 'iphoneApk','androidApk','describe','introduce', 'img','cate','virtual_people','num_people','is_install','isMoviesList','dBeginTime');
		$filter = sprintf("is_del=0 AND `status`=1 and `is_install`=1 and `isMoviesList`=1 ORDER BY `movie_sort_num` DESC");
		$rankingGameList = GameProcess::getHotGameList($selFields, $filter);
		$arrankingGameList = array();
		foreach($rankingGameList as &$v){
			if(mb_strlen($v['describe'],'utf-8')>17){
				$v['describe'] = mb_substr($v['describe'],0,17,'utf-8').'...';
			}
			if($v['is_install'] == 1){
				$v['install'] = '安装';
				//判断人数格式
				$v['num_people'] = GeneralFunc::peopleFormat($v['num_people']+$v['virtual_people']).'人下载';
				if(GeneralFunc::isIphone()){
					if($v['iphoneApk'] != ""){
						//影视同期榜
						if($v['isMoviesList'] == 1){
							$year = date("Y",strtotime($v['dBeginTime']));
							$month = date("n",strtotime($v['dBeginTime']));
							$yearNow = date("Y",time());
							if($year < $yearNow ){
								$v['month'] = $month.'月影视同期（'.$year.'年）';
							}else{
								$v['month'] = $month.'月影视同期';
							}
							$arrankingGameList[$v['month']][] = $v;
						}
					}
				}else{
					if($v['androidApk'] != ""){
						if($v['isMoviesList'] == 1){
							$year = date("Y",strtotime($v['dBeginTime']));
							$month = date("n",strtotime($v['dBeginTime']));
							$yearNow = date("Y",time());
							if($year < $yearNow ){
								$v['month'] = $month.'月影视同期（'.$year.'年）';
							}else{
								$v['month'] = $month.'月影视同期';
							}
							$arrankingGameList[$v['month']][] = $v;
						}
					}
				}
				//影视同期榜
			}else{
				$v['install'] = '打开';
				$v['num_people'] = GeneralFunc::peopleFormat($v['num_people']+$v['virtual_people']).'人在玩';
			}
		}
		Yii::app()->smarty->assign('ARRANKINGGAMELIST',$arrankingGameList);
		Yii::app()->smarty->display('movieGame/ranking.html');
	}

	//下载榜
	public function actionDownload()
	{
		//用户信息
		$uSessInfo = UserProcess::getLoginSessionInfo();
		//存页面的配置id   iCurCid
		UserProcess::setPageSessionInfo(5);
		//如果是微信用户，则要获取Openid
		if(Weixin::isWxVisitor() and empty($uSessInfo['openid']))
		{
			$retuUrl = Yii::app()->params['baseUrl'].'project/index.php?r=partner/Game';
			UserProcess::getOpenidOnOAuth($retuUrl);
			exit(0);
		}

		//游戏排行榜
		$selFields = array('gid', 'gname', 'iphoneApk','androidApk','describe','introduce', 'img','cate','virtual_people','num_people','is_install','isDownList','dBeginTime');
		$filter = sprintf("is_del=0 AND `status`=1 and `is_install`=1 and `isDownList`=1 ORDER BY (`num_people`+`virtual_people`) DESC");
		$rankingGameList = GameProcess::getHotGameList($selFields, $filter);
		$downGameList = array();
		foreach($rankingGameList as &$v){
			if(mb_strlen($v['describe'],'utf-8')>17){
				$v['describe'] = mb_substr($v['describe'],0,17,'utf-8').'...';
			}
			if($v['is_install'] == 1){
				$v['install'] = '安装';
				//判断人数格式
				$v['num_people'] = GeneralFunc::peopleFormat($v['num_people']+$v['virtual_people']).'人下载';
				if(GeneralFunc::isIphone()){
					if($v['iphoneApk'] != ""){
						//下载榜
						if($v['isDownList'] == 1){
							$downGameList[] = $v;
						}
					}
				}else{
					if($v['androidApk'] != ""){
						//下载榜
						if($v['isDownList'] == 1){
							$downGameList[] = $v;
						}
					}
				}
				//影视同期榜
			}else{
				$v['install'] = '打开';
				$v['num_people'] = GeneralFunc::peopleFormat($v['num_people']+$v['virtual_people']).'人在玩';
			}
		}
		Yii::app()->smarty->assign('DOWNGAMELIST',$downGameList);
		Yii::app()->smarty->display('movieGame/downLoad.html');
	}

	//火热榜
	public function actionHotlist()
	{
		//用户信息
		$uSessInfo = UserProcess::getLoginSessionInfo();
		//存页面的配置id   iCurCid
		UserProcess::setPageSessionInfo(6);
		//如果是微信用户，则要获取Openid
		if(Weixin::isWxVisitor() and empty($uSessInfo['openid']))
		{
			$retuUrl = Yii::app()->params['baseUrl'].'project/index.php?r=partner/Game';
			UserProcess::getOpenidOnOAuth($retuUrl);
			exit(0);
		}

		//游戏排行榜
		$selFields = array('gid', 'gname', 'iphoneApk','androidApk','describe','introduce', 'img','cate','virtual_people','num_people','is_install','isPopList','dBeginTime');
		$filter = sprintf("is_del=0 AND `status`=1 and `is_install`=1 and `isPopList`=1 ORDER BY `pop_sort_num` DESC");
		$rankingGameList = GameProcess::getHotGameList($selFields, $filter);
		$hotGameList = array();
		foreach($rankingGameList as &$v){
			if(mb_strlen($v['describe'],'utf-8')>17){
				$v['describe'] = mb_substr($v['describe'],0,17,'utf-8').'...';
			}
			if($v['is_install'] == 1){
				$v['install'] = '安装';
				//判断人数格式
				$v['num_people'] = GeneralFunc::peopleFormat($v['num_people']+$v['virtual_people']).'人下载';
				if(GeneralFunc::isIphone()){
					if($v['iphoneApk'] != ""){
						//火热榜
						if($v['isPopList'] == 1){
							$hotGameList[] = $v;
						}
					}
				}else{
					if($v['androidApk'] != ""){
						//火热榜
						if($v['isPopList'] == 1){
							$hotGameList[] = $v;
						}
					}
				}
				//影视同期榜
			}else{
				$v['install'] = '打开';
				$v['num_people'] = GeneralFunc::peopleFormat($v['num_people']+$v['virtual_people']).'人在玩';
			}
		}
		Yii::app()->smarty->assign('HOTGAMELIST',$hotGameList);
		Yii::app()->smarty->display('movieGame/hotlist.html');
	}

//新品榜
	public function actionNewlist()
	{
		//用户信息
		$uSessInfo = UserProcess::getLoginSessionInfo();
		//存页面的配置id   iCurCid
		UserProcess::setPageSessionInfo(7);
		//如果是微信用户，则要获取Openid
		if(Weixin::isWxVisitor() and empty($uSessInfo['openid']))
		{
			$retuUrl = Yii::app()->params['baseUrl'].'project/index.php?r=partner/Game';
			UserProcess::getOpenidOnOAuth($retuUrl);
			exit(0);
		}

		//游戏排行榜
		$selFields = array('gid', 'gname', 'iphoneApk','androidApk','describe','introduce', 'img','cate','virtual_people','num_people','is_install','isNewList','dBeginTime');
		$filter = sprintf("is_del=0 AND `status`=1 and `is_install`=1 and `isNewList`=1 ORDER BY `new_sort_num` DESC");
		$rankingGameList = GameProcess::getHotGameList($selFields, $filter);
		$newGameList = array();
		foreach($rankingGameList as &$v){
			if(mb_strlen($v['describe'],'utf-8')>17){
				$v['describe'] = mb_substr($v['describe'],0,17,'utf-8').'...';
			}
			if($v['is_install'] == 1){
				$v['install'] = '安装';
				//判断人数格式
				$v['num_people'] = GeneralFunc::peopleFormat($v['num_people']+$v['virtual_people']).'人下载';
				if(GeneralFunc::isIphone()){
					if($v['iphoneApk'] != ""){
						//新品榜
						if($v['isNewList'] == 1){
							$newGameList[] = $v;
						}
					}
				}else{
					if($v['androidApk'] != ""){
						//新品榜
						if($v['isNewList'] == 1){
							$newGameList[] = $v;
						}
					}
				}
				//影视同期榜
			}else{
				$v['install'] = '打开';
				$v['num_people'] = GeneralFunc::peopleFormat($v['num_people']+$v['virtual_people']).'人在玩';
			}
		}
		Yii::app()->smarty->assign('NEWGAMELIST',$newGameList);
		Yii::app()->smarty->display('movieGame/newlist.html');
	}


	//游戏详情页
	public function actionGamedetail()
	{
		$gid = empty($_REQUEST['gid'])?0:$_REQUEST['gid'];
		$GameDetails = GameProcess::getGameDetails($gid);
		if($gid == 0 || empty($GameDetails))
		{
			GeneralFunc::alert('请选择游戏列表的游戏');
			GeneralFunc::gotoUrl('/project/index.php?r=partner/Game');
			exit(0);
		}
		//存页面的配置id   iCurCid
		UserProcess::setPageSessionInfo(8);
		//游戏id赋值
		new SiteFilter($gid);
		$iUserID = 0;
		$userInfo = UserProcess::getLoginSessionInfo();
		if(!empty($userInfo['iUserID'])){
			$iUserID = $userInfo['iUserID'];
		}
		//增加点击量
		//GameProcess::updateGameClicks($gid);
		//表单提交，新增用户浏览记录入库
		$userBrownLog = array(
			'iUserID'=>$iUserID,
			'gid'=>$gid
		);
		$filter = sprintf(" `iUserID`=%d AND `gid`=%d ",$iUserID,$gid);
		//添加用户浏览记录
//		if($iUserID != 0){
//			if(!GameProcess::getBrowseLog(array(),$filter)){
//				$aid = UserProcess::addUserBrowseLog($userBrownLog);
//			}
//		}
		//获取游戏截图
		$gameImg = GameProcess::getGameImgListBygid($gid);
		//游戏礼包列表
		$filter = sprintf(" `gid`=%d and is_del=0 AND `status`=1 and endtime>=NOW() order by weight desc",$gid);
		$gamePackageList = GameProcess::getGamePackageList(array(), $filter);

		//获取游戏礼包个数-不包含过期的礼包
		$filter = sprintf("is_del=0 AND `status`=1 and endtime>=NOW() and gid=%d",$GameDetails['gid']);
		$gamePackageCount = GameProcess::getGamePackageCount($filter);
		$num_package = $gamePackageCount[0]['num_package'];

		//判断礼包库存-不包含过期的礼包
		foreach($gamePackageList as &$v){
			$filter = sprintf("endtime>=NOW() and iUserID = 0 and pid=%d",$v['pid']);
			$MyPackageCount = GameProcess::getMyPackageCount($filter);
			$v['getCount'] = $MyPackageCount[0]['num_count'];
		}
//是否跳转的标志
		$href=0;
		//检测是否为iPhone
		if($GameDetails['is_install'] == 1){
			if(GeneralFunc::isIphone()){
				$safety = "";
				if($GameDetails['iphoneApk']!=""){
					$href=1;
				}
			}else{
				$safety = "<h4>无毒    安全监测</h4>";
				if($GameDetails['androidApk']!=""){
					$href=1;
				}
			}
			$install = "安装";
		}else{
			if($GameDetails['h5Url']!=""){
				$href=1;
			}
			$safety = "";
			$install = "打开";
		}

		//一句话描述
		if(mb_strlen($GameDetails['describe'],'utf-8')>22){
			$GameDetails['describe'] = mb_substr($GameDetails['describe'],0,22,'utf-8').'...';
		}

		//影片详情展示三行
		$GameDetails['introduce'] = str_replace(" "," ",str_replace("\n","<br/>",$GameDetails['introduce']));
		if(mb_strlen($GameDetails['introduce'],'utf-8')<68){
			$info = $GameDetails['introduce'];
			$flag = 0;
		}else{
			$info = mb_substr($GameDetails['introduce'],0,68,'utf-8').'...';
			$flag = 1;
		}

		//视频
		Yii::app()->smarty->assign('P_VIDEO',$GameDetails['p_video']);
		//宣传图片
		Yii::app()->smarty->assign('GID',$GameDetails['gid']);
		Yii::app()->smarty->assign('IMG',$GameDetails['img']);
		Yii::app()->smarty->assign('DESCRIBE',$GameDetails['describe']);
		Yii::app()->smarty->assign('INSTALL',$install);
		Yii::app()->smarty->assign('SAFETY',$safety);
		Yii::app()->smarty->assign('P_IMG',$GameDetails['p_img']);
		Yii::app()->smarty->assign('GAMESIZE',$GameDetails['gamesize']);
		Yii::app()->smarty->assign('GNAME',$GameDetails['gname']);
		Yii::app()->smarty->assign('CATE',$GameDetails['cate']);
		Yii::app()->smarty->assign('NUM_PEOPLE',GeneralFunc::peopleFormat($GameDetails['num_people']+$GameDetails['virtual_people']));
		Yii::app()->smarty->assign('INFO',$info);
		Yii::app()->smarty->assign('FLAG',$flag);
		Yii::app()->smarty->assign('NUMPACKAGE',$num_package);
		Yii::app()->smarty->assign('INTRODUCE',$GameDetails['introduce']);
		Yii::app()->smarty->assign('GAMEIMG',$gameImg);
		Yii::app()->smarty->assign('HREF',$href);
		//权限说明
		if($GameDetails['auth_desc'] != ""){
			if(GeneralFunc::isIphone()){
				$GameDetails['auth_desc'] = "";
			}else{
				$GameDetails['auth_desc'] = str_replace(" ","</br>",$GameDetails['auth_desc']);
			}
		}
		Yii::app()->smarty->assign('AUTH_DESC',$GameDetails['auth_desc']);
		//礼包
		Yii::app()->smarty->assign('PACKLIST',$gamePackageList);
		Yii::app()->smarty->display('movieGame/gamedetail.html');
	}

	//游戏礼包列表页
	public function actionGamepackage()
	{
		//用户信息
		$uSessInfo = UserProcess::getLoginSessionInfo();
		//存页面的配置id   iCurCid
		UserProcess::setPageSessionInfo(9);
		//如果是微信用户，则要获取Openid
		if(Weixin::isWxVisitor() and empty($uSessInfo['openid']))
		{
			$retuUrl = Yii::app()->params['baseUrl'].'project/index.php?r=partner/Game';
			UserProcess::getOpenidOnOAuth($retuUrl);
			exit(0);
		}

		//包含游戏礼包的游戏列表
//		$selFields = array('gid', 'gname', 'describe','iphoneApk','androidApk','h5Url','img','num_package','is_install');
//		$filter = sprintf("is_del=0 AND `status`=1 and `num_package`>0");
//		$gameList = GameProcess::getHotGameList($selFields, $filter);
		$gameList = GameProcess::getGameListByPackage();
		$gameList1=array();


		//游戏礼包列表
		$filter = sprintf("is_del=0 AND `status`=1 and endtime>=NOW() order by weight desc");
		$gamePackageList = GameProcess::getGamePackageList(array(), $filter);

		//获取游戏礼包个数-不包含过期的礼包
		$filter = sprintf("is_del=0 AND `status`=1 and endtime>=NOW() group by gid");
		$gamePackageCount = GameProcess::getGamePackageCount($filter);

		//判断礼包库存-不包含过期的礼包
		foreach($gamePackageList as &$v){
			$filter = sprintf("endtime>=NOW() and iUserID = 0 and pid=%d",$v['pid']);
			$MyPackageCount = GameProcess::getMyPackageCount($filter);
			$v['num_count'] = $MyPackageCount[0]['num_count'];
			if(mb_strlen($v['info'],'utf-8')>17){
				$v['info'] = mb_substr($v['info'],0,17,'utf-8').'...';
			}
		}

		foreach($gameList as &$v){
			$v['num_package'] = 0;
			foreach($gamePackageCount as $k => $v1){
				if($v['gid'] == $v1['gid']){
					$v['num_package'] = $v1['num_package'];
					break;
				}
			}
			if(mb_strlen($v['describe'],'utf-8')>18){
				$v['describe'] = mb_substr($v['describe'],0,18,'utf-8').'...';
			}
			if($v['is_install'] == 1){
				if(GeneralFunc::isIphone()){
					if($v['iphoneApk']!=""){
						$gameList1[]=$v;
					}
				}else{
					if($v['androidApk']!=""){
						$gameList1[]=$v;
					}
				}
			}else{
				if($v['h5Url']!="") {
					$gameList1[] = $v;
				}
			}

		}
		Yii::app()->smarty->assign('GAMELIST',$gameList1);
		Yii::app()->smarty->assign('GAMEPACKAGELIST',$gamePackageList);
		Yii::app()->smarty->display('movieGame/package.html');
	}

	//游戏礼包详情页
	public function actionPackagedetail()
	{
		$pid = empty($_REQUEST['pid'])?0:$_REQUEST['pid'];
		$gid = empty($_REQUEST['gid'])?0:$_REQUEST['gid'];
		$flag = isset($_REQUEST['flag'])?$_REQUEST['flag']:0;
		//存页面的配置id   iCurCid
		UserProcess::setPageSessionInfo(10);
		$PackageDetails = GameProcess::getGamePackageBypid($pid);
		if($pid == 0 || empty($PackageDetails))
		{
			GeneralFunc::alert('请选择礼包列表的礼包');
			GeneralFunc::gotoUrl("Back");
			exit(0);
		}
		new SiteFilter($gid,$pid);
		$gameList = GameProcess::getGameDetails($PackageDetails['gid']);
		if($gameList['is_install'] == 1){
			$gameList['install'] = '安装';
			//判断人数格式
		}else{
			$gameList['install'] = '打开';
		}

		$filter = sprintf("iUserID != 0 and pid=%d",$PackageDetails['pid']);
		$MyPackageCount = GameProcess::getMyPackageCount($filter);
		//库存
		$filter = sprintf("endtime>=NOW() and iUserID = 0 and pid=%d",$PackageDetails['pid']);
		$MyPackageCount = GameProcess::getMyPackageCount($filter);
		$numCount = $MyPackageCount[0]['num_count'];

		$content = str_replace("\n","<br/>",$PackageDetails['content']);
		$exchange = str_replace("\n","<br/>",$PackageDetails['exchange']);
		Yii::app()->smarty->assign('GID',$PackageDetails['gid']);
		Yii::app()->smarty->assign('PID',$PackageDetails['pid']);
		Yii::app()->smarty->assign('PIC',$gameList['img']);
		Yii::app()->smarty->assign('INSTALL',$gameList['install']);
		Yii::app()->smarty->assign('NAME',$PackageDetails['name']);
		Yii::app()->smarty->assign('GETCOUNT',$PackageDetails['getCount']);
		Yii::app()->smarty->assign('NUMPEOPLE',GeneralFunc::peopleFormat($PackageDetails['num_people']+$MyPackageCount[0]['num_count']));
		Yii::app()->smarty->assign('DESCRIBE',$content);
		Yii::app()->smarty->assign('EXCHANGE',$exchange);
		Yii::app()->smarty->assign('FLAG',$flag);
		Yii::app()->smarty->assign('NUMCOUNT',$numCount);
		Yii::app()->smarty->display('movieGame/packagedetail.html');
	}

	//获取游戏礼包
	public function actionGetPackage()
	{
		$pid = empty($_REQUEST['pid'])?0:$_REQUEST['pid'];
		$PackageDetails = GameProcess::getGamePackageBypid($pid);
		if($pid == 0 || empty($PackageDetails))
		{
			GeneralFunc::alert('请选择礼包列表的礼包');
			GeneralFunc::gotoUrl('/project/index.php?r=partner/Game');
			exit(0);
		}
		$pageInfo = UserProcess::getPageSessionInfo();
		$iCurCid = 2;
		if($pageInfo){
			$iCurCid = $pageInfo['iCurCid'];
		}
		$iUserID = 0;
		$userInfo = UserProcess::getLoginSessionInfo();
		if(!empty($userInfo['iUserID'])){
			$iUserID = $userInfo['iUserID'];
			//获取常玩游戏
			$filter = sprintf(" `iUserID`=%d ",$iUserID);
			$browLog = GameProcess::getBrowseLog(array(),$filter);
			$i=0;
			foreach($browLog as $v){
				if($v['gid'] != $PackageDetails['gid']){
					$i++;
				}
			}
			if($i==count($browLog)){
				echo json_encode(array('ERRORPACKAGE'=>1,'data'=>'您还没有玩过这款游戏哦！'));
				exit();
			}
			GameProcess::addUserAccessLog($userInfo['sPhone'],GeneralFunc::deviceMess());
		}
		$filter = sprintf(" `iUserID`=%d and `pid`=%d",$iUserID,$pid);
		if($iUserID != 0){
			//判断库存
			if($PackageDetails['num_count']>0){
				//判断该礼包是每日领取一次还是限领一次
				if($PackageDetails['getCount'] == 0){
					//限领一次
					//获取我的礼包
					$mypackage = GameProcess::getMypackage(array(),$filter);
					if($mypackage){
						echo json_encode(array('ERRORPACKAGE'=>4,'data'=>array_merge($mypackage[0],$PackageDetails)));
						exit();
					}else{
						if(GameProcess::upMypackage($pid,$iUserID)){
							//更新库存
							$kucun = sprintf(" `num_people`=num_people+1 , `num_count`=num_count-1");
							if(GameProcess::upPackageCount($pid,$kucun)){
								$mypackage = GameProcess::getMypackage(array(),$filter);
								echo json_encode(array('ERRORPACKAGE'=>0,'data'=>array_merge($mypackage[0],$PackageDetails)));
								exit();
							}else{
								Yii::app()->smarty->assign('ERRORPACKAGE',1);
							}
						}else{
							Yii::app()->smarty->assign('ERRORPACKAGE',1);
						}
					}
				}else{
					//每日一次
					$mypackage = GameProcess::getMypackage(array(),$filter);
					if($mypackage){
						if(date('Y-m-d',strtotime($mypackage[0]['gettime'])) == date('Y-m-d',time())){
							//今日已领取
							echo json_encode(array('ERRORPACKAGE'=>3,'data'=>array_merge($mypackage[0],$PackageDetails)));
							exit();
						}else{
							//今日未领取
							if(GameProcess::upMypackage($pid,$iUserID)){
								//更新库存
								$kucun = sprintf(" `num_people`=num_people+1 , `num_count`=num_count-1");
								if(GameProcess::upPackageCount($pid,$kucun)){
									echo json_encode(array('ERRORPACKAGE'=>0,'data'=>array_merge($mypackage[0],$PackageDetails)));
									exit();
								}else{
									echo json_encode(array('ERRORPACKAGE'=>1,'data'=>'领取失败！'));
									exit();
								}
							}else{
								echo json_encode(array('ERRORPACKAGE'=>1,'data'=>'领取失败！'));
								exit();
							}
						}
					}else{
						//从未领取过
						if(GameProcess::upMypackage($pid,$iUserID)){
							//更新库存
							$kucun = sprintf(" `num_people`=num_people+1 , `num_count`=num_count-1");
							if(GameProcess::upPackageCount($pid,$kucun)){
								$mypackage = GameProcess::getMypackage(array(),$filter);
								echo json_encode(array('ERRORPACKAGE'=>0,'data'=>array_merge($mypackage[0],$PackageDetails)));
								exit();
							}else{
								echo json_encode(array('ERRORPACKAGE'=>1,'data'=>'领取失败！'));
								exit();
							}
						}else{
							echo json_encode(array('ERRORPACKAGE'=>1,'data'=>'领取失败！'));
							exit();
						}
					}
				}
			}else{
				//库存不足
				echo json_encode(array('ERRORPACKAGE'=>2,'data'=>'库存不足！'));
				exit();
			}
		}else{
			echo json_encode(array('ERRORPACKAGE'=>5,'data'=>'/usercenter/login.html?go='.Yii::app()->params['baseUrl'].'/project/index.php?r=partner/Game/Gamepackage&iUserSourceID=78&from='.$iCurCid));
			exit();
		}
	}

	//我的游戏
	public function actionMyGame()
	{
		//用户信息
		$uSessInfo = UserProcess::getLoginSessionInfo();
		//如果是微信用户，则要获取Openid
		if(Weixin::isWxVisitor() and empty($uSessInfo['openid']))
		{
			$retuUrl = Yii::app()->params['baseUrl'].'project/index.php?r=partner/Game';
			UserProcess::getOpenidOnOAuth($retuUrl);
			exit(0);
		}
		$iUserID = 0;
		$userInfo = UserProcess::getLoginSessionInfo();
		if(!empty($userInfo['iUserID'])){
			$iUserID = $userInfo['iUserID'];
			GameProcess::addUserAccessLog($userInfo['sPhone'],GeneralFunc::deviceMess());
		}
		$filter = sprintf(" `iUserID`=%d ",$iUserID);
		$iCurCid = 2;
		$pageInfo = UserProcess::getPageSessionInfo();
		if($pageInfo){
			$iCurCid = $pageInfo['iCurCid'];
		}
		if($iUserID != 0){
			//存页面的配置id   iCurCid
			UserProcess::setPageSessionInfo(12);
			//获取常玩游戏
			$browLog = GameProcess::getBrowseLog(array(),$filter);
		}else{
			GeneralFunc::gotoUrl('/usercenter/login.html?go='.Yii::app()->params['baseUrl'].'/project/index.php?r=partner/Game/MyGame&iUserSourceID=78&from='.$iCurCid);
			exit();
		}
		$GameDetails = array();
		if($browLog){
			foreach($browLog as $v){
				//游戏列表
				$selFields = array('gid', 'gname', 'iphoneApk','androidApk','describe','introduce','img','cate','virtual_people','num_people','is_install');
				$gid = sprintf(" %d and is_del=0 AND `status`=1",$v['gid']);
				$GameInfo = GameProcess::getGameDetails($gid,$selFields);
				if($GameInfo){
					if(mb_strlen($GameInfo['describe'],'utf-8')>17){
						$GameInfo['describe'] = mb_substr($GameInfo['describe'],0,17,'utf-8').'...';
					}
					if($GameInfo['is_install'] == 1){
						$GameInfo['install'] = '安装';
						$GameInfo['num_people'] = GeneralFunc::peopleFormat($GameInfo['num_people']+$GameInfo['virtual_people'])."下载";
						if(GeneralFunc::isIphone()){
							if($GameInfo['iphoneApk']==""){
								continue;
							}
						}else{
							if($GameInfo['androidApk']==""){
								continue;
							}
						}
					}else{
						$GameInfo['install'] = '打开';
						$GameInfo['num_people'] = GeneralFunc::peopleFormat($GameInfo['num_people']+$GameInfo['virtual_people'])."人在玩";
					}
					$GameDetails[] = $GameInfo;
				}
			}
		}

		Yii::app()->smarty->assign('GAMEDETAILS',$GameDetails);
		Yii::app()->smarty->display('movieGame/my.html');
	}

	//我的礼包
	public function actionMyPackage()
	{
		//用户信息
		$uSessInfo = UserProcess::getLoginSessionInfo();
		//如果是微信用户，则要获取Openid
		if(Weixin::isWxVisitor() and empty($uSessInfo['openid']))
		{
			$retuUrl = Yii::app()->params['baseUrl'].'project/index.php?r=partner/Game';
			UserProcess::getOpenidOnOAuth($retuUrl);
			exit(0);
		}
		$iUserID = 0;
		$userInfo = UserProcess::getLoginSessionInfo();
		if(!empty($userInfo['iUserID'])){
			$iUserID = $userInfo['iUserID'];
			GameProcess::addUserAccessLog($userInfo['sPhone'],GeneralFunc::deviceMess());
		}
		$filter = sprintf(" `iUserID`=%d ",$iUserID);
		$iCurCid = 2;
		$pageInfo = UserProcess::getPageSessionInfo();
		if($pageInfo){
			$iCurCid = $pageInfo['iCurCid'];
		}
		if($iUserID != 0){
			//存页面的配置id   iCurCid
			UserProcess::setPageSessionInfo(13);
			//获取我的礼包
			$mypackage = GameProcess::getMypackage(array(),$filter);
		}else{
			GeneralFunc::gotoUrl('/usercenter/login.html?go='.Yii::app()->params['baseUrl'].'/project/index.php?r=partner/Game/MyPackage&iUserSourceID=78&from='.$iCurCid);
			exit();
		}

		$PackageList = array();
		//获取我的礼包
		if($mypackage){

			foreach($mypackage as $v){
				//礼包详情
				$PackageInfo = GameProcess::getGamePackageBypid($v['pid'],array());
				$gameList = GameProcess::getGameDetails($PackageInfo['gid'],array('img','iphoneApk','androidApk'));
				if(GeneralFunc::isIphone()){
					if($gameList['iphoneApk']==""){
						continue;
					}
				}else{
					if($gameList['androidApk']==""){
						continue;
					}
				}
				$PackageInfo['passCode'] = $v['passCode'];
				$PackageInfo['endtime'] = $v['endtime'];
				$PackageInfo['pic'] = $gameList['img'];
				$PackageList[] = $PackageInfo;
			}
		}

		Yii::app()->smarty->assign('PACKAGEINFO',$PackageList);
		Yii::app()->smarty->display('movieGame/myPackage.html');
	}

	//消息推送
	public function actionMessage()
	{
		//用户信息
		$uSessInfo = UserProcess::getLoginSessionInfo();
		//如果是微信用户，则要获取Openid
		if(Weixin::isWxVisitor() and empty($uSessInfo['openid']))
		{
			$retuUrl = Yii::app()->params['baseUrl'].'project/index.php?r=partner/Game';
			UserProcess::getOpenidOnOAuth($retuUrl);
			exit(0);
		}
		$iUserID = 0;
		$userInfo = UserProcess::getLoginSessionInfo();
		if(!empty($userInfo['iUserID'])){
			$iUserID = $userInfo['iUserID'];
			GameProcess::addUserAccessLog($userInfo['sPhone'],GeneralFunc::deviceMess());
		}
		$filter = sprintf(" `iUserID`=%d ",$iUserID);
		$iCurCid = 2;
		$pageInfo = UserProcess::getPageSessionInfo();
		if($pageInfo){
			$iCurCid = $pageInfo['iCurCid'];
		}
		if($iUserID != 0){
			//存页面的配置id   iCurCid
			UserProcess::setPageSessionInfo(14);
			//消息推送
			$MessageInfo = GameProcess::getMessageInfo($iUserID);
			foreach($MessageInfo as &$v){
				$v['info'] = mb_substr($v['info'],0,20,'utf-8');
			}
		}else{
			GeneralFunc::gotoUrl('/usercenter/login.html?go='.Yii::app()->params['baseUrl'].'/project/index.php?r=partner/Game/Message&iUserSourceID=78&from='.$iCurCid);
			exit();
		}
		Yii::app()->smarty->assign('MESSAGEINFO',$MessageInfo);
		Yii::app()->smarty->display('movieGame/message.html');
	}

	//h5显示我的礼包
	public function actionUserPackage()
	{
		$iUserID = 0;
		$userInfo = UserProcess::getLoginSessionInfo();
		$iUserID = $userInfo['iUserID'];
		$filter = sprintf(" `iUserID`=%d ",$iUserID);
		$mypackage = GameProcess::getMypackage(array(),$filter);
		$PackageList = array();
		//获取我的礼包
		if($mypackage){
			foreach($mypackage as $v){
				//礼包详情
				$PackageInfo = GameProcess::getGamePackageBypid($v['pid'],array());
				$gameList = GameProcess::getGameDetails($PackageInfo['gid'],array('img','iphoneApk','androidApk'));
				if(GeneralFunc::isIphone()){
					if($gameList['iphoneApk']==""){
						continue;
					}
				}else{
					if($gameList['androidApk']==""){
						continue;
					}
				}
				$PackageInfo['passCode'] = $v['passCode'];
				$PackageInfo['endtime'] = $v['endtime'];
				$PackageInfo['pic'] = $gameList['img'];
				$PackageList[] = $PackageInfo;
			}
		}
		if(!empty($PackageList)){
			echo json_encode(array("flag"=>true,"err"=>$PackageList));exit();
		}else{
			echo json_encode(array("flag"=>false,"err"=>""));exit();
		}
	}
}