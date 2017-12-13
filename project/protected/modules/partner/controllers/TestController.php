<?php

/**
 * TestController - 测试控制器
 * @author luzhizhong
 * @version V1.0
 */
Yii::import('application.modules.partner.models.process.*');
//require("../admin/admininit.php");
class TestController extends Controller
{
	/**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array(
		);
	}

	public function actionIndex()
	{
		$type = $_REQUEST['type'];
		$key = md5($type.'@'.Yii::app()->params['interfPW_Partner']['KouL'].'@'.GeneralFunc::getCurDate());

		define("REQUEST_URL", Yii::app()->params['testUrl']."/index.php?r=partner/KouL&type=%s&key=%s&param=%s");
		switch($type)
		{
			case 'binding':
				$param = array('couponNo'=>'sdiofweir', 'couponPW'=>'sdfsd', 'uid'=>'123', 'phone'=>'13621083819');
				break;
			default:
				$param = array();
				break;
		}

		$url = sprintf(REQUEST_URL, $type, $key, json_encode($param));
		$result = file_get_contents($url);
		$result = json_decode($result, true);
		print_r($result);
	}

	//游戏流量导出
	public function actionPvexport(){
		$location = 'pvGame'.date('Y-m-d');
		header("Content-type:application/octet-stream");
		header("Accept-Ranges:bytes");
		header("Content-type:application/vnd.ms-excel");
		header("Content-Disposition:attachment;filename=".$location.".xls");
		header("Pragma: no-cache");
		header("Expires: 0");
		$pageConf = GameProcess::getpageConf();
		$str = '页面名称'.'	'.'来源页面名称'.'	'.'跳转次数';
		echo mb_convert_encoding(trim($str),'gbk','utf-8')."\n";
		$dBeginTime = '2017-10-19';
		$dEndTime = '2017-10-20';
		foreach($pageConf as $v){
			foreach($pageConf as $val){
				$ret = GameProcess::getPvexport($v['cid'],$val['cid'],$dBeginTime,$dEndTime);
				foreach($ret as $val1){
					if($val1['tot'] != 0){
						if($val1['gid']!=0&&$val1['pid']==0){
							$val1['sCurPConfName']=GameProcess::getGameDetails($val1['gid'],array('gname'))['gname'].$val1['sCurPConfName'];
						}
						if($val1['pid']!=0){
							if($val1['gid']!=0){
								$val1['sFromPConfName']=GameProcess::getGameDetails($val1['gid'],array('gname'))['gname'].$val1['sFromPConfName'];
							}
							$val1['sCurPConfName']=GameProcess::getGamePackageBypid($val1['pid'],array('name'))['name'].$val1['sCurPConfName'];
						}
						echo mb_convert_encoding(trim($val1["sCurPConfName"]),'gbk','utf-8').'	'.mb_convert_encoding(trim($val1["sFromPConfName"]),'gbk','utf-8').'	'.$val1["tot"]."\n";
					}
				}
			}
		}
	}

	//游戏下载量
	public function actionDownload(){
		$location = 'loadGame'.date('Y-m-d');
		header("Content-type:application/octet-stream");
		header("Accept-Ranges:bytes");
		header("Content-type:application/vnd.ms-excel");
		header("Content-Disposition:attachment;filename=".$location.".xls");
		header("Pragma: no-cache");
		header("Expires: 0");
		$str = '游戏名称'.'	'.'下载页面'.'	'.'下载量';
		echo mb_convert_encoding(trim($str),'gbk','utf-8')."\n";
		$dBeginTime = '2017-10-9';
		$dEndTime = '2017-10-16';
		$pageLoad = GameProcess::getpageLoad($dBeginTime,$dEndTime);
		foreach($pageLoad as $v){
			$gname = GameProcess::getGameDetails($v['gid'],array('gname'))['gname'];
			echo mb_convert_encoding(trim($gname),'gbk','utf-8').'	'.mb_convert_encoding(trim($v["sCurPConfName"]),'gbk','utf-8').'	'.$v["tot"]."\n";
		}
	}
}