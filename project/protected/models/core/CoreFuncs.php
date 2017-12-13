<?php
// To be used after ajaxinit as a shortcut
function ajaxExit($arResponse){
    echo json_encode($arResponse);
    exit();
}


function Sub_textarea($strname,$strTag,$strValue)
{
    echo "<textarea name='{$strname}' style='width:510px;' rows='5' id='{$strname}' tag = '{$strTag}'>{$strValue}</textarea>";
 ?>
    <a href="javascript:admin_Size(-5,'<?php echo $strname ?>')"><img src="/admin/skins/images/minus.gif" unselectable="on" border='0' title="减少编辑区域行数"></a> <a href="javascript:admin_Size(5,'<?php echo $strname ?>')"><img src="/admin/skins/images/plus.gif" unselectable="on" border='0' title="增加编辑区域行数"></a>
<?php 
  }


function CheckStr($str)
{
    if (empty($str))
    {
        return '';
    }
    return $str;
}

/////////error and log functions////////////////////
function customError($msg, $errorlvl, $showerror, $linenum, $vars = NULL ) {
	
    Model_Log_Server::addLog($msg, $linenum);
}

function addAdminLog()
{
    $remote_addr = isset($_SERVER['REMOTE_ADDR'])?  $_SERVER['REMOTE_ADDR'] :  '127.0.0.1';
    $script_name = isset($_SERVER['SCRIPT_NAME'])? $_SERVER['SCRIPT_NAME'] : '/admin/login.php';
    
    if(!isset($_SESSION)){
              session_start();
          };
    $adminUser = '';
    $userName = '';
    if (!empty($_SESSION["adminUser"]))
    {
        $adminUser = $_SESSION["adminUser"];
    }
    if (!empty($_SESSION['userName']))
    {
        $userName = $_SESSION['userName'];
    }
    $detail = Model_Util::encodeContent($_REQUEST);
    Model_Log_Server::addAdminLog($adminUser, $userName, $script_name, $remote_addr, $detail);
}



function CalculBaseCouponNum($arrange)
{
    $resultNum = 0;
    $arrangePrice = $arrange[BookSeatingArrange::arrangePrice];
    if ($arrangePrice[EPWArrangePrice::price] > 24.0)
    {
        if ($arrange[BookSeatingArrange::dimensional] == "3D")
        {
            $resultNum = round($arrangePrice[EPWArrangePrice::price] / 10.0, 0) + 1;
        }
        else
        {
            $resultNum = round($arrangePrice[EPWArrangePrice::price] / 10.0, 0);
        }
    }
    else
    {
        $resultNum = $arrange[BookSeatingArrange::dimensional] == "3D" ? 3 : 2;
    }
    return $resultNum;
}

//生成随机数
function randomkeys($length,$type)
{
    $pattern='1234567890ABCDEFGHIJKLMNPQRSTUVWXYZ';
    if($type=="n")
    {
        $bnum=0;
        $endnum=9;
    }
    if($type=="u")
    {
        $bnum=10;
        $endnum=34;
    }
    if($type=="m")
    {
        $bnum=0;
        $endnum=34;
    }
    $key="";
    for($i=0;$i<$length;$i++)
    {
      $key .= $pattern{mt_rand($bnum,$endnum)};    //生成php随机数
    }
    return $key;
}

//卡号的长度，密码的长度 生成一张新卡插入数据库
function createCoupon($clen,$plen)
{
    $ok=true;
    while($ok){
        $sCheckNo=randomkeys($clen,"n");
        $sPassword= randomkeys($plen, "n");       
        $CouponInfo=array("sCheckNo"=>$sCheckNo,"sPassWord"=>$sPassword);
        $arCouponInfo = DB_Coupon_Info::getCouponInfoByCheckNo($sCheckNo);
        if (empty($arCouponInfo))
        {
            $rel= DB_Coupon_Info::insertCouponInfo($CouponInfo);
            $ok = false;
        }
        else
        {
            $ok = true;
        }
    }
    return $CouponInfo;
    //创建一个卡号 密码 参数传入卡号 和密码的长度   
}

 //这个创建订单号不用验证数据库 全球唯一码本机绝对不会重复
function createOuterOrderId()
{ //创建外部订单号0
$charid = strtoupper(substr(md5(uniqid(mt_rand(), true)),8,24)); 
$hyphen = '';   //chr(45);
$uuid = substr($charid, 0, 8).substr($charid, 8, 4).$hyphen.substr($charid,12, 4).$hyphen.substr($charid,16, 4).$hyphen.substr($charid,20,12);
return "DD-".date("md")."-".$uuid;
}

 //这个创建订单号不用验证数据库 全球唯一码本机绝对不会重复
function createepiaoBankNo()
{ //创建外部订单号0
$charid = strtoupper(substr(md5(uniqid(mt_rand(), true)),8,24)); 
$hyphen = '';   //chr(45);// "-" 
$uuid = substr($charid, 0, 8).$hyphen.substr($charid, 8, 4).$hyphen.substr($charid,12, 4).$hyphen.substr($charid,16, 4).$hyphen.substr($charid,20,11);
return "TT-".date("md")."-".$uuid;
}
//
//
function createFile($file_name,$msgdata)
{
    if(!is_dir(self::$LOC_ADHOC))
        {
            
            mkdir(self::$LOC_ADHOC, 0777);
        }        
        
        if (!is_writable(self::$LOC_ADHOC)) {
            return;
        }
        $location = self::$LOC_ADHOC .$file_name;
        //$location = self::$LOC_ADHOC . date('Y-M-d-H') . ".log";
        error_log ($msgdata, 3, $location);
}


function getUserSession($key)
{
    $rel=$_SESSION[$key];
    if(empty($rel))
    {
       alertLocation("登录超时，请重新登录","/login.php");       
    }
    return $rel;
}

if (!function_exists('array_column')) {
    function array_column($input, $column_key, $index_key = null) {
        $arr = array_map(function($d) use ($column_key, $index_key) {
            if (!isset($d[$column_key])) {
                return null;
            }
            if ($index_key !== null) {
                return array($d[$index_key] => $d[$column_key]);
            }
            return $d[$column_key];
        }, $input);

        if ($index_key !== null) {
            $tmp = array();
            foreach ($arr as $ar) {
                $tmp[key($ar)] = current($ar);
            }
            $arr = $tmp;
        }
        return $arr;
    }
}
// php获取当前访问的完整url地址
function GetCurUrl() {
    $url = 'http://';
    if (isset ( $_SERVER ['HTTPS'] ) && $_SERVER ['HTTPS'] == 'on') {
        $url = 'https://';
    }
    if ($_SERVER ['SERVER_PORT'] != '80') {
        $url .= $_SERVER ['HTTP_HOST'] . ':' . $_SERVER ['SERVER_PORT'] . $_SERVER ['REQUEST_URI'];
    } else {
        $url .= $_SERVER ['HTTP_HOST'] . $_SERVER ['REQUEST_URI'];
    }
    return $url;
}
// 判断是否是在微信浏览器里
function isWeixinBrowser() {
    $agent = $_SERVER ['HTTP_USER_AGENT'];
    if (! strpos ( $agent, "icroMessenger" )) {
        return false;
    }
    return true;
}
?>
    
