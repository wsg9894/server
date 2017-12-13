<?php

/**
 * LBS - 位置服务类
 * @author luzhizhong
 * @version V1.0
 */

class LBS
{
	function __construct(){}
	function __destruct(){}


    CONST EARTH_RADIUS = 6378137; //m    
	CONST IPI = 0.0174532925199433333333; //3.1415926535898/180.0
	CONST GOOGLE_MAP_INTER = 'http://ditu.google.cn/maps/geo?q=%s,%s&output=json'; //Google API
    CONST GOOGLE_MAP_GEOCODE = 'http://maps.google.com/maps/geo?key=sl_shanlink&q=%s&output=json';
    CONST GOOGLE_MAP_GEOCODE_API = 'http://maps.googleapis.com/maps/api/geocode/json?address=%s&language=zh_CN&sensor=false';
    CONST GD_GEOCODE_API = 'http://api.amap.com:9090/geocode/simple?resType=json&encode=utf-8&range=300&roadnum=3&crossnum=2&poinum=2&retvalue=1&key=undefined&sid=7000&address=%s&rid=1';
    /*
     * 注：经测试，
     * GOOGLE_MAP_GEOCODE 精度在100m以内，没有请求限制，但是对输入条件要求较严格，大部分“地址”无法解析
     * GOOGLE_MAP_GEOCODE_API 精度在100m以内，但是有请求限制：2500次/IP
     * GD_GEOCODE_API 精度在200m左右，没有请求限制
     */
    
    private static function getRadian($d)
    {    
        return $d * M_PI / 180;
    }    

    /**
     * 获取经纬度两点间的距离（m）
     *
     * @param string $lat1：纬度1
     * @param string $lng1：经度1
     * @param string $lng2：纬度2
     * @param string $lat2：经度2
     * @return int 二者距离（m）
     */
    public static function getDistance($lat1, $lng1, $lat2, $lng2){
    	$earthRadius = 6367000; //approximate radius of earth in meters
    	$lat1 = ($lat1 * pi() ) / 180;
    	$lng1 = ($lng1 * pi() ) / 180;
    	$lat2 = ($lat2 * pi() ) / 180;
    	$lng2 = ($lng2 * pi() ) / 180;
    	$calcLongitude = $lng2 - $lng1;
    	$calcLatitude = $lat2 - $lat1;
    	$stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);
    	$stepTwo = 2 * asin(min(1, sqrt($stepOne)));
    	$calculatedDistance = $earthRadius * $stepTwo;
    	return round($calculatedDistance);
    }
    
	/**
	 * 获取经纬度两点间的距离（m，已不用）,lzz update at 2017-03-07
	 * 
	 * @param string $lat1：纬度1
	 * @param string $lng1：经度1
	 * @param string $lng2：纬度2
	 * @param string $lat2：经度2
	 * @return int 二者距离（m）
	 */
    public static function getDistince_bak ($lat1, $lng1, $lat2, $lng2)
    {
        $lat1 = self::getRadian($lat1);
        $lat2 = self::getRadian($lat2);
            
        $a = $lat1 - $lat2;
        $b = self::getRadian($lng1) - self::getRadian($lng2);
            
        $v = 2 * asin(sqrt(pow(sin($a/2),2) + cos($lat1) * cos($lat2) * pow(sin($b/2),2)));    
            
        $v = round(self::EARTH_RADIUS * $v * 10000) / 10000;

        return $v;
    }    


	/**
	 * PHP根据经纬度反向解析地址(By Google Map API)
	 * 
	 * @param string $lat：纬度
	 * @param string $lng：经度
	 * @param int $level
	 * @return string 地址信息
	 */
	public static function get_address_by_location($lat,$lng,$level=0){

		$google_api_inter = sprintf(self::GOOGLE_MAP_INTER,$lat,$lng);
		$res = json_decode(file_get_contents($google_api_inter));

		if($res->Status->code==200)
		{
			$res_address = $res->Placemark;
			return $res_address[$level]->address;
		}else
		{
			return 0;
		}
	}

	/**
	 * 根据经纬度数据获取周边的地址信息列表（东、南、西、北、中共5个点）
	 *
	 * @param string $latitude 纬度
	 * @param string $longitude 经度
	 * @param int $distance 距离
	 * @return array[][] 周边地址信息列表 
	 */
	public static function get_location_nearby($latitude,$longitude,$distance=500)
	{
		//地址信息－基本点
		$locInfo = self::getLocationInfo($latitude,$longitude);
		$retu['base'] = array('latitude'=>$latitude,'longitude'=>$longitude,'locInfo'=>$locInfo);
		
		//地址信息－东
		$longitude_east = self::getNearbyPoint($longitude,$distance);
		$locInfo = self::getLocationInfo($latitude,$longitude_east);
		$retu['east'] = array('latitude'=>$latitude,'longitude'=>$longitude_east,'locInfo'=>$locInfo);
		
		//地址信息－西
		$longitude_west = self::getNearbyPoint($longitude,-$distance);
		$locInfo = self::getLocationInfo($latitude,$longitude_west);
		$retu['west'] = array('latitude'=>$latitude,'longitude'=>$longitude_west,'locInfo'=>$locInfo);
		
		//地址信息－南
		$latitude_south = self::getNearbyPoint($latitude,-$distance);
		$locInfo = self::getLocationInfo($latitude_south,$longitude);
		$retu['south'] = array('latitude'=>$latitude_south,'longitude'=>$longitude,'locInfo'=>$locInfo);
		
		//地址信息－北
		$latitude_north = self::getNearbyPoint($latitude,$distance);
		$locInfo = self::getLocationInfo($latitude_north,$longitude);
		$retu['north'] = array('latitude'=>$latitude_north,'longitude'=>$longitude,'locInfo'=>$locInfo);
		
		return $retu;
	}
	/**
	 * PHP根据经纬度反向解析详细地址信息(By Google Map API)
	 * 返回：详细地址信息数组【全地址、行政地区、区/县、街道/公路、邮编】
	 * 
	 * @param string $lat：纬度
	 * @param string $lng：经度
	 */
	public static function getLocationInfo($lat,$lng){
	
		$locInfo = array();

		$google_api_inter = sprintf(self::GOOGLE_MAP_INTER,$lat,$lng);
		$res = json_decode(file_get_contents($google_api_inter));

		if($res->Status->code==200){
			
			$res_address = $res->Placemark[0];
			
			$address = $res_address->address;		//全地址 ＋ 邮编
			
			if( ($pos = stripos($address, '邮政编码'))!==false )
			{
				$locInfo['address'] = trim(substr($address, 0, $pos));		//全地址

				$tempStr = substr($address, $pos);
				$tempArr = explode(':',$tempStr);			
				$locInfo['postcode'] = trim($tempArr[1]);					//邮编

			}else{
				
				$locInfo['address'] = $address;								//全地址
				$locInfo['postcode'] = '';									//邮编
			}

			$country = $res_address->AddressDetails->Country->CountryName;		//国家
			$locInfo['country'] = $country;	
			
			$administrativearea = $res_address->AddressDetails->Country->AdministrativeArea->AdministrativeAreaName;//行政地区
			$locInfo['administrativearea'] = $administrativearea;
			
			$dependentlocality = $res_address->AddressDetails->Country->AdministrativeArea->Locality->DependentLocality->DependentLocalityName;		//区县
			$locInfo['dependentlocality'] = $dependentlocality;

			$thoroughfare = $res_address->AddressDetails->Country->AdministrativeArea->Locality->DependentLocality->Thoroughfare->ThoroughfareName;		//街道
			$locInfo['thoroughfare'] = $thoroughfare;
		}

		return $locInfo;
	}
	
	/**
	 * 根据地址信息解析经纬度（google）
	 *
	 * @param string $address：地址信息
	 * @return array：一维数组（经度、纬度）
	 */
	public static function getCoordinateByGoogleMap($address)
	{
		if(empty($address))
		{
			return array();
		}
		
		$coordinateArr = array();
		$address = urlencode($address);
		$google_api_geocode = sprintf(self::GOOGLE_MAP_GEOCODE,$address);
		
		$res = json_decode(file_get_contents($google_api_geocode));
		
		if($res->Status->code==200)
		{
			//请求成功
			$coordinates = $res->Placemark[0]->Point->coordinates;
			$coordinateArr['longitude'] = $coordinates[0];
			$coordinateArr['latitude'] = $coordinates[1];
			
		}else if($res->Status->code==620){
			//请求间隔太小
		}else{
			//其他错误，有待完善...
			/*
			 * 602 - G_GEO_UNAVAILABLE_ADDRESS（地址不可用） 
			 */
			
		}
		
		$coordinateArr['status'] = $res->Status->code;		
		return $coordinateArr;
	}
	
	/**
	 * 根据地址信息解析经纬度（By google API）
	 *
	 * @param string $address：地址信息
	 * @return array：一维数组（经度、纬度）
	 */
	public static function getCoordinateByGoogleApi($address)
	{
		if(empty($address))
		{
			return array();
		}
		
		$coordinateArr = array();
		$address = urlencode($address);
		$google_api_geocode = sprintf(self::GOOGLE_MAP_GEOCODE_API,$address);
		
		$result = json_decode(file_get_contents($google_api_geocode),true);
		
		if(strtolower($result['status'])=='ok')
		{
			$coordinateArr['latitude'] = $result['results'][0]['geometry']['location']['lat'];
			$coordinateArr['longitude'] = $result['results'][0]['geometry']['location']['lng'];
			$coordinateArr['status'] = 200;
		}
		return $coordinateArr;
	}
	
	/**
	 * 根据地址信息解析经纬度（By 高德 API）
	 *
	 * @param string $address：地址信息
	 * @return array：一维数组（经度、纬度）
	 */
	public static function getCoordinateByGDApi($address)
	{
		if(empty($address))
		{
			return array();
		}
	
		$coordinateArr = array();
		$gd_api_geocode = sprintf(self::GD_GEOCODE_API,$address);
	
		$result = file_get_contents($gd_api_geocode);
		$result = strstr($result,'{');
	
		if(!empty($result))
		{
			$tempArr = json_decode($result,true);
			$tempArr = $tempArr['list'][0];
			
			$coordinateArr['latitude'] = $tempArr['y'];
			$coordinateArr['longitude'] = $tempArr['x'];
			$coordinateArr['status'] = 200;
		}
		return $coordinateArr;
	}
	/**
	 * 经纬度换算平面坐标（笛卡尔坐标）
	 * 
	 * @param string $lat：纬度
	 * @param string $lng：经度
	 * @param int $datum：//投影基准面类型：北京54基准面为54，西安80基准面为80，WGS84基准面为84；默认为84
	 * @param string $zonewide：三度带/六度带；默认为3
	 * @return array：一维数组（平面坐标x、y）
	 */
	public static function geodeticToCartesian($lat,$lng,$datum=84,$zonewide=3){
		
		$cartesianArr = array();

		if($zonewide==6)
		{
			$prjno = floor(round($lng)/$zonewide) + 1;	//投影带号
			$L0 = $prjno*$zonewide - 3;					//中央经线度数
		}
		else
		{
			$prjno = floor(round(($lng-1.5)/3)) + 1;	//投影带号
			$L0 = $prjno*3;								//中央经线度数
		}

		if($datum==54)
		{
			$a = 6378245;								//参考椭球体长半轴
			$f = 1/298.3;								//参考椭球体扁率
		} 
		else if($datum==84)
		{
			$a = 6378137;								//参考椭球体长半轴
			$f = 1/298.257223563;						//参考椭球体扁率
		}

		$L0 = $L0*self::IPI;
		$lng = $lng*self::IPI;
		$lat = $lat*self::IPI;
		$e2 = 2*$f-$f*$f;								//椭球第一偏心率
		$l = $lng-$L0;
		$t = tan($lat);
		$m = $l * cos($lat);


		$N = $a/sqrt(1-$e2* sin($lat) * sin($lat));		//卯酉圈曲率半径
		$q2 = $e2/(1-$e2)* cos($lat)* cos($lat);

		$a1 = 1 + 3/4*$e2+45/64*$e2*$e2 + 175/256*$e2*$e2*$e2 + 11025/16384*$e2*$e2*$e2*$e2 + 43659/65536*$e2*$e2*$e2*$e2*$e2;
		$a2 = 3/4*$e2 + 15/16*$e2*$e2 + 525/512*$e2*$e2*$e2 + 2205/2048*$e2*$e2*$e2*$e2 + 72765/65536*$e2*$e2*$e2*$e2*$e2;
		$a3 = 15/64*$e2*$e2 + 105/256*$e2*$e2*$e2 + 2205/4096*$e2*$e2*$e2*$e2 + 10359/16384*$e2*$e2*$e2*$e2*$e2;
		$a4 = 35/512*$e2*$e2*$e2 + 315/2048*$e2*$e2*$e2*$e2 + 31185/13072*$e2*$e2*$e2*$e2*$e2;

		$b1 = $a1*$a*(1-$e2);
		$b2 = -1/2*$a2*$a*(1-$e2);
		$b3 = 1/4*$a3*$a*(1-$e2);
		$b4 = -1/6*$a4*$a*(1-$e2);

		$c0 = $b1;
		$c1 = 2*$b2 + 4*$b3 + 6*$b4;
		$c2 = -(8*$b3 + 32*$b4);
		$c3 = 32*$b4;

		$s = $c0*$lat + cos($lat)*($c1*sin($lat) + $c2*sin($lat)*sin($lat)*sin($lat) + $c3*sin($lat)*sin($lat)*sin($lat)*sin($lat)*sin($lat));		//赤道至纬度B的经线弧长

		$x = $s + 1/2*$N*$t*$m*$m + 1/24*(5-$t*$t+9*$q2+4*$q2*$q2)*$N*$t*$m*$m*$m*$m + 1/720*(61-58*$t*$t+$t*$t*$t*$t)*$N*$t*$m*$m*$m*$m*$m*$m;
		$y = $N*$m + 1/6*(1-$t*$t+$q2)*$N*$m*$m*$m + 1/120*(5-18*$t*$t+$t*$t*$t*$t-14*$q2 - 58*$q2*$t*$t)*$N*$m*$m*$m*$m*$m;
		$y = $y + 1000000*$prjno + 500000 - 38000000;
		
		$cartesianArr['x'] = round(abs($x));
		$cartesianArr['y'] = round(abs($y));
		
		return $cartesianArr;
	}

	/**
	 * 将度分秒转换为度
	 * 
	 * @param string $d 度
	 * @param string $f	分
	 * @param string $m	秒
	 */
	public static function trans_du($d,$f,$m,$dotnum=8){

		//return $d + round($f/60,8) + round($m/60/60,8);
		return $d + round( ($f + round($m/60,$dotnum))/60 , $dotnum);
	}

	/**
	 * 获取周边的经纬度数据
	 * 
	 * @param string $value 度
	 * @param string $distance	距离
	 * @param string $type	类型(经度、纬度)
	 */
	public static function getNearbyPoint($value,$distance){
		
		//先转成NTU度数【1、ntu相当于十万分之一度；2、在较小距离下，ntu值之间的距离与米 单位相当】
		$ntuValue = $value * 100000;		
		$ntuValue += $distance;

		return $ntuValue / 100000;
	}

	/**
	 * 平面坐标转换经纬度 
	 *
	 * @param string $x：平面X
	 * @param string $y：平面Y
	 * @param string $center：中央经线度数（时区*15，例如北京为东八区，中央经线度数为8*15=120）
	 * @return array：一维数组（经纬度latitude、longitude）
	 */
	public static function CartesianToGeo($x, $y, $center)
	{
		$geoArr = array();

		$ParaE1 = 6.69438499958795E-03;//椭球体第一偏心率
		$Parak0 = 1.57048687472752E-07;//有关椭球体的常量
		$Parak1 = 5.05250559291393E-03;//有关椭球体的常量
		$Parak2 = 2.98473350966158E-05;//有关椭球体的常量
		$Parak3 = 2.41627215981336E-07;//有关椭球体的常量
		$Parak4 = 2.22241909461273E-09;//有关椭球体的常量
		$ParaC = 6399596;			   //极点子午圈曲率半径

		$n = 3;
		$y1 = $y - 500000 - 1000000*$n;//减去带号

		$e = $Parak0 * $x;
		$se = Sin($e);
		$bf = $e + Cos($e) * ($Parak1 * $se - $Parak2 * Pow($se, 3) + $Parak3 * Pow($se, 5) - $Parak4 * Pow($se, 7));

		$g = 1;
		$t = Tan($bf);
		$nl = $ParaE1 * Pow(Cos($bf), 2);
		$v = Sqrt(1 + $nl);

		$N = $ParaC / $v;


		$yn = $y1 / $N;
		$vt = Pow($v, 2) * $t;
		$t2 = Pow($t, 2);


		$latitude = $bf - $vt * Pow($yn, 2) / 2.0 + (5.0 + 3.0 * $t2 + $nl - 9.0 * $nl * $t2) * $vt * Pow($yn, 4) / 24.0 - (61.0 + 90.0 * $t2 + 45 * Pow($t2, 2)) * $vt * Pow($yn, 6) / 720.0;

		$cbf = 1 / Cos($bf);
		$longitude = $cbf * $yn - (1.0 + 2.0 * $t2 + $nl) * $cbf * Pow($yn, 3) / 6.0 + (5.0+ 28.0 * $t2 + 24.0 * Pow($t2, 2) + 6.0 * $nl + 8.0 * $nl * $t2) * $cbf * Pow($yn, 5) / 120.0 + $center;

		$geoArr['latitude'] = $latitude;
		$geoArr['longitude'] = $longitude;

		return $geoArr;
	}
}

?>