<?php

/**
 * AjaxController - 用于Ajax操作
 * @author luzhizhong
 * @version V1.0
 */

Yii::import('application.models.data.db.*');
Yii::import('application.modules.partner.models.data.*');
Yii::import('application.modules.partner.models.process.*');

class AjaxController extends Controller
{
    /**
     * Declares class-based actions.
     */
    public function actions()
    {
        return array(
        );
    }

    /**
     * 创建前台返回Arr
     * @param int $gid 商品id
     * @param int $errNo 错误码
     * @return array[] 返回数组
     */
    private function getReturnArr($gid, $errNo)
    {
        return GoodsProcess::getExchangeErrInfo($gid, $errNo);
    }
    /**
     * 验证商品是否可兑换（前台显示字数<=9）
     */
    public function actionIsExchange()
    {
        $gid = $_REQUEST["gid"];
        $uid = $_REQUEST["uid"];

        $ret = GoodsProcess::isExchange($uid, $gid);
        $retArr = $this->getReturnArr($gid, $ret);

        echo json_encode($retArr);
    }

    /**
     * 兑换商品
     */
    public function actionExchangeGoods()
    {
        $uid = $_REQUEST["uid"];
        $gid = $_REQUEST["gid"];

        //兑换商品
        $ret = GoodsProcess::exchangeGoods($uid, $gid);
        $retArr = $this->getReturnArr($gid, $ret);

        echo json_encode($retArr);
    }
    /**
     * 获取邀请者uid（by openid）
     * 获取标准：只给最后一个接受邀请的好友加积分，且只加一次
     */
    public function actionGetInviteUsers()
    {
        $openid = $_REQUEST["openid"];
        $uid = UserProcess::getInviteUsersByOpenid($openid);
        echo $uid;
    }
    /**
     * 增加积分操作（仅针对微信关注，已停用）
     */
    public function actionAddScore_Focus()
    {
        $uid = $_REQUEST["uid"];
        $openid = $_REQUEST["openid"];
        $key = $_REQUEST["key"];

        if(empty($uid) or empty($openid) or empty($key))
        {
            echo "-1";
            exit(0);
        }
        if($key!=md5($uid.GeneralFunc::getCurDate().$openid))
        {
            echo "-2";
            exit(0);
        }

        $ret = ScoreProcess::addScore($uid, 11, 1, '', $openid);
        echo $ret;
    }

    /**
     * 增加积分操作（仅针对微信关注）
     * 1、如果是注册用户且第一次关注，给新用户送E豆
     * 2、如果是E豆商城的邀请好友关注，给邀请者送E豆
     */
    public function actionAddScore_Subscribe()
    {
        $openid = $_REQUEST["openid"];
        $key = $_REQUEST["key"];

        if(empty($openid) or empty($key))
        {
            echo "-1";
            exit(0);
        }
        if($key!=md5(GeneralFunc::getCurDate().$openid))
        {
            echo "-2";
            exit(0);
        }

        //第一步，给新用户送E豆
        $uInfo = UserProcess::getUInfoByOpenid($openid, array('iUserID', 'sPhone'));
        if(!empty($uInfo))
        {
            ScoreProcess::addScore($uInfo['iUserID'], 13, 1);
        }

        //第二步，给邀请者送E豆
        $inviterUid = UserProcess::getInviterUidByInvitee($uInfo['iUserID']);
        ScoreProcess::addScore($inviterUid, 11, 1, '', $uInfo['iUserID']);

        echo 1;
    }

    /**
     * 签到操作
     */
    public function actionSignIn()
    {
        $uid = $_REQUEST["uid"];

        //签到
        $ret = UserProcess::signIn($uid);

        $retArr = array();
        switch($ret)
        {
            case '-1':			//参数错误
                $retArr = array('ok'=>FALSE, 'errmsg'=>'参数错误');
                break;
            case '-201':		//用户不存在
                $retArr = array('ok'=>FALSE, 'errmsg'=>'用户不存在');
                break;
            default:
                $retArr = array('ok'=>TRUE, 'errmsg'=>'');
                break;
        }

        echo json_encode($retArr);
    }

    /**
     * 获取城市、区县列表
     *
     * @param int $pid 父id
     * @param string $area 城市：city；区县：region
     */
    public function actionGetAreaList()
    {
        $pid = $_REQUEST["pid"];
        $area = in_array($_REQUEST["area"], array('city', 'region')) ? $_REQUEST["area"] : 'city';

        $areaList = array();
        if(empty($pid) or !is_numeric($pid))
        {
            return $areaList;
        }

        if($area=='city')
        {
            $areaList = CityProcess::getCityList($pid);
        }else{
            $areaList = CityProcess::getRegionList($pid);

            //如果是区县，要加入‘其他区县’选项
            $areaList[] = array('iRegionID'=>0, 'sRegionName'=>'其他区县');
        }

        //echo json_encode(array('ok'=>TRUE, 'list'=>$areaList));
        echo json_encode($areaList);
        //echo json_encode(array('ok'=>TRUE, 'errmsg'=>''));
    }
}