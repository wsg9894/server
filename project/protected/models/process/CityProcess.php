<?php

/**
 * CityProcess - 城市地区操作类
 * @author anqing
 * @version V1.0
 */


class CityProcess
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

	public static function getCityInfo()
	{
		$SQL = sprintf("select iCityID,sCityName,sCityPY,iHotCity from {{b_city}} ORDER by sCityPY");
		return DbUtil::queryAll($SQL);
	}

	public static function getCityInfoByCityId($cityID)
	{
		$SQL = sprintf("select * from {{b_city}} where iCityID = $cityID");
		return DbUtil::queryRow($SQL);
	}

	public static function getCityInfoByName($sCityName)
	{
		$SQL = sprintf("select * from {{b_city}} where positionName LIKE '%%%s%%'",$sCityName);
		return DbUtil::queryRow($SQL);
	}
	
	/**
	 * 获取城市列表（By 省份id）
	 * 
	 * @param int $proID 省份id
	 * @return array[][] 省份列表
	 */
	public static function getCityList($proID)
	{
		$sql = sprintf("select iCityID,sCityName from {{b_city}} where priviceID=%d", $proID);
		return DbUtil::queryAll($sql);
	}
	
	/**
	 * 获取区县列表（By 城市id）
	 *
	 * @param int $cityID 城市id
	 * @return array[][] 区县列表
	 */
	public static function getRegionList($cityID)
	{
		$sql = sprintf("select iRegionID,sRegionName from {{b_region}} where iCityID=%d", $cityID);
		return DbUtil::queryAll($sql);
	}
	
}