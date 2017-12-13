<?php
class UseroauthProcess{
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
     * 获取热门游戏列表
     * @param array[] $fields 所要字段列表，一维数组，如果为空则获取全部
     * @param string $filter where添加
     * @return array[][] 热门游戏列表，二维数组
     */
    public static function getUserOauth($fields=array(), $filter=''){
        $gameDBObj = new G_UoauthDB();
        $fields = array_intersect($fields,$gameDBObj->attributeNames());
        if(is_array($fields) and !empty($fields))
        {
            $selStr = '`'.implode('`,`', $fields).'`';
        }else{
            $selStr = '*';
        }
        $whereStr = empty($filter) ? 'WHERE 1' : 'WHERE 1 AND '.$filter;
        $sql = 'SELECT '.$selStr.' FROM {{g_useroauth}} '.$whereStr.'';
        $bannerList = DbUtil::queryAll($sql);
        return $bannerList;
    }


    /**
     * 修改用户oauth
     * @param $userToken
     * @param $iUserID
     * @return mixed
     */
    public static function updateUserOauth($editInfo){
        foreach($editInfo as $key =>$v)
        {
            $subSql[] = "$key='".mysql_escape_string($v)."'";
        }

        $sql = sprintf("update {{g_useroauth}} set %s where iUserID=%d ", implode(',',$subSql),$editInfo['iUserID']);
        return DbUtil::execute($sql);
    }

    /**
     * 添加oauth
     * @param $userToken
     * @param $iUserID
     * @return mixed
     */
    public static function addUserOauth($editInfo){
        foreach($editInfo as $key =>$v)
        {
            $subSql[] = "$key='".mysql_escape_string($v)."'";
        }
        $sql = sprintf("insert into {{g_useroauth}} set %s",implode(',',$subSql));
        return DbUtil::execute($sql);
    }
}