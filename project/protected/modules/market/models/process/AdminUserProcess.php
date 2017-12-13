<?php
/**
 * AdminUserProcess 后台用户登录操作类
 * User: wang
 * Date: 2017/12/5
 * Time: 14:14
 */

class AdminUserProcess
{
    function __construct()
    {
    }
    function __destruct()
    {
    }
    private function filterInputFields($inputFields,$defFields)
    {
        return array_intersect_key($inputFields,$defFields);
    }

    /**
     * 获取用户登录的session信息
     * @return bool
     */
    public static function getSessionInfo()
    {
        $sessionInfo['adminID'] = Yii::app()->session->get('adminID');
        $sessionInfo['adminUserName'] = Yii::app()->session->get('adminUserName');
        if($sessionInfo['adminID'] == NULL){
            return false;
        }else{
            return $sessionInfo;
        }
    }

    public static function clearSession()
    {
        $sess = Yii::app()->session->destroy();
        return $sess;
    }

    /**
     * 用户登录
     * @param $username
     * @param $password
     * @return array
     */
    public static function getUserLogin($email,$password)
    {
        $result = [];
        $sql = "SELECT admin_user_id,admin_user_email,admin_user_password,admin_status,admin_realname FROM {{admin_user}} WHERE admin_user_email = '$email'";
        $user_exists = DbUtil::queryRow($sql);
        if($user_exists){
            if($password == $user_exists['admin_user_password']){
                if($user_exists['admin_status'] == 1){
                    Yii::app()->session->add('adminID',$user_exists['admin_user_id']);
                    Yii::app()->session->add('adminUserName',$user_exists['admin_realname']);
                    $result['data'] = $user_exists;
                }else{
                    $result['msg'] = '您的账号已禁用，请联系管理员';
                }
            }else{
                $result['msg'] = '密码输入不正确';
            }
        }else{
            $result['msg'] = '账号不存在';
        }
        return $result;
    }

    public static function getAdminUser()
    {
        $sql = "SELECT a.admin_user_id,a.admin_user_email,a.admin_realname,a.admin_status,a.admin_createtime,o.role_name FROM {{admin_user}} AS a INNER JOIN {{admin_role}} AS r INNER JOIN {{role}} AS o ON a.admin_user_id=r.admin_user_id AND r.role_id=o.role_id";
        $adminUser = DbUtil::queryAll($sql);
        return $adminUser;
    }

    public static function getCount($tableName)
    {
        $sql = "SELECT count(*) AS num FROM {{{$tableName}}}";
        return DbUtil::queryAll($sql);
    }

    public static function SaveStatus($adminID,$adminStatus)
    {
        if($adminStatus == 1){
            $sql = "UPDATE {{admin_user}} SET admin_status = 0 WHERE admin_user_id = '$adminID'";
        }else{
            $sql = "UPDATE {{admin_user}} SET admin_status = 1 WHERE admin_user_id = '$adminID'";
        }
        return DbUtil::execute($sql);
    }

    /**
     * 获取所有的角色
     * @return array
     */
    public static function getAdminRole()
    {
        $sql = "SELECT role_id,role_name FROM {{role}} WHERE role_status = 1";
        $adminRole = DbUtil::queryAll($sql);
        return $adminRole;
    }

    public static function adminUserAdd($email,$password,$realname)
    {
        $sql = "SELECT admin_user_email FROM {{admin_user}} WHERE admin_user_email = '$email'";
        $exists = DbUtil::queryRow($sql);
        if($exists){
            return false;
        }else{
            $sql = "INSERT INTO {{admin_user}}(admin_user_email,admin_user_password,admin_realname,admin_createtime) VALUES('$email','$password','$realname',NOW())";
            DbUtil::execute($sql);
            return DbUtil::lastInsertID();
        }
    }

    public static function adminRoleAdd($user_id,$role_id)
    {
        $sql = "INSERT INTO {{admin_role}}(admin_user_id,role_id) VALUES('$user_id','$role_id')";
        return DbUtil::execute($sql);
    }

    public static function getAdminInfo($adminUserID)
    {
        $sql = "SELECT a.admin_user_id,a.admin_user_email,a.admin_realname,a.admin_status,a.admin_createtime,o.role_id,o.role_name FROM {{admin_user}} AS a INNER JOIN {{admin_role}} AS r INNER JOIN {{role}} AS o ON a.admin_user_id=r.admin_user_id AND r.role_id=o.role_id WHERE a.admin_user_id = '$adminUserID'";
        $adminInfo = DbUtil::queryRow($sql);
        return $adminInfo;
    }

    public static function saveAdminInfo($email,$password,$realname,$role_id,$saveid)
    {
        $sql = "UPDATE {{admin_user}} SET admin_user_email='$email',admin_user_password='$password',admin_realname='$realname' WHERE admin_user_id = '$saveid'";
        if(DbUtil::execute($sql)){
            $sql = "UPDATE {{admin_role}} SET role_id='$role_id',admin_user_id='$saveid' WHERE admin_user_id = '$saveid'";
            return DbUtil::execute($sql);
        }else{
            return false;
        }
    }

    public static function searchAdminUser()
    {

    }

    public static function getRole()
    {
        $sql = "SELECT * FROM {{role}}";
        $roleInfo = DbUtil::queryAll($sql);
        return $roleInfo;
    }

    public static function saveRoleStatus($roleID,$roleStatus)
    {
        if($roleStatus == 1){
            $sql = "UPDATE {{role}} SET role_status = 0 WHERE role_id = '$roleID'";
        }else{
            $sql = "UPDATE {{role}} SET role_status = 1 WHERE role_id = '$roleID'";
        }
        return DbUtil::execute($sql);
    }

    public static function getMenu()
    {
        $sql = "SELECT * FROM {{menu}}";
        $menuInfo = DbUtil::queryAll($sql);
        return $menuInfo;
    }
}