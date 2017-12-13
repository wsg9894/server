<?php
/**
 * Created by PhpStorm.
 * User: wang
 * Date: 2017/11/30
 * Time: 10:51
 */

class UserProcess
{
    function __construct()
    {

    }
    function __destruct()
    {

    }
    /**
     * 有效字段过滤（过滤有效的字段列表）
     *
     * @param array[] $inputFields	输入的字段列表
     * @param array[] $defFields 数据库字段列表
     * @return array[] 过滤后的字段列表
     */
    private function filterInputFields($inputFields,$defFields)
    {
        return array_intersect_key($inputFields,$defFields);
    }
    public static function getUserList()
    {
        $user = new UserDB();
        $sql = "SELECT * FROM {{e_adminuser}}";
        $userList = DbUtil::queryAll($sql);
        return $userList;
    }
    public static function getSession()
    {
        $sess = new Session();
        $username = $sess->get('username');
        if(empty($username)){
            return false;
        }else{
            return $username;
        }
    }
    public static function getUserLogin($username,$password)
    {
        $user = new UserDB();
        $sql = "SELECT * FROM {{e_adminuser}} WHERE sAdminUserID = '$username'";
        $user_exists = DbUtil::queryRow($sql);
        if($user_exists){
            if($password == $user_exists['sPassword']){
                $sess = new Session();
                $sess->add('username',$user_exists['sAdminUserName']);
                return $user_exists;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
}