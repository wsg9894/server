<?php
/**
 * IndexController 模板嵌套控制器
 */
class IndexController extends Controller
{
	public function actions()
	{
		return array(
		);
	}
	//首页
	public function actionIndex()
	{
		Yii::app()->smarty->display('market/index.html');
	}
	//后台登陆
	public function actionLogin()
	{
		Yii::app()->smarty->display('market/login.html');
	}
	//主体部分
	public function actionWelcome()
	{
		Yii::app()->smarty->display('market/welcome.html');
	}
	//会员列表
	public function actionMemberlist()
	{
		Yii::app()->smarty->display('market/member-list.html');
	}
	//会员添加
	public function actionMemberadd()
	{
		Yii::app()->smarty->display('market/member-add.html');
	}
	//会员编辑
	public function actionMemberedit()
	{
		Yii::app()->smarty->display('market/member-edit.html');
	}
	//修改密码
	public function actionUpdatepassword()
	{
		Yii::app()->smarty->display('market/member-password.html');
	}
	//会员删除
	public function actionMemberdel()
	{
		Yii::app()->smarty->display('market/member-del.html');
	}
	//订单列表
	public function actionOrderlist()
	{
		Yii::app()->smarty->display('market/order-list.html');
	}
	//订单添加
	public function actionOrderadd()
	{
		Yii::app()->smarty->display('market/order-add.html');
	}
	//管理员列表
	public function actionAdminlist()
	{
		Yii::app()->smarty->display('market/admin-list.html');
	}
	//管理员添加
	public function actionAdminadd()
	{
		Yii::app()->smarty->display('market/admin-add.html');
	}
	//管理员编辑
	public function actionAdminedit()
	{
		Yii::app()->smarty->display('market/admin-edit.html');
	}
	//角色管理
	public function actionAdminrole()
	{
		Yii::app()->smarty->display('market/admin-role.html');
	}
	//权限分类
	public function actionAdmincate()
	{
		Yii::app()->smarty->display('market/admin-cate.html');
	}
	//权限管理
	public function actionAdminrule()
	{
		Yii::app()->smarty->display('market/admin-role.html');
	}
	//折线图
	public function actionBrokenline()
	{
		Yii::app()->smarty->display('market/broken-line.html');
	}
	//柱状图
	public function actionColumn()
	{
		Yii::app()->smarty->display('market/column.html');
	}
	//地图
	public function actionMap()
	{
		Yii::app()->smarty->display('market/map.html');
	}
	//饼图
	public function actionPiechart()
	{
		Yii::app()->smarty->display('market/pie-chart.html');
	}
	//雷达图
	public function actionRadar()
	{
		Yii::app()->smarty->display('market/radar.html');
	}
	//K线图
	public function actionKline()
	{
		Yii::app()->smarty->display('market/k-line.html');
	}
	//热力图
	public function actionHeat()
	{
		Yii::app()->smarty->display('market/heat.html');
	}
	//仪表图
	public function actionMeter()
	{
		Yii::app()->smarty->display('market/meter.html');
	}
}