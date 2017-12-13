<?php
/**
 * 后台管理员操作类
 * User: wang
 * Date: 2017/12/6
 * Time: 17:33
 */
Yii::import('application.models.process.lib.*');
Yii::import('application.modules.market.models.data.db.*');
Yii::import('application.modules.market.models.process.*');
class AdminController extends Controller
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

    public function actionAdminaccount()
    {
        $tableName = 'admin_user';
        $userInfo = AdminUserProcess::getAdminUser();
        $userCount = AdminUserProcess::getCount($tableName);
        Yii::app()->smarty->assign('USER',$userInfo);
        Yii::app()->smarty->assign('COUNT',$userCount[0]['num']);
        Yii::app()->smarty->display('market/adminaccount.html');
    }

    /**
     * 修改管理员的状态
     * @return bool
     */
    public function actionSavestatus()
    {
        $adminID = Yii::app()->request->getPost('adminID');
        $adminStatus = Yii::app()->request->getPost('adminStatus');
        if(AdminUserProcess::SaveStatus($adminID,$adminStatus)){
            return true;
        }else{
            return false;
        }
    }

    public function actionAdminadd()
    {
        if(Yii::app()->request->isPostRequest){
            $email = Yii::app()->request->getPost('email');
            $password = md5(Yii::app()->request->getPost('password').'epiaowang.com');
            $realname = Yii::app()->request->getPost('realname');
            $role_id = Yii::app()->request->getPost('role');
            $result = AdminUserProcess::adminUserAdd($email,$password,$realname);
            if($result == false){
                GeneralFunc::alert('此邮箱已存在');
            }else{
                $role_result = AdminUserProcess::adminRoleAdd($result,$role_id);
                echo 'ok';
            }
        }else{
            $adminRole = AdminUserProcess::getAdminRole();
            Yii::app()->smarty->assign('ROLE',$adminRole);
            Yii::app()->smarty->display('market/adminadd.html');
        }
    }

    public function actionAdminedit()
    {
        if(Yii::app()->request->isPostRequest){
            $email = Yii::app()->request->getPost('email');
            $password = md5(Yii::app()->request->getPost('password').'epiaowang.com');
            $realname = Yii::app()->request->getPost('realname');
            $role_id = Yii::app()->request->getPost('role');
            $saveid = Yii::app()->request->getPost('saveid');
            $result = AdminUserProcess::saveAdminInfo($email,$password,$realname,$role_id,$saveid);
            if($result){
                echo 'ok';
            }
        }else{
            $adminID = Yii::app()->request->getParam('admin_user_id');
            $adminInfo = AdminUserProcess::getAdminInfo($adminID);
            $adminRole = AdminUserProcess::getAdminRole();
            $selected = 'selected';
            Yii::app()->smarty->assign('SELECTED',$selected);
            Yii::app()->smarty->assign('INFO',$adminInfo);
            Yii::app()->smarty->assign('ROLE',$adminRole);
            Yii::app()->smarty->display('market/Adminedit.html');
        }
    }

    public function actionSearch()
    {

    }


    public function actionRolelist()
    {
        $tableName = 'role';
        $roleInfo = AdminUserProcess::getRole();
        $roleCount = AdminUserProcess::getCount($tableName);
        Yii::app()->smarty->assign('ROLE',$roleInfo);
        Yii::app()->smarty->assign('COUNT',$roleCount[0]['num']);
        Yii::app()->smarty->display('market/role.html');
    }

    public function actionSaverolestatus()
    {
        $roleID = Yii::app()->request->getPost('roleID');
        $roleStatus = Yii::app()->request->getPost('roleStatus');
        AdminUserProcess::saveRoleStatus($roleID,$roleStatus);
    }

    public function actionRoleadd()
    {
        $menuInfo = AdminUserProcess::getMenu();
        $tree = array();
        foreach($menuInfo as $v){
            $tree[$v['menu_id']] = $v;
            $tree[$v['menu_id']]['children'] = array();
        }
        foreach($tree as $key=>$item){
            if($item['parent_id'] != 0){
                $tree[$item['parent_id']]['children'][] = &$tree[$key];//注意：此处必须传引用否则结果不对
                if($tree[$key]['children'] == null){
                    unset($tree[$key]['children']); //如果children为空，则删除该children元素（可选）
                }
            }
        }
        foreach($tree as $key=>$category){
            if($category['parent_id'] != 0){
                unset($tree[$key]);
            }
        }
        Yii::app()->smarty->assign('MENU',$tree);
        Yii::app()->smarty->display('market/role-add.html');
    }
}