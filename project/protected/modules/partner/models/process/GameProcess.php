<?php

/**
 * GameProcess - game操作类
 * @author luzhizhong
 * @version V1.0
 */


class GameProcess
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

    /**
     * 获取热门游戏列表
     * @param array[] $fields 所要字段列表，一维数组，如果为空则获取全部
     * @param string $filter where添加
     * @return array[][] 热门游戏列表，二维数组
     */
    public static function getHotGameList($fields=array(), $filter=''){
        $gameDBObj = new G_GameDB();
        $fields = array_intersect($fields,$gameDBObj->attributeNames());
        if(is_array($fields) and !empty($fields))
        {
            $selStr = '`'.implode('`,`', $fields).'`';
        }else{
            $selStr = '*';
        }
        $whereStr = empty($filter) ? 'WHERE 1' : 'WHERE 1 AND '.$filter;
        $sql = 'SELECT '.$selStr.' FROM {{g_game}} '.$whereStr.'';
//		echo $sql;die;
        $bannerList = DbUtil::queryAll($sql);
        return $bannerList;
    }

    //获取包含游戏礼包的游戏-按游戏礼包倒序排列
    public static function getGameListByPackage(){

        $sql = 'SELECT a.* FROM {{g_game}} as a,{{g_package}} as b where a.is_del=0 AND a.`status`=1 and `num_package`>0 and a.gid=b.gid and b.is_del=0 AND b.`status`=1 and endtime>=NOW() group by gid order by max(weight) desc';
		//echo $sql;
        return DbUtil::queryAll($sql);
    }

    public static function getGameDetails($gid,$fields=array()){
        $GameInfo = array();
        if(empty($gid))
        {
            return $GameInfo;
        }
        $goodsDBObj = new G_GameDB();

        //过滤掉无效字段
        $fields = array_intersect($fields, $goodsDBObj->attributeNames());
        if(is_array($fields) and !empty($fields))
        {
            $selStr = '`'.implode('`,`', $fields).'`';
            $sql = 'SELECT '.$selStr.' FROM {{g_game}} WHERE gid='.$gid;
        }else{
            $sql = 'SELECT * FROM {{g_game}} WHERE gid='.$gid;
        }
        return DbUtil::queryRow($sql);
    }

    public static function getGameImgListBygid($gid,$fields=array()){
        $GameInfo = array();
        if(empty($gid))
        {
            return $GameInfo;
        }
        $goodsDBObj = new G_GameDB();

        //过滤掉无效字段
        $fields = array_intersect($fields, $goodsDBObj->attributeNames());
        if(is_array($fields) and !empty($fields))
        {
            $selStr = '`'.implode('`,`', $fields).'`';
            $sql = 'SELECT '.$selStr.' FROM {{g_gameimg}} WHERE gid='.$gid;
        }else{
            $sql = 'SELECT * FROM {{g_gameimg}} WHERE gid='.$gid;
        }
        return DbUtil::queryAll($sql);
    }

    public static function getGamePackageList($fields=array(), $filter=''){
        $gameDBObj = new G_PackageDB();
        $fields = array_intersect($fields,$gameDBObj->attributeNames());
        if(is_array($fields) and !empty($fields))
        {
            $selStr = '`'.implode('`,`', $fields).'`';
        }else{
            $selStr = '*';
        }
        $whereStr = empty($filter) ? 'WHERE 1' : 'WHERE 1 AND '.$filter;
        $sql = 'SELECT '.$selStr.' FROM {{g_package}} '.$whereStr.'';
        return DbUtil::queryAll($sql);
    }

    public static function getGamePackageCount($filter=''){
        $whereStr = empty($filter) ? 'WHERE 1' : 'WHERE 1 AND '.$filter;
        $sql = 'SELECT count(gid) as num_package,gid FROM {{g_package}} '.$whereStr.'';
        return DbUtil::queryAll($sql);
    }

    public static function getMyPackageCount($filter=''){
        $whereStr = empty($filter) ? 'WHERE 1' : 'WHERE 1 AND '.$filter;
        $sql = 'SELECT count(pid) as num_count,gid FROM {{g_myPackage}} '.$whereStr.'';
        return DbUtil::queryAll($sql);
    }

    public static function getGameImgList($fields=array(), $filter=''){
        $gameDBObj = new G_GameDB();
        $fields = array_intersect($fields,$gameDBObj->attributeNames());
        if(is_array($fields) and !empty($fields))
        {
            $selStr = '`'.implode('`,`', $fields).'`';
        }else{
            $selStr = '*';
        }
        $whereStr = empty($filter) ? 'WHERE 1' : 'WHERE 1 AND '.$filter;
        $sql = 'SELECT '.$selStr.' FROM {{g_gameimg}} '.$whereStr.'';
        return DbUtil::queryAll($sql);
    }

    public static function getGamePackageBypid($pid,$fields=array()){
        $GameInfo = array();
        if(empty($pid))
        {
            return $GameInfo;
        }
        $goodsDBObj = new G_PackageDB();

        //过滤掉无效字段
        $fields = array_intersect($fields, $goodsDBObj->attributeNames());
        if(is_array($fields) and !empty($fields))
        {
            $selStr = '`'.implode('`,`', $fields).'`';
            $sql = 'SELECT '.$selStr.' FROM {{g_package}} WHERE pid='.$pid;
        }else{
            $sql = 'SELECT * FROM {{g_package}} WHERE pid='.$pid;
        }
        return DbUtil::queryRow($sql);
    }

    //查找用户浏览记录
    public static function getBrowseLog($fields=array(), $filter=''){
        $gameDBObj = new G_UserBrowseLogDB();
        $fields = array_intersect($fields,$gameDBObj->attributeNames());
        if(is_array($fields) and !empty($fields))
        {
            $selStr = '`'.implode('`,`', $fields).'`';
        }else{
            $selStr = '*';
        }
        $whereStr = empty($filter) ? 'WHERE 1' : 'WHERE 1 AND '.$filter;
        $sql = 'SELECT '.$selStr.' FROM {{g_browseLog}} '.$whereStr.'';
        return DbUtil::queryAll($sql);
    }

    //获取我的礼包
    public static function getMypackage($fields=array(), $filter=''){
        $gameDBObj = new G_GameDB();
        $fields = array_intersect($fields,$gameDBObj->attributeNames());
        if(is_array($fields) and !empty($fields))
        {
            $selStr = '`'.implode('`,`', $fields).'`';
        }else{
            $selStr = '*';
        }
        $whereStr = empty($filter) ? 'WHERE 1' : 'WHERE 1 AND '.$filter;
        $sql = 'SELECT '.$selStr.' FROM {{g_myPackage}} '.$whereStr.'';
        return DbUtil::queryAll($sql);
    }

    //更新我的礼包
    public static function upMypackage($pid, $iUserID){

        if($pid == 0 || $iUserID==0){
            return 0;
        }
        $sql = 'update {{g_myPackage}} set iUserID='.$iUserID.',gettime=NOW() where pid = '.$pid.' and iUserID = 0 limit 1';
        return DbUtil::execute($sql);
    }

    //更新库存和领取人数
    public static function upPackageCount($pid, $filter=''){

        if($pid == 0 || empty($filter)){
            return 0;
        }
        $sql = 'update {{g_package}} set '.$filter.' where pid = '.$pid;
        return DbUtil::execute($sql);
    }

    public static function updateGameClicks($gid){
        if($gid == 0){
            return 0;
        }
        $sql = 'update {{g_game}} set num_people = num_people+1 where gid = '.$gid;
        return DbUtil::execute($sql);
    }

    public static function updateGameDown($gid){
        if($gid == 0){
            return 0;
        }
        $sql = 'update {{g_game}} set isDownList = 1 where gid = '.$gid;
        return DbUtil::execute($sql);
    }

    public static function getMessageInfo($iUserID){
        $sql = 'SELECT * FROM {{g_message}} where iUserID= '.$iUserID;
        return DbUtil::queryAll($sql);
    }

    //添加用户访问记录
    public static function addUserAccessLog($sPhone,$devicename){
        $sql = sprintf("insert into {{g_usergame}}(phone,devicename,createtime) values('%s','%s',NOW())",$sPhone,$devicename);
        return DbUtil::execute($sql);
    }


    /*`lid` int(11) NOT NULL AUTO_INCREMENT,
  `iCurCid` int(5) NOT NULL DEFAULT '0' COMMENT '当前页面配置id，外联于tb_g_pageconf.cid',
  `sCurPConfName` varchar(128) NOT NULL DEFAULT '' COMMENT '当前页面名称，外联于tb_g_pageconf.sName',
  `iFromCid` int(5) NOT NULL DEFAULT '0' COMMENT '先前页面配置id，外联于tb_g_pageconf.cid',
  `sFromPConfName` varchar(128) NOT NULL DEFAULT '' COMMENT '先前页面名称，外联于tb_g_pageconf.sName',
  `sTermOS` varchar(64) NOT NULL DEFAULT '' COMMENT '终端操作系统',
  `sTermOSVersion` varchar(64) NOT NULL DEFAULT '' COMMENT '终端操作系统版本号',
  `sTermOSLanguage` varchar(32) NOT NULL DEFAULT '' COMMENT '终端操作系统语言',
  `sTermType` varchar(32) NOT NULL DEFAULT '' COMMENT '终端型号/手机型号',
  `sTermBrowser` varchar(32) NOT NULL DEFAULT '' COMMENT '终端浏览器类型',
  `sUserIP` varchar(16) NOT NULL DEFAULT '' COMMENT '用户IP地址',
  `dCreateTime` timestamp NULL COMMENT '创建时间',*/

    //添加用户访问记录
    public static function addUserPvLog($iCurCid,$sCurPConfName,$iFromCid,$sFromPConfName,$gid,$pid,$sTermOS,$sTermOSVersion,$sTermOSLanguage,$sTermType,$sTermBrowser,$sUserIP){
        $sql = sprintf("insert into {{g_pvlog}}(iCurCid,sCurPConfName,iFromCid,sFromPConfName,gid,pid,sTermOS,sTermOSVersion,sTermOSLanguage,sTermType,sTermBrowser,sUserIP,dCreateTime) values(%d,'%s',%d,'%s',%d,%d,'%s','%s','%s','%s','%s','%s',NOW())",$iCurCid,$sCurPConfName,$iFromCid,$sFromPConfName,$gid,$pid,$sTermOS,$sTermOSVersion,$sTermOSLanguage,$sTermType,$sTermBrowser,$sUserIP);
        return DbUtil::execute($sql);
    }

    //获取个页面的配置信息
    public static function getPageInfo($cid){
        $sql = 'SELECT * FROM {{g_pageconf}} where cid= '.$cid;
        return DbUtil::queryRow($sql);
    }

    //查询各个页面不同游戏的下载量
    public static function getPageLoadByGid($gid,$cid){
        $sql = 'SELECT * FROM {{g_pageload}} where iCurCid= '.$cid.' and gid='.$gid;
        return DbUtil::queryRow($sql);
    }

    //增加各个页面不同游戏的下载量
    public static function addPageLoadByGid($gid){
        $sessObj = new Session();
        $iCurCid = $sessObj->get('iCurCid');
        if($iCurCid){
            $pageInfo = self::getPageInfo($iCurCid);
            if($pageInfo){
                $sql = sprintf("insert into {{g_pageload}}(`iCurCid`,`sCurPConfName`,`gid`,`loadNum`,`dCreateTime`) values(%d,'%s',%d,1,NOW())",$iCurCid,$pageInfo['sName'],$gid);
                return DbUtil::execute($sql);
            }
        }
        return 1;
    }

    //获取各个页面pv的输出量
    public static function getPvexport($cid,$fid,$dBeginTime,$dEndTime){
        $dBeginTime = "'$dBeginTime'";
        $dEndTime = "'$dEndTime'";
        $sql = 'SELECT sCurPConfName,sFromPConfName,count(*) as tot,gid,pid FROM {{g_pvlog}} where dCreateTime>='.$dBeginTime.' and dCreateTime<'.$dEndTime.' and iCurCid= '.$cid.' and iFromCid='.$fid.' group by gid,pid';
        return DbUtil::queryAll($sql);
    }
    //获取各个页面的配置信息
    public static function getpageConf(){
        $sql = 'SELECT * FROM {{g_pageconf}}';
        return DbUtil::queryAll($sql);
    }

    //获取各个页面的下载量
    public static function getpageLoad($dBeginTime,$dEndTime){
        $dBeginTime = "'$dBeginTime'";
        $dEndTime = "'$dEndTime'";
        $sql = 'SELECT count(*) as tot,gid,sCurPConfName FROM {{g_pageload}} where dCreateTime>='.$dBeginTime.' and dCreateTime<'.$dEndTime.' group by gid,iCurCid';
       // return $sql;
        return DbUtil::queryAll($sql);
    }
}