<?php
/**
 * Created by PhpStorm.
 * User: ylp
 * Date: 2017/8/21
 * Time: 10:08
 */
Yii::import('application.modules.output.models.process.*');
class ApiController extends Controller
{
    public function actionIndex(){
        $type = empty($_REQUEST['type']) ? '' : $_REQUEST['type'];		//接口类型
        $key = empty($_REQUEST['key']) ? '' : $_REQUEST['key'];			//接口密钥
        //密钥验证
        if(FALSE==$this->checkKey($type, $key))
        {
            $ret = GeneralFunc::returnErr(ErrorParse::getErrorNo('verify_err'), ErrorParse::getErrorDesc('verify_err'));
            GeneralFunc::getJson($ret);
        }

        //接口业务逻辑处理
        $param = $_REQUEST['param'];	//传入的参数数组
        $param = json_decode($param, true);
        switch($type)
        {
            case 'movie_match':		//太和，e票网影片匹配
                $ret = self::addMatchMovie($param);
                break;
            case 'get_today_movielist_by_city':		//某城市当日上映的影片列表
                $ret = self::getMovieList($param);
                break;
        }
        GeneralFunc::getJson($ret);
    }
    /**
     * 影片匹配
     */
    public static function addMatchMovie($param){
        $iThMovieID = $param['movieID'];
        $sThMovieName = $param['movieName'];
        $dPlayTime = $param['moviePlayDate'];
        if(empty($iThMovieID) || empty($sThMovieName) || empty($dPlayTime)){
            return GeneralFunc::returnErr(ErrorParse::getErrorNo('param_lost'), ErrorParse::getErrorDesc('param_lost'));
        }
        $MovieInfo = MovieProcess::getCinemaInfoByName($sThMovieName,$dPlayTime);
        if(empty($MovieInfo)){
            return GeneralFunc::returnErr(ErrorParse::getErrorNo('param_error'), ErrorParse::getErrorDesc('param_error'));
        }
        $MovieMatchInfo = OutMovieProcess::getMatchMovieInfo($MovieInfo['iMovieID']);
        //存在
        if(!empty($MovieMatchInfo)){
            //如果movieid一致的话，就返回影片信息
            if($MovieMatchInfo['iThMovieID']==$iThMovieID){
                $ret = array('epMovieID'=>$MovieInfo['iMovieID'],'epMovieName'=>$MovieInfo['sMovieName']);
                return array('nErrCode'=>0, 'nResult'=>$ret);
            }else{
                //交叉
                $MovieMatchInfo2 = OutMovieProcess::getMatchMovieInfoByThID($iThMovieID);
                if(!empty($MovieMatchInfo2) && $MovieMatchInfo2['iMovieID']!=$MovieInfo['iMovieID']){
                    return GeneralFunc::returnErr(ErrorParse::getErrorNo('param_error'), ErrorParse::getErrorDesc('param_error'));
                }
                //不一致的话，就说明需要进行修改操
                OutMovieProcess::updateMatchInfo($MovieInfo['iMovieID'],$iThMovieID,$sThMovieName);
                return array('nErrCode'=>0, 'nResult'=>array('epMovieID'=>$MovieInfo['iMovieID'],'epMovieName'=>$sThMovieName));
            }
        }
        $editInfo = array('iMovieID'=>$MovieInfo['iMovieID'],
            'iThMovieID'=>$iThMovieID,'sThMovieName'=>$sThMovieName);
        $iFlag = OutMovieProcess::addMatchMovieInfo($editInfo);
        if(empty($iFlag)){
            return GeneralFunc::returnErr(ErrorParse::getErrorNo('db_insert_err'), ErrorParse::getErrorDesc('db_insert_err'));
        }
        $ret = array('epMovieID'=>$MovieInfo['iMovieID'],'epMovieName'=>$MovieInfo['sMovieName']);
        return array('nErrCode'=>0, 'nResult'=>$ret);
    }

    /**
     * 获取城市当日上映的影片列表
     */
    public static function getMovieList($param){
        $iCityID = $param['cityID'];
        if(empty($iCityID)){
            return GeneralFunc::returnErr(ErrorParse::getErrorNo('param_lost'), ErrorParse::getErrorDesc('param_lost'));
        }
        $cityInfo = CityProcess::getCityInfoByCityId($iCityID);
        if (empty($cityInfo)){
            return GeneralFunc::returnErr(ErrorParse::getErrorNo('param_error'), ErrorParse::getErrorDesc('param_error'));
        }
        //获取影院id列表
        $sCinemaID = CinemaProcess::getCinemaIdListByCity($iCityID);
        //获取影片列表
        $movieList = MovieProcess::getMovieListByCity($sCinemaID);
        return array('nErrCode'=>0, 'nResult'=>$movieList);
    }


    /**
     * 密钥验证
     * @param string $type 接口标识
     * @param string $key 传入的密钥
     * @return bool true－密钥正确；false－密钥错误
     */
    private function checkKey($type, $key)
    {
        return $key == md5(implode('@', array($type, Yii::app()->params['taihe_Partner'])));
    }
}