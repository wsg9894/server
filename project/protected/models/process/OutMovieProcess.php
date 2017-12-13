<?php
class OutMovieProcess
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

    public static function addMatchMovieInfo($editInfo){
        $MovieInfo = self::filterInputFields($editInfo,O_MovieDB::model()->attributes);
        try
        {
            $uaDBObj = new O_MovieDB();

            reset($MovieInfo);
            for($i=0; $i<count($MovieInfo); $i++)
            {
                $cField = current($MovieInfo);
                $key = key($MovieInfo);
                $uaDBObj->$key = $cField;
                next($MovieInfo);
            }

            if($uaDBObj->validate() and $uaDBObj->save())
            {
                return $uaDBObj->attributes['iMovieID'];
            }
        }
        catch(Exception $e)
        {
        }

        return 0;
    }

    /**
     * 通过影片id获取匹配关系
     * @param $sThMovieID
     * @param $iMovieID
     * @return array
     */
    public static function getMatchMovieInfo($iMovieID){
        $sql = sprintf("SELECT *
				FROM {{out_movie}}
				WHERE iMovieID = %d",$iMovieID);
        return DbUtil::queryRow($sql);
    }

    public static function getMatchMovieInfoByThID($sThMovieID){
        $sql = sprintf("SELECT *
				FROM {{out_movie}}
				WHERE  iThMovieID = '%s'",$sThMovieID);
        return DbUtil::queryRow($sql);
    }

    public static function updateMatchInfo($iMovieID,$iThMovieID,$sThMovieName)
    {
        $SQL = sprintf("update {{out_movie}}  set iThMovieID = '%s',sThMovieName='%s' where iMovieID=%d",$iThMovieID,$sThMovieName,$iMovieID);
        return DbUtil::execute($SQL);
    }

    public static function getMovieMatchList(){
        $sql = sprintf("SELECT iMovieID
				FROM {{out_movie}}");
        return DbUtil::queryAll($sql);
    }
}