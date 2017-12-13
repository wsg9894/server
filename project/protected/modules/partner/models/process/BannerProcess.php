<?php

/**
 * BannerProcess - banner位操作类
 * @author luzhizhong
 * @version V1.0
 */


class BannerProcess
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
	 * 获取banner位列表（不考虑翻页）
	 *
	 * @param array[] $fields 所要字段列表，一维数组，如果为空则获取全部
	 * @param string $filter where添加
	 * @return array[][] banner位列表，二维数组
	 */
	public static function getBannerList($fields=array(), $filter='')
	{
		$bannerDBObj = new G_BannerDB();
//		print_r($bannerDBObj->attributeNames());die;
		//过滤掉无效字段
		$fields = array_intersect($fields, $bannerDBObj->attributeNames());
		if(is_array($fields) and !empty($fields))
		{
			$selStr = '`'.implode('`,`', $fields).'`';
		}else{
			$selStr = '*';
		}
		
		$whereStr = empty($filter) ? 'WHERE 1' : 'WHERE 1 AND '.$filter;
		$sql = 'SELECT '.$selStr.' FROM {{g_banner}} '.$whereStr.' ORDER BY `weight` DESC';
//		echo $sql;die;
		$bannerList = DbUtil::queryAll($sql);
		return $bannerList;
	}
}