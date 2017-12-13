<?php

/**
 * GeneralFunc - 一般操作类
 * @author luzhizhong
 * @version V1.1
 */

class GeneralFunc
{
	function __construct()
	{
	}
	function __destruct()
	{
	}
	/**
	 * url跳转
	 * @param string $url 跳转的url
	 * @return 无
	 */	
	public static function gotoUrl($url)
	{
		switch($url)
		{
			case "Back":
				echo "<script>history.back(-1)</script>";
				break;
			case "Pre":
				echo "<script>history.go(-1)</script>";
				break;
			case "Next":
				echo "<script>history.forward()</script>";
				break;
			default:				
				echo "<script>window.location='".$url."'</script>";
				break;
		}
	}
	/**
	 * alert操作
	 * @param string $text alert输出
	 * @return 无
	 */	
	public static function alert($text)
	{
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
		echo '<script>alert("'.$text.'")</script>';
	}
	
	/**
	 * 写日志功能实现（追加写）
	 * @param string $message 日志内容	 
	 * @param string $logFilePath 日志目录
	 * @param string $logFilePath 日志目录
	 * @return bool 写入结果
	 */	
	public static function writeLog($message,$logFilePath,$logFileName='')
	{
		if(empty($logFileName))
		{
			$logFilePath .= date('Y-m-d',time()).'.log';
		}else{
			$logFilePath .= $logFileName;
		}
		
		$tempStrLog = date('H:i:s').' '.$message."\n";
		$f = fopen($logFilePath, 'a');
		fwrite($f, $tempStrLog);
		fclose($f);
				
		unset($tempStrLog);
		return true;
	}
	
	/**
	 * 实现xml转换为数组
	 * @param string $xml xml数据
	 * @return array[][] 数组
	 */	
	public static function xml_to_array($xml)
	{
		$array = (array)(simplexml_load_string($xml));		
		foreach ($array as $key=>$item){
			$array[$key]  =  GeneralFunc::struct_to_array((array)$item);
			
			//lzz add at 20120913，如果为空，则以空string代替空array
			if(empty($array[$key]))	
			{
				$array[$key] = '';
			}
		}
		return $array;
	}
	
	private static function struct_to_array($item) {
		
		if(!is_string($item)) {
			$item = (array)$item;
			foreach ($item as $key=>$val){
				$item[$key]  =  GeneralFunc::struct_to_array($val);
				
				//lzz add at 20120913，如果为空，则以空string代替空array
				if(empty($item[$key]))	
				{
					$item[$key] = '';
				}
			}
		}
		//lzz add at 20120913，目的是从源头消除空格
		else{							
			$item = trim($item);
		}
		return $item;
	}
	
	/**
	 * 实现错误信息的xml/json包
	 * @param int $errCode 错误编号
	 * @param string $errStr 错误描述
	 * @param string $dType 错误描述
	 * @return string xml串
	 */	
	public static function returnErr($errCode, $errStr, $dType='json')
	{
		if(strtolower($dType)=='json')
		{
			return array('nErrCode' => $errCode, 'nDescription' => $errStr);			
		}else{
			return '<nErrCode>'.$errCode.'</nErrCode><nDescription>'.$errStr.'</nDescription>';
		}
	}

	/**
	 * 接口返回json串
	 */
	public static function getJson($data = array()){
		echo json_encode($data);exit(0);
	}

	/**
	 * 太和字符串截取
	 * @param $outerOrderId
	 * @return string
	 */
	public static function substrOrderID($outerOrderId){
		return substr($outerOrderId,-20);
	}
	
	/**
	 * 实现手机号码的验证操作
	 * @param string $mobile 手机号
	 * @return bool 验证结果
	 */	
	public static function isMobile($mobile)
	{
		if( !preg_match("/^13[0-9]{1}[0-9]{8}$|14[1357]{1}[0-9]{8}$|15[0-9]{1}[0-9]{8}$|18[0-9]{9}$/",$mobile) )
		{
			return false;
		}
		else
		{
			return true;
		}
	}
	/**
	 * 实现邮箱的验证操作
	 * @param string $email 邮箱
	 * @return bool 验证结果
	 */	
	public static function isEmail($email)
	{
		$retu = false;
	
		if( strstr($email,'@')&& strstr($email,'.') )
		{
			if(eregi("^([_a-z0-9]+([\._a-z0-9-]+)*)@([a-z0-9]{2,}(\.[a-z0-9-]{2,})*\.[a-z]{2,3})$", $email))
			{
				$retu = true;
			}
		}
		return $retu;
	}
	
	/**
	 * 获取IP地址
	 * @return string IP地址
	 */
	public static function getIP()
	{
		if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown'))
		{
			$ip = getenv('HTTP_CLIENT_IP');
		}
		else if (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown'))
		{
			$ip = getenv('HTTP_X_FORWARDED_FOR');
		}
		else if (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown'))
		{
			$ip = getenv('REMOTE_ADDR');
		}
		else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown'))
		{
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		else
		{
			$ip = '';
		}
		return $ip;
	}

	/**
	 * 获取当前时间
	 * @return string 当前时间（datetime型：YYYY-MM-DD HH:II:SS）
	 */
	public static function getCurTime()
	{
		return date('Y-m-d H:i:s');
	}

	/**
	 * 获取当前日期
	 * @return string 当前日期（datetime型：YYYY-MM-DD）
	 */
	public static function getCurDate()
	{
		return date('Y-m-d');
	}
	
	/**
	 * 脚本过滤（代码来源于网络，经验证，过滤效果还不错，可放心使用）
	 * 
	 * @param string $content 过滤前的文本信息
	 * @return string 过滤后的文本信息
	 */
	public static function filterHTML($content)
	{
		$search = array (
				"'<script[^>]*?>.*?</script>'si", 	// 去掉 javascript
				"'<style[^>]*?>.*?</style>'si", 	// 去掉 css
				"'<[/!]*?[^<>]*?>'si", 				// 去掉 HTML 标记
				"'<!--[/!]*?[^<>]*?>'si", 			// 去掉 注释标记
				"'([rn])[s]+'", 					// 去掉空白字符
				"'&(quot|#34);'i", 					// 替换 HTML 实体
				"'&(amp|#38);'i",
				"'&(lt|#60);'i",
				"'&(gt|#62);'i",
				"'&(nbsp|#160);'i",
				"'&(iexcl|#161);'i",
				"'&(cent|#162);'i",
				"'&(pound|#163);'i",
				"'&(copy|#169);'i",
				"'&#(d+);'e",						// 作为 PHP 代码运行
				
				"'&ldquo;'",						//lzz add at 2012-11-26 start
				"'&rdquo;'",						//lzz add at 2012-11-26 end
										
				"'&mdash;'",						//lzz add at 2012-12-05
				"'&hellip;'",
				"'&times;'",
				"'&lsquo;'",
				"'&rsquo;'",
				"'&middot;'",
				"'&#39;'"							//lzz add at 2012-12-05 end
		); 
		
		$replace = array ("",
				"",
				"",
				"",
				"\1",
				"\"",
				"&",
				"<",
				">",
				" ",
				chr(161),
				chr(162),
				chr(163),
				chr(169),
				"chr(\1)",
				
				"“",								
				"”",
				
				"－"	,								//全角破折号
				"…",								//半角省略号
				"×",								//全角星号
				"‘",								//全角单引号（左）
				"’",								//全角单引号（右）
				"·",								//全角middot
				"'"									//半角单引号
				);
		
		try
		{
			$result = trim(preg_replace($search, $replace, $content));
		}
		catch(Exception $e)
		{
			$result = '';
		}
		return $result;
	}
	
	/**
	 * 获取验证码（仅数字）
	 * @param int $len 验证码长度
	 * @return string 验证码
	 */
	public static function getVerificationCode($len=6)
	{
		/*
		$chars = array(
			'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'm', 'n', 'p', 'q', 'r', 's', 't', 'u', 'v',
			'w', 'x', 'y', 'z', '2', '3', '4', '5', '6', '7', '8', '9'
		);
		*/
		$chars = array(	'0', '1', '2', '3', '4', '5', '6', '7', '8', '9' );
	
		$charsLen = count($chars) - 1;
		shuffle($chars);	//将数组打乱
	
		$output = '';
		for( $i=0; $i<$len; $i++ )
		{
			$output .= $chars[mt_rand(0,$charsLen)];
		}
	
		return $output;
	}

	/**
	 * 判断是否为日期格式（YYYY-MM-DD）
	 * @param string $str 验证码
	 * @return bool 验证结果
	 */
	public static function isDate($str, $format='Y-m-d')
	{
	
		$strArr = explode('-',$str);
		if(empty($strArr))
		{
			return false;
		}
		foreach($strArr as $val)
		{
			if(strlen($val)<2)
			{
				$val = '0'.$val;
			}
			$newArr[] = $val;
		}
	
		$str = implode('-',$newArr);
		$unixTime = strtotime($str);
		$checkDate = date($format,$unixTime);
		if($checkDate==$str)
		{
			return true;
		}else
		{
			return false;
		}
	}
	
	/**
	 * 通过生日获得年龄
	 * @param string $birthday 日期（格式：YYYY-MM-DD）
	 * @return int 年龄
	 */
	public static function getAgeByBirthday($birthday)
	{
		if(!self::isDate($birthday))
		{
			return 0;
		}
		
		//当前年
		$curYear = date('Y');
		
		//生日年
		$tempDateArr = explode('-', $birthday);
		$birYear = $tempDateArr['0'];
		
		if($curYear<$birthday)
		{
			return 0;
		}
		
		return $curYear-$birthday;
	}
	/**
	 * 验证form提交
	 * @param string $var post提交验证name
	 * @return string 验证码
	 */
	public static function submitCheck($var)
	{
		if(!empty($_POST[$var]) && $_SERVER['REQUEST_METHOD'] == 'POST')
		{
			if((empty($_SERVER['HTTP_REFERER'])
					|| preg_replace('/https?:\/\/([^\:\/]+).*/i', '\\1', $_SERVER['HTTP_REFERER']) == preg_replace('/([^\:]+).*/', '\\1', $_SERVER['HTTP_HOST'])) )
			{
				return true;
			} else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * 获取分页信息
	 * @param int $total 总数
	 * @param int $curPage 当前页数
	 * @param int $pageSize 每页数量
	 * @return array[] 分页数组
	 */
	public static function getPageInfo($total,$curPage,$pageSize=20)
	{
		$totalPage = ceil($total/$pageSize);
		return array('t'=>$total,'cp'=>$curPage,'tp'=>$totalPage,'ps'=>$pageSize);
	}
	
	/**
	 * 字符串截取
	 * @param string $string 字符串
	 * @param int $length 截取长度
	 * @param string $etc 是否显示省略号（false-不显示）
	 * @return string 截取后的字符串
	 */
	public static function truncate_utf8_string($string, $length, $etc = '...')
	{
		$result = '';
		$string = html_entity_decode(trim(strip_tags($string)), ENT_QUOTES, 'UTF-8');
		$strlen = strlen($string);
		for ($i = 0; (($i < $strlen) && ($length > 0)); $i++)
		{
			if ($number = strpos(str_pad(decbin(ord(substr($string, $i, 1))), 8, '0', STR_PAD_LEFT), '0'))
			{
				if ($length < 1.0)
				{
					break;
				}
				$result .= substr($string, $i, $number);
				$length -= 1.0;
				$i += $number - 1;
			}
			else
			{
				$result .= substr($string, $i, 1);
				$length -= 0.5;
			}
		}
		$result = htmlspecialchars($result, ENT_QUOTES, 'UTF-8');
		if ($i < $strlen)
		{
			$result .= $etc;
		}
		return $result;
	}

	/**
	 * 判断操作系统是否是iphone
	 * @return bool (true 是) (false 不是)
	 */
	public static function isIphone(){
		$system = false;
		if(strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone')||strpos($_SERVER['HTTP_USER_AGENT'], 'iPad')){
			$system = true;
		}
		return $system;
	}

	/*
	 * 获取设备信息
	 * */
	public static function deviceMess(){
		$ua = $_SERVER['HTTP_USER_AGENT'];
		$ua = explode(' ',$ua);
		if(strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone')||strpos($_SERVER['HTTP_USER_AGENT'], 'iPad')){
			return $ua[3];
		}else{
			return $ua[4].$ua[5];
		}
	}

	/*
	 * 获取设备版本信息
	 * */
	public static function versionPhone(){
		$ua = $_SERVER['HTTP_USER_AGENT'];//这里只进行IOS和Android两个操作系统的判断，其他操作系统原理一样
		if (strpos($ua, 'Android') !== false) {//strpos()定位出第一次出现字符串的位置，这里定位为0
			preg_match("/(?<=Android )[\d\.]{1,}/", $ua, $version);
			return array('Android','Android'.$version[0]);
		} elseif (strpos($ua, 'iPhone') !== false) {
			preg_match("/(?<=CPU iPhone OS )[\d\_]{1,}/", $ua, $version);
			return array('iOS','iOS'.str_replace('_', '.', $version[0]));
		} elseif (strpos($ua, 'iPad') !== false) {
			preg_match("/(?<=CPU OS )[\d\_]{1,}/", $ua, $version);
			return array('iOS','iOS'.str_replace('_', '.', $version[0]));
		}else{
			return self::determineplatform($ua);
		}
	}

	function determineplatform($agent) {
		$os ='';
		if (preg_match('/win/i', $agent) && strpos($agent, '95'))
		{
			$os = 'Windows 95';
		}
		else if (preg_match('/win 9x/i', $agent) && strpos($agent, '4.90'))
		{
			$os = 'Windows ME';
		}
		else if (preg_match('/win/i', $agent) && preg_match('/98/i', $agent))
		{
			$os = 'Windows 98';
		}
		else if (preg_match('/win/i', $agent) && preg_match('/nt 6.0/i', $agent))
		{
			$os = 'Windows Vista';
		}
		else if (preg_match('/win/i', $agent) && preg_match('/nt 6.1/i', $agent))
		{
			$os = 'Windows 7';
		}
		else if (preg_match('/win/i', $agent) && preg_match('/nt 6.2/i', $agent))
		{
			$os = 'Windows 8';
		}else if(preg_match('/win/i', $agent) && preg_match('/nt 10.0/i', $agent))
		{
			$os = 'Windows 10';#添加win10判断
		}else if (preg_match('/win/i', $agent) && preg_match('/nt 5.1/i', $agent))
		{
			$os = 'Windows XP';
		}
		else if (preg_match('/win/i', $agent) && preg_match('/nt 5/i', $agent))
		{
			$os = 'Windows 2000';
		}
		else if (preg_match('/win/i', $agent) && preg_match('/nt/i', $agent))
		{
			$os = 'Windows NT';
		}
		else if (preg_match('/win/i', $agent) && preg_match('/32/i', $agent))
		{
			$os = 'Windows 32';
		}
		else if (preg_match('/linux/i', $agent))
		{
			$os = 'Linux';
		}
		else if (preg_match('/unix/i', $agent))
		{
			$os = 'Unix';
		}
		else if (preg_match('/sun/i', $agent) && preg_match('/os/i', $agent))
		{
			$os = 'SunOS';
		}
		else if (preg_match('/ibm/i', $agent) && preg_match('/os/i', $agent))
		{
			$os = 'IBM OS/2';
		}
		else if (preg_match('/Mac/i', $agent) && preg_match('/PC/i', $agent))
		{
			$os = 'Macintosh';
		}
		else if (preg_match('/PowerPC/i', $agent))
		{
			$os = 'PowerPC';
		}
		else if (preg_match('/AIX/i', $agent))
		{
			$os = 'AIX';
		}
		else if (preg_match('/HPUX/i', $agent))
		{
			$os = 'HPUX';
		}
		else if (preg_match('/NetBSD/i', $agent))
		{
			$os = 'NetBSD';
		}
		else if (preg_match('/BSD/i', $agent))
		{
			$os = 'BSD';
		}
		else if (preg_match('/OSF1/i', $agent))
		{
			$os = 'OSF1';
		}
		else if (preg_match('/IRIX/i', $agent))
		{
			$os = 'IRIX';
		}
		else if (preg_match('/FreeBSD/i', $agent))
		{
			$os = 'FreeBSD';
		}
		else if (preg_match('/teleport/i', $agent))
		{
			$os = 'teleport';
		}
		else if (preg_match('/flashget/i', $agent))
		{
			$os = 'flashget';
		}
		else if (preg_match('/webzip/i', $agent))
		{
			$os = 'webzip';
		}
		else if (preg_match('/offline/i', $agent))
		{
			$os = 'offline';
		}
		else
		{
			$os = $agent;
		}
		$os1 = explode(' ',$os);
		$os1[1] = $os;
		return $os1;
	}

	// 判断是否是在微信浏览器里
	function isWeixinBrowser() {
		$agent = $_SERVER ['HTTP_USER_AGENT'];
		if (! strpos ( $agent, "icroMessenger" )) {
			return false;
		}
		return true;
	}

	//判断人数-格式
	function peopleFormat($num_people) {
		if($num_people>=10000 && $num_people<1000000){
			return round($num_people/10000 ,1).'万';
		}elseif($num_people>=1000000 && $num_people<100000000){
			return round($num_people/10000 ).'万';
		}elseif($num_people>=100000000){
			return round($num_people/100000000).'亿';
		}else{
			return $num_people;
		}
	}
//正值表达式比对解析$_SERVER['HTTP_USER_AGENT']中的字符串 获取访问用户的浏览器的信息
	function determinebrowser(){
		$browseragent="";   //浏览器
		$browserversion=""; //浏览器的版本
		$Agent = $_SERVER ['HTTP_USER_AGENT'];
		if (ereg('MSIE ([0-9].[0-9]{1,2})',$Agent,$version)) {
			$browserversion=$version[1];
			$browseragent="Internet Explorer";
		} else if (ereg( 'Opera/([0-9]{1,2}.[0-9]{1,2})',$Agent,$version)) {
			$browserversion=$version[1];
			$browseragent="Opera";
		} else if (ereg( 'Firefox/([0-9.]{1,5})',$Agent,$version)) {
			$browserversion=$version[1];
			$browseragent="Firefox";
		}else if (ereg( 'Chrome/([0-9.]{1,3})',$Agent,$version)) {
			$browserversion=$version[1];
			$browseragent="Chrome";
		}
		else if (ereg( 'Safari/([0-9.]{1,3})',$Agent,$version)) {
			$browseragent="Safari";
			$browserversion="";
		}
		else if( preg_match('/.*?(MicroMessenger\/([0-9.]+))\s*/', $Agent, $version)){
			$browserversion=$version[2];
			$browseragent="MicroMessenger";
		}elseif (stripos($Agent, "Maxthon") > 0) {
			preg_match("/Maxthon\/([\d\.]+)/", $Agent, $aoyou);
			$browseragent = "傲游";
			$browserversion = $aoyou[1];
		}elseif(stripos($Agent, "Edge") > 0) {
			//win10 Edge浏览器 添加了chrome内核标记 在判断Chrome之前匹配
			preg_match("/Edge\/([\d\.]+)/", $Agent, $Edge);
			$browseragent = "Edge";
			$browserversion = $Edge[1];
		} else{
			$browserversion="";
			$browseragent="Unknown";
		}
		return $browseragent." ".$browserversion;
	}

	//用户设定的操作系统的语言
	function language(){
		$language = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
		return explode(',',explode(';',$language)[0])[0];
	}

}