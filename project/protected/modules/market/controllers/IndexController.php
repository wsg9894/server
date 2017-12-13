<?php
/**
 * IndexController 模板嵌套控制器
 */
Yii::import('application.models.process.lib.*');
Yii::import('application.modules.market.models.data.db.*');
Yii::import('application.modules.market.models.process.*');
class IndexController extends Controller
{
    public function __construct()
    {
        $sessionInfo = AdminUserProcess::getSessionInfo();
        if($sessionInfo == false){
            GeneralFunc::alert('您还没有登录，请登录');
            GeneralFunc::gotoUrl('index.php?r=market/Login/Login');
        }
    }
	public function actions()
	{
		return array(
		);
	}
	//首页
	public function actionIndex()
	{
	    $sessiInfo = AdminUserProcess::getSessionInfo();
	    Yii::app()->smarty->assign('SESS',$sessiInfo);
		Yii::app()->smarty->display('market/index.html');
	}
	//主体部分
	public function actionWelcome()
	{
		Yii::app()->smarty->display('market/welcome.html');
	}

	//用户资料管理
	public function actionAdmindata()
	{
		Yii::app()->smarty->display('market/admindata.html');
	}
	//影院资料
	public function actionCinemalist()
	{
		Yii::app()->smarty->display('market/cinema-list.html');
	}
	//影院添加
	public function actionCinemadd()
	{
		Yii::app()->smarty->display('market/cinema-add.html');
	}
	//影院匹配
	public function actionCinemamatch()
	{
		Yii::app()->smarty->display('market/cinema-match.html');
	}
	//排期信息
	public function actionSchedule()
	{
		Yii::app()->smarty->display('market/schedule.html');
	}
	//立减
	public function actionMinus()
	{
		Yii::app()->smarty->display('market/minus.html');
	}
	//添加立减活动
	public function actionMinusadd()
	{
		Yii::app()->smarty->display('market/minus-add.html');
	}
	//减至
	public function actionBereduced()
	{
		Yii::app()->smarty->display('market/bereduced.html');
	}

	//渠道管理
	public function actionChannel()
	{
		Yii::app()->smarty->display('market/channel.html');
	}

}