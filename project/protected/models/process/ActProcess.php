<?php

/**
 * ActProcess - 活动信息操作类
 * @author luzhizhong
 * @version V1.0
 */


class ActProcess
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
	 * 获取活动信息
	 *
	 * @param int $aid 活动id
	 * @param array[] $fields 所要字段列表，一维数组，如果为空则获取全部
	 * @return array[] 活动信息，一维数组
	 */
	public static function getActInfo($aid, $fields)
	{
		$actDBObj = new B_HuodongDB();

		if(is_array($fields) and !empty($fields))
		{
			//过滤掉无效字段
			$actInfo = array_intersect($fields, $actDBObj->attributeNames());
			$selStr = implode(',', $actInfo);
			$sql = 'SELECT '.$selStr.' FROM {{b_huodong}} where iHuoDongID='.$aid;
		}else{
			$sql = 'SELECT * FROM {{b_huodong}} where iHuoDongID='.$aid;
		}
		
		return DbUtil::queryRow($sql);
	}
}