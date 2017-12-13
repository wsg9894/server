<?php
/**  
* 常用工具类  
* author Lee.  
* Last modify $Date: 2012-8-23
*/
class CoreTool {
    
    
        static public function Location($_url) {
		echo "<script type='text/javascript'>location.href='$_url';</script>";
		exit();
	}
   
	/**
	 * js 弹窗并且跳转
	 * @param string $_info
	 * @param string $_url
	 * @return js
	 */
	static public function alertLocation($_info, $_url, $iscg = false) {
		$fronttanchutext = "fronttanchutext('$_info','$iscg','location','$_url')";
		echo "<script type='text/javascript'>
					if((typeof fronttanchutext == 'function')){
						if($('#tanchu').size() <= 0) fronttancu();
						".$fronttanchutext.";
					}else{
						alert('$_info');
						location.href='$_url';
					}
				</script>";
		exit();
	}

	/**
	 * 直接跳转页面，不弹框
	 * @param $_url
	 */
	static public function skipLocation($_url) {
		echo "<script type='text/javascript'>location.href='$_url';</script>";
		exit();
	}
        /**
	 * js 弹窗并且跳转
	 * @param string $_info
	 * @param string $_url
	 * @return js
	 */
	static public function alertLogin($_info, $iscg = false) {
		$_loginurl='/usercenter/login.html?go='.'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
		$fronttanchutext = "fronttanchutext('$_info','$iscg','location','$_loginurl')";
		echo "<script type='text/javascript'>
					if((typeof fronttanchutext == 'function')){
						if($('#tanchu').size() <= 0) fronttancu();
						".$fronttanchutext.";
					}else{
						alert('$_info');
						location.href='$_loginurl'
					}
				</script>";
		exit();
	}
	
	/**
	 * js 弹窗返回
	 * @param string $_info
	 * @return js
	 */
	static public function alertBack($_info,$iscg = false) {
		$fronttanchutext = "fronttanchutext('$_info','$iscg','location','back')";
		echo "<script type='text/javascript'>
					if((typeof fronttanchutext == 'function')){
						if($('#tanchu').size() <= 0) fronttancu();
						".$fronttanchutext.";
					}else{
						alert('$_info');
						history.back();
					}
				</script>";
		exit();
	}
	
	/**
	 * 页面跳转
	 * @param string $url
	 * @return js
	 */
	static public function headerUrl($url) {
		echo "<script type='text/javascript'>location.href='{$url}';</script>";
		exit();
	}
	
	/**
	 * 弹窗关闭
	 * @param string $_info
	 * @return js
	 */
	static public function alertClose($_info,$iscg = false) {
		$fronttanchutext = "fronttanchutext('$_info','$iscg','close','window')";
		echo "<script type='text/javascript'>
					if((typeof fronttanchutext == 'function')){
						if($('#tanchu').size() <= 0) fronttancu();
						".$fronttanchutext.";
					}else{
						alert('$_info');
						window.top.opener = null;
						 window.close();
					}
				</script>";
		exit();
	}
	
	/**
	 * 弹窗
	 * @param string $_info
	 * @return js
	 */
	static public function alert($_info,$iscg = false) {
		$fronttanchutext = "fronttanchutext('$_info','$iscg','hide')";
		echo "<script type='text/javascript'>
					if((typeof fronttanchutext == 'function')){
						if($('#tanchu').size() <= 0) fronttancu();
						".$fronttanchutext.";
					}else{
						alert('$_info');
					}
				</script>";
		exit();
	}
	
	/**
	 * 系统基本参数上传图片专用
	 * @param string $_path
	 * @return null
	 */
	static public function sysUploadImg($_path) {
		echo '<script type="text/javascript">document.getElementById("logo").value="'.$_path.'";</script>';
		echo '<script type="text/javascript">document.getElementById("pic").src="'.$_path.'";</script>';
		echo '<script type="text/javascript">$("#loginpop1").hide();</script>';
		echo '<script type="text/javascript">$("#bgloginpop2").hide();</script>';
	}
	
	/**
	 * html过滤
	 * @param array|object $_date
	 * @return string
	 */
	static public function htmlString($_date) {
		if (is_array($_date)) {
			foreach ($_date as $_key=>$_value) {
				$_string[$_key] = Tool::htmlString($_value);  //递归
			}
		} elseif (is_object($_date)) {
			foreach ($_date as $_key=>$_value) {
				$_string->$_key = Tool::htmlString($_value);  //递归
			}
		} else {
			$_string = htmlspecialchars($_date);
		}
		return $_string;
	}
	
	/**
	 * 数据库输入过滤
	 * @param string $_data
	 * @return string
	 */
	static public function mysqlString($_data) {
		$_data = trim($_data);
		return !GPC ? addcslashes($_data) : $_data;
	}
	
	/**
	 * 清理session
	 */
	static public function unSession() {
		if (session_start()) {
			session_destroy();
		}
	}
	
	/**
	 * 验证是否为空
	 * @param string $str
	 * @param string $name
	 * @return bool (true or false)
	 */
	static function validateEmpty($str, $name) {
		if (empty($str)) {
			self::alertBack('警告：' .$name . '不能为空！');
		}
	}
	
	/**
	 * 验证是否相同
	 * @param string $str1
	 * @param string $str2
	 * @param string $alert
	 * @return JS 
	 */
	static function validateAll($str1, $str2, $alert) {
		if ($str1 != $str2) self::alertBack('警告：' .$alert);
	}
	
	/**
	 * 验证ID
	 * @param Number $id
	 * @return JS
	 */
	static function validateId($id) {
		if (empty($id) || !is_numeric($id)) self::alertBack('警告：参数错误！');
	}
	
	/**
	 * 格式化字符串
	 * @param string $str
	 * @return string
	 */
	static public function formatStr($str) {
		$arr = array(' ', '	', '&', '@', '#', '%',  '\'', '"', '\\', '/', '.', ',', '$', '^', '*', '(', ')', '[', ']', '{', '}', '|', '~', '`', '?', '!', ';', ':', '-', '_', '+', '=');
		foreach ($arr as $v) {
			$str = str_replace($v, '', $str);
		}
		return $str;
	}
	
         /**
	 * js 弹窗并且跳转
	 * @param string $_info
	 * @param string $_url
	 * @return js
	 */
	static public function getChineseWeek($time) {        
            $weekarray=array("星期日","星期一","星期二","星期三","星期四","星期五","星期六");           
	    return $weekarray[date('w', strtotime($time))];	    
	}
           /**
	 * js 弹窗并且跳转
	 * @param string $_info
	 * @param string $_url
	 * @return js
	 */
	static public function getChineseDay($time="default") {
                $str_today = date('Y-m-d'); //获取今天的日期 字符串 
                $ux_today =  strtotime($str_today); //将今天的日期字符串转换为 时间戳

                $ux_tomorrow = $ux_today+3600*24;// 获取明天的时间戳
                $str_tomorrow = date('Y-m-d',$ux_tomorrow);//获取明天的日期 字符串


                $ux_afftertomorrow = $ux_today+3600*24*2;// 获取后天的时间戳
                $str_afftertomorrow = date('Y-m-d',$ux_afftertomorrow);//获取后天的日期 字符串

                $ux_in = strtotime($time);//获取输入日期的 时间戳
                $str_in_format = date('Y-m-d',$ux_in);//格式化为y-m-d的 日期字符串
               
           
                if($str_in_format==$str_today){
                   return "今天"; 
                }else if($str_in_format==$str_tomorrow){
                   return "明天"; 
                }else if($str_in_format==$str_afftertomorrow){
                   return "后天"; 
                }else{
             return   $str_in_format;
                }
	}
	/**
	 * 格式化时间
	 * @param int $time 时间戳
	 * @return string
	 */
	static public function formatDate($time='default') {
		$date = $time == 'default' ? date('Y-m-d H:i:s', time()) : date('Y-m-d H:i:s', $time);
		return $date;
	}
	
	/**  
	* 获得真实IP地址  
	* @return string  
	*/
	static public function realIp() {   
	    static $realip = NULL;   
	    if ($realip !== NULL) return $realip;  
	    if (isset($_SERVER)) {  
	        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {   
	            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);  
	            foreach ($arr AS $ip) {  
	                $ip = trim($ip);  
	                if ($ip != 'unknown') {   
	                    $realip = $ip;   
	                    break;   
	                }   
	            }   
	        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {   
	            $realip = $_SERVER['HTTP_CLIENT_IP'];  
	        } else {   
	            if (isset($_SERVER['REMOTE_ADDR'])) {   
	                $realip = $_SERVER['REMOTE_ADDR'];   
	            } else {   
	                $realip = '0.0.0.0';   
	            }  
	        }  
	    } else {  
	        if (getenv('HTTP_X_FORWARDED_FOR')) {  
	            $realip = getenv('HTTP_X_FORWARDED_FOR');  
	        } elseif (getenv('HTTP_CLIENT_IP')) {  
	            $realip = getenv('HTTP_CLIENT_IP');  
	        } else {  
	            $realip = getenv('REMOTE_ADDR');  
	        }  
	    }
	    preg_match('/[\d\.]{7,15}/', $realip, $onlineip);  
	    $realip = !empty($onlineip[0]) ? $onlineip[0] : '0.0.0.0';  
	    return $realip;  
	}
	
	/**
	 * 加载 Smarty 模板
	 * @param string $html
	 * @return null;
	 */
	static public function display() {
		global $tpl;$html = null;
		$htmlArr = explode('/', $_SERVER[SCRIPT_NAME]);
		$html = str_ireplace('.php', '.html', $htmlArr[count($htmlArr)-1]);
		$dir = dirname($_SERVER[SCRIPT_NAME]);
		$firstStr = substr($dir, 0, 1);
		$endStr = substr($dir, strlen($dir)-1, 1);
		if ($firstStr == '/' || $firstStr == '\\') $dir = substr($dir, 1);
		if ($endStr != '/' || $endStr != '\\') $dir = $dir . '/';
		$tpl->display($dir.$html);
	}
	
	/**
	 * 创建目录
	 * @param string $dir
	 */
	static public function createDir($dir) {
		if (!is_dir($dir)) {
			mkdir($dir, 0777,true);
		}
	}
	
	/**
	 * 创建文件（默认为空）
	 * @param unknown_type $filename
	 */
	static public function createFile($filename) {
		if (!is_file($filename)) touch($filename);
	}
	
	/**
	 * 正确获取变量
	 * @param string $param
	 * @param string $type
	 * @return string
	 */
	static public function getData($param, $type='post') {
		$type = strtolower($type);
		if ($type=='post') {
			return Tool::mysqlString(trim($_POST[$param]));
		} elseif ($type=='get') {
			return Tool::mysqlString(trim($_GET[$param]));
		}
	}
	
	/**
	 * 删除文件
	 * @param string $filename
	 */
	static public function delFile($filename) {
		if (file_exists($filename)) unlink($filename);
	}
	
	/**
	 * 删除目录
	 * @param string $path
	 */
	static public function delDir($path) {
		if (is_dir($path)) rmdir($path);
	}
	
	/**
	 * 删除目录及地下的全部文件
	 * @param string $dir
	 * @return bool
	 */
	static public function delDirOfAll($dir) {
		//先删除目录下的文件：
		if (is_dir($dir)) {
			$dh=opendir($dir);
			while (!!$file=readdir($dh)) {
				if($file!="." && $file!="..") {
					$fullpath=$dir."/".$file;
					if(!is_dir($fullpath)) {
						unlink($fullpath);
					} else {
						self::delDirOfAll($fullpath);
					}
				}
			}
			closedir($dh);
			//删除当前文件夹：
			if(rmdir($dir)) {
		    	return true;
			} else {
				return false;
			}
		}
	}

	/**
	 * 验证登陆
	 */
	static public function validateLogin() {
                //地址需要修改
		if (empty($_SESSION['iUserID'])) header('Location:/longin/');
	}
	
	/**
	 * 给已经存在的图片添加水印
	 * @param string $file_path
	 * @return bool
	 */
	static public function addMark($file_path) {
		if (file_exists($file_path) && file_exists(MARK)) {
			//求出上传图片的名称后缀
			$ext_name = strtolower(substr($file_path, strrpos($file_path, '.'), strlen($file_path)));
			//$new_name='jzy_' . time() . rand(1000,9999) . $ext_name ;
			$store_path = ROOT_PATH . UPDIR;
			//求上传图片高宽
			$imginfo = getimagesize($file_path);
			$width = $imginfo[0];
			$height = $imginfo[1];
			 //添加图片水印             
			switch($ext_name) {
				case '.gif':
					$dst_im = imagecreatefromgif($file_path);
					break;
				case '.jpg':
					$dst_im = imagecreatefromjpeg($file_path);
					break;
				case '.png':
					$dst_im = imagecreatefrompng($file_path);
					break;
			}
			$src_im = imagecreatefrompng(MARK);
			//求水印图片高宽
			$src_imginfo = getimagesize(MARK);
			$src_width = $src_imginfo[0];
			$src_height = $src_imginfo[1];
			//求出水印图片的实际生成位置
			$src_x = $width - $src_width - 10;
			$src_y = $height - $src_height - 10;
			//新建一个真彩色图像
			$nimage = imagecreatetruecolor($width, $height);               
			//拷贝上传图片到真彩图像
			imagecopy($nimage, $dst_im, 0, 0, 0, 0, $width, $height);          
			//按坐标位置拷贝水印图片到真彩图像上
			imagecopy($nimage, $src_im, $src_x, $src_y, 0, 0, $src_width, $src_height);
			//分情况输出生成后的水印图片
			switch($ext_name) {
				case '.gif':
					imagegif($nimage, $file_path);
					break;
				case '.jpg':
					imagejpeg($nimage, $file_path);
					break;
				case '.png':
					imagepng($nimage, $file_path);
					break;     
			}
			//释放资源 
			imagedestroy($dst_im);
			imagedestroy($src_im);
			unset($imginfo);
			unset($src_imginfo);
			//移动生成后的图片
			@move_uploaded_file($file_path, ROOT_PATH.UPDIR . $file_path);
		}
	}
	
	/**
	*  中文截取2，单字节截取模式
	* @access public
	* @param string $str  需要截取的字符串
	* @param int $slen  截取的长度
	* @param int $startdd  开始标记处
	* @return string
	*/
	static public function cn_substr($str, $slen, $startdd=0){
		$cfg_soft_lang = PAGECHARSET;
		if($cfg_soft_lang=='utf-8') {
			return self::cn_substr_utf8($str, $slen, $startdd);
		}
		$restr = '';
		$c = '';
		$str_len = strlen($str);
		if($str_len < $startdd+1) {
			return '';
		}
		if($str_len < $startdd + $slen || $slen==0) {
			$slen = $str_len - $startdd;
		}
		$enddd = $startdd + $slen - 1;
		for($i=0;$i<$str_len;$i++) {
			if($startdd==0) {
				$restr .= $c;
			} elseif($i > $startdd) {
				$restr .= $c;
			}
			if(ord($str[$i])>0x80) {
				if($str_len>$i+1) {
					$c = $str[$i].$str[$i+1];
				}
				$i++;
			} else {
				$c = $str[$i];
			}
			if($i >= $enddd) {
				if(strlen($restr)+strlen($c)>$slen) {
					break;
				} else {
					$restr .= $c;
					break;
				}
			}
		}
		return $restr;
	}

	/**
	*  utf-8中文截取，单字节截取模式
	*
	* @access public
	* @param string $str 需要截取的字符串
	* @param int $slen 截取的长度
	* @param int $startdd 开始标记处
	* @return string
	*/
	static public function cn_substr_utf8($str, $length, $start=0) {
		if(strlen($str) < $start+1) {
			return '';
		}
		preg_match_all("/./su", $str, $ar);
		$str = '';
		$tstr = '';
		//为了兼容mysql4.1以下版本,与数据库varchar一致,这里使用按字节截取
		for($i=0; isset($ar[0][$i]); $i++) {
			if(strlen($tstr) < $start) {
				$tstr .= $ar[0][$i];
			} else {
				if(strlen($str) < $length + strlen($ar[0][$i]) ) {
					$str .= $ar[0][$i];
				} else {
					break;
				}
			}
		}
		return $str;
	}
	
	/**
	 * 删除图片，根据图片ID
	 * @param int $image_id
	 */
	static function delPicByImageId($image_id) {
		$db_name = PREFIX . 'images i';
		$m = new Model();
		$data = $m->getOne($db_name, "i.id={$image_id}", "i.path as p, i.big_img as b, i.small_img as s");
		foreach ($data as $v) {
			@self::delFile(ROOT_PATH . $v['p']);
			@self::delFile(ROOT_PATH . $v['b']);
			@self::delFile(ROOT_PATH . $v['s']);
		}
		$m->del(PREFIX . 'images', "id={$image_id}");
		unset($m);
	}
	
	/**
	 * 图片等比例缩放
	 * @param resource $im    新建图片资源(imagecreatefromjpeg/imagecreatefrompng/imagecreatefromgif)
	 * @param int $maxwidth   生成图像宽
	 * @param int $maxheight  生成图像高
	 * @param string $name    生成图像名称
	 * @param string $filetype文件类型(.jpg/.gif/.png)
	 */
	static public function resizeImage($im, $maxwidth, $maxheight, $name, $filetype) {
		$pic_width = imagesx($im);
		$pic_height = imagesy($im);
		if(($maxwidth && $pic_width > $maxwidth) || ($maxheight && $pic_height > $maxheight)) {
			if($maxwidth && $pic_width>$maxwidth) {
				$widthratio = $maxwidth/$pic_width;
				$resizewidth_tag = true;
			}
			if($maxheight && $pic_height>$maxheight) {
				$heightratio = $maxheight/$pic_height;
				$resizeheight_tag = true;
			}
			if($resizewidth_tag && $resizeheight_tag) {
				if($widthratio<$heightratio)
					$ratio = $widthratio;
				else
					$ratio = $heightratio;
			}
			if($resizewidth_tag && !$resizeheight_tag)
				$ratio = $widthratio;
			if($resizeheight_tag && !$resizewidth_tag)
				$ratio = $heightratio;
			$newwidth = $pic_width * $ratio;
			$newheight = $pic_height * $ratio;
			if(function_exists("imagecopyresampled")) {
				$newim = imagecreatetruecolor($newwidth,$newheight);
				imagecopyresampled($newim,$im,0,0,0,0,$newwidth,$newheight,$pic_width,$pic_height);
			} else {
				$newim = imagecreate($newwidth,$newheight);
				imagecopyresized($newim,$im,0,0,0,0,$newwidth,$newheight,$pic_width,$pic_height);
			}
			$name = $name.$filetype;
			imagejpeg($newim,$name);
			imagedestroy($newim);
		} else {
			$name = $name.$filetype;
			imagejpeg($im,$name);
		}
	}
        
         public static function assoc_unique($arr, $key)
             {
               $tmp_arr = array();
               foreach($arr as $k => $v)
              {
                 if(in_array($v[$key], $tmp_arr))//搜索$v[$key]是否在$tmp_arr数组中存在，若存在返回true
                {
                   unset($arr[$k]);
                }
              else {
                  $tmp_arr[] = $v[$key];
                }
              }
            sort($arr); //sort函数对数组进行排序
            return $arr;
            }

	/**
	 * 下载文件
	 * @param string $file_path 绝对路径
	 */
	static public function downFile($file_path) {
		//判断文件是否存在
		$file_path = iconv('utf-8', 'gb2312', $file_path); //对可能出现的中文名称进行转码
		if (!file_exists($file_path)) {
			exit('文件不存在！');
		}
		$file_name = basename($file_path); //获取文件名称
		$file_size = filesize($file_path); //获取文件大小
		$fp = fopen($file_path, 'r'); //以只读的方式打开文件
		header("Content-type: application/octet-stream");
		header("Accept-Ranges: bytes");
		header("Accept-Length: {$file_size}");
		header("Content-Disposition: attachment;filename={$file_name}");
		$buffer = 1024;
		$file_count = 0;
		//判断文件是否结束
		while (!feof($fp) && ($file_size-$file_count>0)) {
			$file_data = fread($fp, $buffer);
			$file_count += $buffer;
			echo $file_data;
		}
		fclose($fp); //关闭文件
	}

	/**
	 * 将64进制的数字字符串转为10进制的数字字符串
	 * @param $m string 64进制的数字字符串
	 * @param $len integer 返回字符串长度，如果长度不够用0填充，0为不填充
	 * @return string
	 *
	 */
	static public function hex64to10($m, $len = 0) {
		$m = (string)$m;
		$hex2 = '';
		$Code = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_$';
		for($i = 0, $l = strlen($Code); $i < $l; $i++) {
			$KeyCode[] = $Code[$i];
		}
		$KeyCode = array_flip($KeyCode);

		for($i = 0, $l = strlen($m); $i < $l; $i++) {
			$one = $m[$i];
			$hex2 .= str_pad(decbin($KeyCode[$one]), 6, '0', STR_PAD_LEFT);
		}
		$return = bindec($hex2);

		if($len) {
			$clen = strlen($return);
			if($clen >= $len) {
				return $return;
			}
			else {
				return str_pad($return, $len, '0', STR_PAD_LEFT);
			}
		}
		return $return;
	}

	/**
	 * 将10进制的数字字符串转为64进制的数字字符串
	 * @param $m string 10进制的数字字符串
	 * @param $len integer 返回字符串长度，如果长度不够用0填充，0为不填充
	 * @return string
	 *
	 */
	static public function hex10to64($m, $len = 0) {
		$KeyCode = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_$';
		$hex2 = decbin($m);
		$hex2 = self::str_rsplit($hex2, 6);
		$hex64 = array();
		foreach($hex2 as $one) {
			$t = bindec($one);
			$hex64[] = $KeyCode[$t];
		}
		$return = preg_replace('/^0*/', '', implode('', $hex64));
		if($len) {
			$clen = strlen($return);
			if($clen >= $len) {
				return $return;
			}
			else {
				return str_pad($return, $len, '0', STR_PAD_LEFT);
			}
		}
		return $return;
	}
	/**
	 * 功能和PHP原生函数str_split接近，只是从尾部开始计数切割
	 * @param $str string 需要切割的字符串
	 * @param $len integer 每段字符串的长度
	 * @return array
	 *
	 */
	static function str_rsplit($str, $len = 1) {
		if($str == null || $str == false || $str == '') return false;
		$strlen = strlen($str);
		if($strlen <= $len) return array($str);
		$headlen = $strlen % $len;
		if($headlen == 0) {
			return str_split($str, $len);
		}
		$return = array(substr($str, 0, $headlen));
		return array_merge($return, str_split(substr($str, $headlen), $len));
	}
}

?>

