<?php

/**
 * ToolsController - 通用工具控制器
 * @author luzhizhong
 * @version V1.0
 */

class ToolsController extends Controller
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
	 * 获取微信Openid
	 * @param int $isSetSession
	 * @param string $backUrl 需要urlencode
	 * return backUrl，带openid参数
	 */
	public function actionGetWXOpenID()
	{
		$code = $_REQUEST['code'];
		$setSession = isset($_REQUEST['setSession']) ? $_REQUEST['setSession'] : 1;
		$backUrl = empty($_REQUEST['retuUrl']) ? 'index.php?r=ScoreStore/Site' : urldecode($_GET['retuUrl']);
		$backUrl = str_replace(Yii::app()->params['wxConf']['getOpenid_RetuUrl_ParamSpace'], '&', $backUrl);
		
		$wx = new Weixin();
		$openid = $wx->reqOpenidOnOAuth($code);
		
		if($setSession)
		{
			//写入Session
			$sessObj = new Session();
			if(!$sessObj->is_registry('iUserID'))
			{
				$uInfo = UserProcess::getUInfoByOpenid($openid, array('iUserID', 'sPhone'));
				if(!empty($uInfo))
				{
					$sessObj->add('iUserID', $uInfo['iUserID']);
					$sessObj->add('sPhone', $uInfo['sPhone']);
					
					//以下用于个人中心 lzz update at 2017-08-22
					$sessObj->add('ok', TRUE);
					$wxUInfo = $wx->getUInfo($openid);
					$sessObj->add('weixin',$wxUInfo);
					
				}
			}
			$sessObj->add('openid', $openid);
		}
		
		$backUrl .= FALSE === strpos($backUrl, '?') ? sprintf('?openid=%s', $openid) : sprintf('&openid=%s', $openid);
		GeneralFunc::gotoUrl($backUrl);
	}
	
	/**
	 * 创建保存为桌面代码
	 * @param String $filename 保存的文件名
	 * @param String $url      访问的连接
	 * @param String $icon     图标路径
	 */
	public function actionCreateShortCut()
	{
		$filename = 'E票网.url';
		$url = 'http://m.epiaowang.com/';
		$icon = 'http://m.epiaowang.com/favicon.ico';
		
		// 创建基本代码
		$shortCut = "[InternetShortcut]\r\nIDList=[{000214A0-0000-0000-C000-000000000046}]\r\nProp3=19,2\r\n";
		$shortCut .= "URL=".$url."\r\n";
		if($icon){
			$shortCut .= "IconFile=".$icon."";
		}
	
		header("content-type:application/octet-stream");
	
		// 获取用户浏览器
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		$encode_filename = rawurlencode($filename);
	
		// 不同浏览器使用不同编码输出
		if(preg_match("/MSIE/", $user_agent)){
			header('content-disposition:attachment; filename="'.$encode_filename.'"');
		}else if(preg_match("/Firefox/", $user_agent)){
			header("content-disposition:attachment; filename*=\"utf8''".$filename.'"');
		}else{
			header('content-disposition:attachment; filename="'.$filename.'"');
		}
	
		echo $shortCut;
	}
	
	
	/**
	 * 给老用户加积分
	 * @param string $backUrl
	 */
	public function actionAddScoreToOldUsers()
	{
		exit(0);
		
		Yii::import('application.modules.scoreStore.models.process.*');
		
		//微信绑定有礼
		$sql = "select DISTINCT(sPhone) AS sPhone from tb_b_useroutpart where sPhone in(select sPhone from tb_e_userbaseinfo where iUserID in (select iUserID from tb_e_huodong where iHuoDongID = 36))";
		$uList = DbUtil::queryAll($sql);
		
		for($i=0; $i<count($uList); $i++)
		{
			$sPhone = $uList[$i]['sPhone'];
			
			$sql = "select iUserID from {{e_userbaseinfo}} where sPhone='".$sPhone."'";
			$tempInfo = DbUtil::queryRow($sql);
			$uid = $tempInfo['iUserID'];
			
			if(ScoreProcess::getScoreLogCount("uid=".$uid." and source=3")>0)
			{
				continue;
			}
			
			$sql = "insert into {{s_scorelog}} set uid=".$uid.",phone='".$sPhone."',score=5,source=3,`desc`='微信绑定有礼',createtime='2016-10-14 18:00:00'";							
			DbUtil::execute($sql);
			
			$sql = "update {{e_userbaseinfo}} set iCurScore=iCurScore+5,iTotalScore=iTotalScore+5 where iUserID=".$uid;
			DbUtil::execute($sql);
		}
		
		echo 'ok';
		exit(0);
	}
	
	/**
	 * 修改'绑定有礼'时间
	 * @param string $backUrl
	 */
	public function actionUpdateBandingTime()
	{
		exit(0);
		Yii::import('application.modules.scoreStore.models.process.*');
	
		//微信绑定有礼
		$sql = "select lid,phone from tb_s_scorelog where source=3 and createtime='2016-10-14 18:00:00'";
		$uList = DbUtil::queryAll($sql);
		
		for($i=0; $i<count($uList); $i++)
		{
			$lid = $uList[$i]['lid'];
			$sPhone = $uList[$i]['phone'];
			
			$sql = "select dCreateTime from {{b_useroutpart}} where sPhone='".$sPhone."'";
			$tempInfo = DbUtil::queryRow($sql);
			
			if(!empty($tempInfo))
			{
				$dCreateTime = $tempInfo['dCreateTime'];
				
				$sql = "update {{s_scorelog}} set createtime='".$dCreateTime."' where lid=".$lid;
				DbUtil::execute($sql);
			}
		}
		echo 'ok';
	}
	
	/**
	 * '血战钢锯岭'数据统计，2016-12-20
	 * @param string $backUrl
	 */
	public function actionXZGJL_Count()
	{
		exit(0);
		ini_set('max_execution_time', 3600);
		header("content-type:text/html;charset=utf-8");
		$inFile = Yii::getPathOfAlias('application.data').'/cinemaData.txt';
		$outFile = Yii::getPathOfAlias('application.data').'/arrangeData.xls';
		
		$iMovieID = 1866;	//‘血战钢锯岭’影片id
		$cinemaList = array();
		if(file_exists($inFile))
		{
			$fp = fopen($inFile, "r");
			if($fp)
			{
				while(!feof($fp))
				{
					$tempArr = explode('	', fgets($fp, 1024));
					$cinemaList[] = array('cinemaName'=>trim($tempArr[0]), 'sellNum'=>trim($tempArr[1]));
				}
			}
			fclose($fp);
		}
		
		//导出具体的排期信息
		foreach ($cinemaList as $index => $cinemaInfo)
		{
			//获取影院id
			$sql = sprintf("SELECT iCinemaID FROM {{b_cinema}} WHERE TRIM(sCinemaName)='%s'", trim($cinemaInfo['cinemaName']));
			$tempArr = DbUtil::queryRow($sql);
			if(empty($tempArr['iCinemaID']))
			{
				continue;
			}
			$iCimemaID = $tempArr['iCinemaID'];
			
			//此影院的排片数量
			$sql = sprintf("SELECT iCinemaID,sCinemaName,iRoomID,sRoomName,dBeginTime,dEndTime,mPrice,iSellSeatNum
					 FROM {{l_sellseatlog_xzgjl}}
					 WHERE iMovieID=%d AND iCinemaID=%d AND dCreateTime<'2016-12-16 00:00:00'", $iMovieID, $iCimemaID);
			$tempArr = DbUtil::queryAll($sql);
			$arrangeCount = count($tempArr);
			$sellNum = round(trim($cinemaInfo['sellNum'])*1.339);
			
			$tempCount = 0;
			while ($sellNum>0)
			{
				if($tempCount%3==0)
				{
					$iSellSeatNum = rand(1,2);
				}else if($tempCount%4==0){
					$iSellSeatNum = rand(1,3);
				}else if($tempCount%6==0){
					$iSellSeatNum = rand(1,4);
				}else{
					$iSellSeatNum = 1;
				}
				
				if($iSellSeatNum>$sellNum)
				{
					$iSellSeatNum = $sellNum;
					$sellNum = 0;
				}
				
				$i = rand(0, ($arrangeCount-1));
				if(empty($tempArr[$i]))
				{
					continue;
				}
				$msg = sprintf("%d	%s	%d	%s	%s	%s	%d	%d	%s\n"
						, $tempArr[$i]['iCinemaID']
						, $tempArr[$i]['sCinemaName']
						, $tempArr[$i]['iRoomID']
						, $tempArr[$i]['sRoomName']
						, $tempArr[$i]['dBeginTime']
						, $tempArr[$i]['dEndTime']
						, $tempArr[$i]['mPrice']
						, $iSellSeatNum
						, round(($tempArr[$i]['mPrice']-19.9)*$iSellSeatNum, 3));
				
				$fp = fopen($outFile, "a+");
				flock($fp, LOCK_EX);
				fwrite($fp, $msg);
				flock($fp, LOCK_UN);
				fclose($fp);
				
				$sellNum -= $iSellSeatNum;
				$tempCount ++;
			}
			
		}
		
		echo 'ok';
	}
	
	/**
	 * 将tb_b_cinema表中的经纬度信息（sCoordinates）转为平面坐标（笛卡尔坐标），2016-12-30
	 * 便于按照经纬度信息查询就近的影院列表
	 */
	public function actionGeodeticToCartesian_Cinema()
	{
		exit(0);
		
		//获取影院信息
		$sql = sprintf("SELECT iCinemaID,sCoordinates FROM {{b_cinema}} WHERE iCartesianX=0 or iCartesianY=0 ORDER BY iCinemaID");
		$cinemaList = DbUtil::queryAll($sql);
		$cinemaCount = count($cinemaList);
		
		for($i=0; $i<$cinemaCount; $i++)
		{
			$cinemaID = $cinemaList[$i]['iCinemaID'];
			$coordinates = $cinemaList[$i]['sCoordinates'];
			$tempArr = explode(',', $coordinates);
			
			if(!empty($tempArr[0]) and !empty($tempArr[1]))
			{
				$cartesianInfo = LBS::geodeticToCartesian($tempArr[1],$tempArr[0]);
				if(!empty($cartesianInfo))
				{
					$sql = sprintf("UPDATE {{b_cinema}} SET iCartesianX=%d,iCartesianY=%d WHERE iCinemaID=%d"
							,$cartesianInfo['x'],$cartesianInfo['y'],$cinemaID);
					DbUtil::execute($sql);
					
				}
			}
		}
		echo 'ok';
	}

	/**
	 * 导入网票影院信息（数据导入+匹配），to tb_b_cinema
	 * 小斌给到的是网票新增+匹配（给出了sCinemaInterfaceNo）好的影院信息
	 */
	public function actionLoadWPCinema()
	{
		ini_set('max_execution_time', 3600);
		ini_set('memory_limit','1024M');

		//参数设定
		$fieldNum = 11;		//多少个字段数据
		$loadType = array('data_lost'=>'10001'			//录入数据不全
		,'city_not_exist'=>'10002'		//城市不存在
		,'region_not_exist'=>'10003'	//区县不存在
		);

		$inFileName = Yii::app()->basePath.'/data/wpCinema.txt';							//影院信息文件
		$outFileName_OK = Yii::app()->basePath.'/data/ok.xls';								//数据OK
		$outFileName_Data_Lost = Yii::app()->basePath.'/data/dataLost.xls';					//数据缺失
		$outFileName_City_NotExist = Yii::app()->basePath.'/data/cityNotExist.xls';			//城市不存在的影院信息
		$outFileName_Region_NotExist = Yii::app()->basePath.'/data/regionNotExist.xls';		//区县不存在的影院信息
		$tableName = 'b_cinema';

		if(!file_exists($inFileName))
		{
			return false;
		}

		//获取影院列表
		$cinemaList = array();
		$fp = fopen($inFileName, "r");
		if($fp)
		{
			while(!feof($fp))
			{
				$tempArr = trimArray(explode('	', fgets($fp, 20480)));
				$tempArr['errType'] = 'ok';
				$cinemaList[] = $tempArr;
			}
		}
		fclose($fp);
		$cinemaCount = count($cinemaList);

		//梳理数据（获取城市id、验证区县信息）
		for($i=0; $i<$cinemaCount; $i++)
		{
			//数据验证
			if(count($cinemaList[$i])<$fieldNum or empty($cinemaList[$i][0]) or empty($cinemaList[$i][1]) or empty($cinemaList[$i][2]) or empty($cinemaList[$i][3]) or empty($cinemaList[$i][9]) or empty($cinemaList[$i][10]))
			{
				$cinemaList[$i]['errType'] = $loadType['data_lost'];
				continue;
			}

			//获取城市id
			$sql = sprintf("SELECT iCityID FROM {{b_city}} WHERE sCityName='%s'", $cinemaList[$i][1]);
			$cityInfo = DbUtil::queryRow($sql);
			if(empty($cityInfo) or empty($cityInfo['iCityID']))
			{
				$cinemaList[$i]['errType'] = $loadType['city_not_exist'];
				continue;
			}else{
				$cinemaList[$i]['cityID'] = $cityInfo['iCityID'];
			}

			//验证区县信息（是否存在）
			$sql = sprintf("SELECT iRegionID FROM {{b_region}} WHERE sRegionName='%s' AND iCityID=%d", $cinemaList[$i][2], $cinemaList[$i]['cityID']);
			$regionInfo = DbUtil::queryRow($sql);
			if(empty($regionInfo) or empty($regionInfo['iRegionID']))
			{
				//不存在，则录入
				$sql = sprintf("INSERT INTO {{b_region}} SET sRegionName='%s',iCityID=%d", $cinemaList[$i][2], $cinemaList[$i]['cityID']);
				DbUtil::execute($sql);

				$msg = sprintf("%s	%s	%s	%s	%s	%s	%s	%s	%s	%s	%s\n", $cinemaList[$i][0], $cinemaList[$i][1], $cinemaList[$i][2], $cinemaList[$i][3], $cinemaList[$i][4], $cinemaList[$i][5], $cinemaList[$i][6], $cinemaList[$i][7], $cinemaList[$i][8], $cinemaList[$i][9], $cinemaList[$i][10]);
				$this->writeXls($outFileName_Region_NotExist, $msg);

				$cinemaList[$i]['errType'] = $loadType['region_not_exist'];
				continue;
			}
		}

		//数据录入
		for($i=0; $i<$cinemaCount; $i++)
		{
			$msg = sprintf("%s	%s	%s	%s	%s	%s	%s	%s	%s	%s	%s\n", $cinemaList[$i][0], $cinemaList[$i][1], $cinemaList[$i][2], $cinemaList[$i][3], $cinemaList[$i][4], $cinemaList[$i][5], $cinemaList[$i][6], $cinemaList[$i][7], $cinemaList[$i][8], $cinemaList[$i][9], $cinemaList[$i][10]);
			switch($cinemaList[$i]['errType'])
			{
				case $loadType['data_lost']:
// 					$msg = sprintf("%s	%s	%s\n", $cinemaList[$i][1], $cinemaList[$i][2], $cinemaList[$i][3]);
					$this->writeXls($outFileName_Data_Lost, $msg);
					break;
				case $loadType['city_not_exist']:
// 					$msg = sprintf("%s	%s	%s\n", $cinemaList[$i][1], $cinemaList[$i][2], $cinemaList[$i][3]);
					$this->writeXls($outFileName_City_NotExist, $msg);
					break;
// 				case $loadType['region_not_exist']:
// 					break;
				default:
					//接口方影院信息
					$sql = sprintf("SELECT iHallNum FROM {{t_cinema}} WHERE sCinemaInterfaceNo='%s'", $cinemaList[$i][0]);
					$interfCinemaInfo = DbUtil::queryRow($sql);
					$roomNum = $interfCinemaInfo['iHallNum']>0 ? $interfCinemaInfo['iHallNum'] : 0;		//影厅数量
					$introduction = empty($cinemaList[$i][8]) ? '暂无' : $cinemaList[$i][8];				//影院介绍

					//E票网影院信息（已经添加过，则重新update）
					$sql = sprintf("SELECT iCinemaID FROM {{%s}} WHERE sCinemaName='%s' AND iCityID=%d", $tableName, $cinemaList[$i][3], $cinemaList[$i]['cityID']);
					$epwCinemaInfo = DbUtil::queryRow($sql);

					if(empty($epwCinemaInfo))
					{
						$sql = sprintf("INSERT INTO {{%s}} SET sCinemaName='%s',iCityID=%d,sRegion='%s',sTraffiCroutes='%s',sAddress='%s',iRoomNum=%d,sTel='%s',sCoordinates='%s',sIntroduction='%s',iInterfaceID=8,sCinemaInterfaceNo='%s',sCinemaInterfaceNos='%s',sCinemaInterfaceName='网票网',sCinemaInterface='网票网(Wpiao)',sCreateUser='lzz4'",$tableName, $cinemaList[$i][3],$cinemaList[$i]['cityID'],$cinemaList[$i][2],$cinemaList[$i][6],$cinemaList[$i][4],$roomNum,$cinemaList[$i][5],$cinemaList[$i][9].",".$cinemaList[$i][10],$introduction,$cinemaList[$i][0],$cinemaList[$i][0]);
						DbUtil::execute($sql);

						//设定电影卡关联
						$cinemaID = DbUtil::lastInsertID();
						setCouponArea($cinemaID);
					}else{
						$cinemaID = $epwCinemaInfo['iCinemaID'];
						$sql = sprintf("UPDATE {{%s}} SET sRegion='%s',sTraffiCroutes='%s',sAddress='%s',iRoomNum=%d,sTel='%s',sCoordinates='%s',sIntroduction='%s',iInterfaceID=8,sCinemaInterfaceNo='%s',sCreateUser='lzz4' WHERE iCinemaID=%d",$tableName, $cinemaList[$i][2],$cinemaList[$i][6],$cinemaList[$i][4],$roomNum,$cinemaList[$i][5],$cinemaList[$i][9].",".$cinemaList[$i][10],$introduction,$cinemaList[$i][0],$cinemaID);
						DbUtil::execute($sql);
					}

// 					$msg = sprintf("%s	%s	%s\n", $cinemaList[$i][1], $cinemaList[$i][2], $cinemaList[$i][3]);
					$this->writeXls($outFileName_OK, $msg);
					break;
			}
		}

		echo 'ok';
	}
	
	/**
	 * 写入excel
	 * @param string $fileName 文件名
	 * @param string $msg 写入内容
	 */
	private function writeXls($fileName, $msg)
	{
		$fp = fopen($fileName, "a+");
		flock($fp, LOCK_EX);
		fwrite($fp, $msg);
		flock($fp, LOCK_UN);
		fclose($fp);
	}
	
	
	public function actionSetCouponTicketArea()
	{
		exit(0);
		$sql = sprintf("select iCinemaID from tb_b_cinema where iInterfaceID=11 and sCinemaInterfaceNo in('10097','10124','10154','10179','10236','10422','10463','10518','107','10764','10805','10816','10865','10917','10956','11006','11020','11086','1131','11499','1162','11856','11969','12267','12341','1235','12587','12651','12724','12803','12833','12835','13062','13092','13095','13236','13245','13268','13285','13317','13431','13441','13454','13512','13576','13590','13604','13623','13631','13634','13645','13683','13713','1377','13887','13892','13958','13976','13995','1402','1426','1428','14370','14384','14402','14512','14533','14541','14559','14585','14588','14657','14677','14827','14924','14997','14998','15061','15092','15098','15101','15116','15192','15202','15226','15228','15240','15266','15349','15359','15383','15385','15394','15418','15443','15462','15557','15662','15676','15715','15720','15745','15750','15751','15815','15835','15885','15930','15938','15977','15997','16035','16042','16052','16075','16079','16082','16106','16120','16127','16137','1615','16170','16175','16197','16213','16226','16302','16315','16318','16327','16332','16336','16391','16396','16397','16430','16437','16443','16459','16467','16469','16470','16475','16498','16501','16504','16515','16539','16551','16555','16556','16557','16560','16570','16583','16587','16605','16621','16650','16651','16660','16670','16678','16692','16694','16701','16704','16712','16719','16729','16731','16736','16764','16765','16778','16782','16783','16787','16795','16809','16816','16819','16824','16825','16828','16840','16845','16854','16888','16889','16892','16893','16894','16897','16898','16905','16909','16910','16916','16918','16921','16922','16926','16931','16934','16940','16942','16948','16952','16957','16958','16965','16969','16979','16981','16993','16994','16996','16997','16998','16999','17028','17029','17034','17037','17039','17059','17068','17069','17087','17089','17090','17099','17102','17103','17109','17110','17120','17124','17128','17131','17132','17141','17146','17148','17155','17165','17166','17170','17171','17173','17175','17179','17183','17198','17203','17205','17212','17218','17222','17223','17232','17240','17250','17255','17264','17277','17289','17290','173','17304','17321','17323','17325','17330','17337','17342','17343','17344','17346','17352','17353','17354','17355','17356','17368','17370','17371','17375','17382','17384','17393','17398','17399','17401','17406','17407','17408','17413','17416','17417','17418','1742','17423','17429','17431','1774','1815','191','192','1962','2142','2191','2239','2299','23614','23620','23623','23627','23632','23634','23640','23643','23645','23649','23651','23653','23654','23655','23656','23658','23661','23673','23685','23687','23688','23693','23694','23697','23709','23712','23719','23723','23730','23731','23733','23747','23749','23760','23763','23764','23769','23785','23789','23793','23794','23796','23798','23803','23817','23879','23915','2393','23981','23997','24017','24018','2414','2505','2535','2541','2573','2588','2749','275','318','33','375','387','395','398','4','413','451','459','471','5139','5182','5216','5519','563','5724','5729','5787','579','588','5890','5938','6204','621','6316','6321','6335','6344','642','6499','680','684','71','7239','7347','7634','7686','7757','7892','7935','7977','8017','8065','8114','8137','8263','830','832','833','8360','8542','8590','867','8676','8824','8839','9037','9062','910','9180','9188','9212','923','9264','9271','9283','931','9333','9453','9647','9705','9750','9875','9934','10916','12612','13719','13810','1796','704','7888')");
		$cinemaList = DbUtil::queryAll($sql);
		$cinemaCount = count($cinemaList);
		
		for($i=0; $i<$cinemaCount; $i++)
		{
			$cinemaID = $cinemaList[$i]['iCinemaID'];
			setCouponArea($cinemaID);
		}
		echo 'ok';
	}
	
	/**
	 * 清理useroutpart的冗余数据
	 * 1、清理手机号-Openid一对多的情况（一个手机号对应多个Openid）
	 */
	public function actionParseUserOutpart()
	{
		exit(0);
				
		$sql = "SELECT sPhone,COUNT(outPart) FROM {{b_useroutpart}} GROUP BY 1 HAVING COUNT(outPart)>1 ORDER BY 2 DESC";
		$partList = DbUtil::queryAll($sql);
		$partCount = count($partList);
		
		$wx = new Weixin();
		
		for($i=0; $i<$partCount; $i++)
		{
			$sphone = $partList[$i]['sPhone'];
			$sql = sprintf("SELECT ids,outPart FROM {{b_useroutpart}} WHERE sPhone='%s' ORDER BY dCreateTime DESC", $sphone);
			$tempList = DbUtil::queryAll($sql);
			$tempCount = count($tempList);
			
			$subscribeFlag = 0;
			for($j=0; $j<$tempCount; $j++)
			{
				$id = $tempList[$j]['ids'];
				$openId = $tempList[$j]['outPart'];
				
				if($wx->isFocus($openId))
				{
					$sql = sprintf("DELETE FROM {{b_useroutpart}} WHERE sPhone='%s' AND ids!=%d", $sphone, $id);
					DbUtil::execute($sql);
					$subscribeFlag = 1;
					break;
				}
			}
			
			if($subscribeFlag==0)
			{
				$sql = sprintf("DELETE FROM {{b_useroutpart}} WHERE sPhone='%s' AND ids!=%d", $sphone, $tempList[0]['ids']);
				DbUtil::execute($sql);
			}
		}
		
		echo 'OK';
	}
	
	/**
	 * 设置电影卡关联（电影卡-影院）
	 */
	public function actionSetCouponArea()
	{
		$cinemaID = $_REQUEST['cinemaID'];
		if(empty($cinemaID) or !is_numeric($cinemaID))
		{
			echo 'ERR';
			exit(0);
		}
		setCouponArea($cinemaID);
		echo 'OK';
	}
	
	/**
	 * 刷票测试
	 */
	public function actionBrush()
	{
		exit(0);
		
		Yii::import('application.models.process.brush.*');			
		Yii::import('application.models.data.db.brush.*');

		/* 注水的接口信息追加
		$sql = "select outerOrderId,iUserId,iRoomMovieID,iInterfaceID,sInterfaceOrderNo,sInterfaceValidCode from tb_fill_order_seat where status in(10104,10105) and sInterfaceValidCode='' and outerorderid in(select outerOrderId from tb_fill_orders where iFakeNum=iownseats) order by dCreateTime limit 1000";
		$orderList = DbUtil::queryAll($sql);
		foreach($orderList as $k => $orderInfo)
		{
			//修改 order_seat
			$orderInterfaceInfo = BaseProcess::createOrderInterfaceInfoForFake($orderInfo['iInterfaceID']);
			$orderSeatInfo = array('iUserId' => $orderInfo['iUserId']
					, 'outerOrderId' => $orderInfo['outerOrderId']
					, 'sInterfaceOrderNo' => $orderInterfaceInfo['sInterfaceOrderNo']
					, 'sInterfaceValidCode' => $orderInterfaceInfo['sInterfaceValidCode']);
			
			BOrderProcess::updateOrderSeatInfo($orderInfo['iUserId'], $orderSeatInfo);
		}
		
		echo 'ok';
		exit(0);
		*/
		
		$iRoommovieID = '8135372834';
		$uInfo = BaseProcess::getRandUInfo();	//随机用户
		
		//获取排期座位图
		$seatList = BaseProcess::getMovieSeatList($iRoommovieID);
		
		print_r($seatList);
		exit(0);
		
		if(FALSE == empty($seatList))
		{
			//获取待锁座座位信息
			$lockSeatList = BaseProcess::getLockSeatList($seatList, $lockModel=300, $lockCount=4);
			
			if(FALSE == empty($lockSeatList))
			{
				//锁座
				$outerOrderId = BaseProcess::lockSeat($iRoommovieID, $lockSeatList, $uInfo);
				echo $outerOrderId;
				exit(0);
				
				if(FALSE == empty($outerOrderId))
				{
					//下单
					BaseProcess::subPayOrder($outerOrderId, $lockSeatList['seatCount'], $uInfo);
				}
			}
		}
		exit(0);
	}
	
	/**
	 * 刷票测试
	 */
	public function actionTest()
	{
		Yii::import('application.models.process.brush.*');			
		Yii::import('application.models.data.db.brush.*');
		
		$iCinemaID = 6847;
		$ret = BaseProcess::isFirstTierCity($iCinemaID);
		if($ret)
		{
			echo 'Yes';
		}else{
			echo 'No';
		}
		
		exit(0);
		//$str = strstr('6排04,6排05,6排06,6排07', ',');
		$tempArr = explode(',', 'D排05,D排06,D排07,D排08');
		array_pop($tempArr);
		$str = implode(',', $tempArr);
		echo $str;
		exit(0);
		//$str = '{"resultCode":"1","resultDesc":"签名校验失败","resultData":""}';
		$str = file_get_contents("http://101.201.79.224:6080/order/updateOrder.do?key=123&orderId=siewri&orderStatus=100000");
		$str = json_decode($str,true);
		print_r($str);
		
	}
	
	/**
	 * APK包解析测试
	 */
	public function actionAPKParse()
	{
		$appObj  = new Apkparser();
		$targetFile = '/data/epw/PHPProject/server/project/apk/2017-06-27/20170627101222.apk';
		
		if($appObj->open($targetFile))
		{
			$appName = $appObj->getAppName();		//应用名称
			$pageage = $appObj->getPackage();		//应用包名
			$verName = $appObj->getVersionName();	//版本名称
			$verCode = $appObj->getVersionCode();	//版本代码
			$perList = $appObj->getPermission();	//权限列表
		
			print_r($perList);
		}
		
	}
	/**
	 * 位置信息测试
	 */
	public function actionLBS()
	{
		$lng = '121.4596510000';
		$lat = '31.2204350000';
		
		$startTime = time();
		$ret = LBS::getLocationInfo($lat, $lng);
		
		$endTime = time();
		print_r($ret);
		
		echo $endTime-$startTime;
	}
}

/********************************************************************************************************************/

/**
 * 数组数据trim
 */
function trimArray($input)
{
	if (!is_array($input))
	{
		return trim($input);
	}
	return array_map('trimArray', $input);
}

/**
 * 设置电影卡关联（电影卡-影院）
 */
function setCouponArea($cinemaID)
{
	$standardCinemaID = 1428;		//北京耀莱成龙国际影城-西红门店
	
	$sql = sprintf("SELECT iCouponID,iChange2D,iChange3D,iChangeIMax,iChangeVip FROM {{e_couponticketcinemaarea}} WHERE iCinemaID=1428 ORDER BY iCouponID");
	$couponList = DbUtil::queryAll($sql);
	$couponCount = count($couponList);
	
	for($i=0; $i<$couponCount; $i++)
	{
		$sql = sprintf("REPLACE INTO {{e_couponticketcinemaarea}} SET iCouponID=%d,iCinemaID=%d,iChange2D=%d,iChange3D=%d,iChangeIMax=%d,iChangeVip=%d,sCreateUser='lzz'", $couponList[$i]['iCouponID'], $cinemaID, $couponList[$i]['iChange2D'], $couponList[$i]['iChange3D'], $couponList[$i]['iChangeIMax'], $couponList[$i]['iChangeVip']);
		DbUtil::execute($sql);		
	}
}

