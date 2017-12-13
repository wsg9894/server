<?php

/**
 * CinemaProcess - 影院操作类
 * @author luzhizhong
 * @version V1.0
 */

date_default_timezone_set("Asia/Shanghai");
class PublicProcess
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

	public static function dateTime($days,$key,$time=""){
		$week = "周".mb_substr("日一二三四五六",date("w",strtotime($key)),1,"utf-8");
		$date = explode('-',$key);
		switch($days){
			case 0:
				$date = '今天'.($date[1]<=9 ? str_replace('0','',$date[1]) : $date[1]).'月'.($date[2]<=9 ? str_replace('0','',$date[2]) : $date[2]).'日';
				break;
			case 1:
				$date = '明天'.($date[1]<=9 ? str_replace('0','',$date[1]) : $date[1]).'月'.($date[2]<=9 ? str_replace('0','',$date[2]) : $date[2]).'日';
				break;
			case 2:
				$date = '后天'.($date[1]<=9 ? str_replace('0','',$date[1]) : $date[1]).'月'.($date[2]<=9 ? str_replace('0','',$date[2]) : $date[2]).'日';
				break;
			case 3:
				$date = $week.($date[1]<=9 ? str_replace('0','',$date[1]) : $date[1]).'月'.($date[2]<=9 ? str_replace('0','',$date[2]) : $date[2]).'日';
				break;
			case 4:
				$date = $week.($date[1]<=9 ? str_replace('0','',$date[1]) : $date[1]).'月'.($date[2]<=9 ? str_replace('0','',$date[2]) : $date[2]).'日';
				break;
			case 5:
				$date = $week.($date[1]<=9 ? str_replace('0','',$date[1]) : $date[1]).'月'.($date[2]<=9 ? str_replace('0','',$date[2]) : $date[2]).'日';
				break;
			case 6:
				$date = $week.($date[1]<=9 ? str_replace('0','',$date[1]) : $date[1]).'月'.($date[2]<=9 ? str_replace('0','',$date[2]) : $date[2]).'日';
				break;
			default:
				break;
		}
		if($time != ""){
			$date.=' '.$time.'　';
		}
		return $date;
	}
	public static function code($code){
		$url='https://api.weixin.qq.com/sns/jscode2session?appid='.ConfigParse::getWeixinKey("appId").'&secret='.ConfigParse::getWeixinKey("secret").'&js_code='.$code.'&grant_type='.ConfigParse::getWeixinKey("grant_type");
		$html =file_get_contents($url);
		$obj=json_decode($html);
		return $obj->openid;
	}
}