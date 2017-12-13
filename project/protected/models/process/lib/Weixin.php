<?php

/**
 * Weixin - 微信接口基础操作类
 * @author lzz
 * @link http://mp.weixin.qq.com/wiki/10/6380dc743053a91c544ffd2b7c959166.html（微信错误码参考）
 * @version V1.0 （client认证的token获取、openid获取、用户信息获取、模板消息推送） add 20160120
 * @version V1.1 （加入微信js接口的调用操作：获取jsapi_ticket、签名算法实现） add 20160123
 */

//require_once(Yii::app()->basePath.'/../../inc/model/base/Model_Base_Redis.php');
class Weixin 
{
	const REDIS_KEY_TOKEN = 'wx_token';
	const REDIS_KEY_JSAPI_TICKET = 'wx_ticket';
	
	private $_appId;
	private $_appSecret;
	private $_rssTemplateId;			//订阅模板消息id
	private $_code;						//code，用于获取openid
	private $_openid;					//用户的openid
	private $_accessToken = null;		//客户端认证的token（基础）
	private $_jsapiTicket = null;		//调用微信JS接口的临时票据
	private $_logName;
	
	private $_reqUrls = array(

				//基础支持的token获取，每天有请求数量限制（100000次/天）
				'client_credential' => 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s',
				//OAuth2.0认证的access_token，需code，请求无限量
				'oauth_access_token' => 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=%s&secret=%s&code=%s&grant_type=authorization_code',
				//code请求接口
				'authorize' => 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=%s&redirect_uri=%s&response_type=code&scope=snsapi_base&state=STATE#wechat_redirect',
				//发送模板消息（下发限制：10W/模板/天/公众号？）
				'templage_send' => 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=%s',
				//获取用户信息（此token需要OAuth2.0认证的token信息，客户端认证的token无效；此接口只能获取基础用户信息） 
				'sns_userinfo' => 'https://api.weixin.qq.com/sns/userinfo?access_token=%s&openid=%s&lang=zh_CN',
				//获取用户信息（此token需要客户端认证的token信息，OAuth2.0认证的token无效；此接口除获取用户基础信息外，还可获取subscribe信息【是否关注】）
				'subs_userinfo' => 'https://api.weixin.qq.com/cgi-bin/user/info?access_token=%s&openid=%s&lang=zh_CN',
				
				//获取js接口的临时票据
				'jsapi_ticket' => 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=%s',

		        //小程序获取access_token链接
		       'wx_access_token' => 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s',

		        //小程序消息推送链接
		       'wx_message_send' =>'https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=%s'
					
	);
	
	public function __construct()
	{
		$this->_appId = 'wx159b42037d6ebd28';
		$this->_appSecret = 'dbddf0ff60de752473d51bff06142107';
		$this->_rssTemplateId = 'Yf46jvIXensp4kdGnnX0KGmWtIavCFB_6Dmb6GrjZ3A';
		
 		$this->_logName = Yii::app()->getRuntimePath().'/wx_use/'.date('Y-m-d').'.log';	//log文件路径
		$this->_wLog('into...', $this->_logName);
		
		//设置AccessToken
		$this->setAccessToken();
	}
	/**
	 * 写日志功能实现（追加写）
	 * @param string $msg 日志内容
	 * @param string $logName 日志文件
	 * @return bool 写入结果
	 */
	private function _wLog($msg, $logName)
	{
		//日志新建文件是root权限，导致写入失败，影响前端业务，lzz update at 2017-07-09
		//return true;
		
		if(empty($logName))
		{
			return false;
		}

		$sLog = date('H:i:s').' '.$msg."\n";
	
		$fp = fopen($logName,"a");
		flock($fp, LOCK_EX) ;
		fwrite($fp, $sLog);
		flock($fp, LOCK_UN);
		fclose($fp);
	
		return true;
	}
	
	/**
	 * 请求Access Token（基础支持，每天有请求数量限制）
	 * @return string Token值
	 */
	private function _reqAccessToken()
	{
		try
		{
			$reqUrl = sprintf($this->_reqUrls['client_credential'], $this->_appId, $this->_appSecret);
			$res = $this->_httpRequest($reqUrl);
			$result = json_decode($res, TRUE);
			
		}catch (Exception $e)
		{
			$this->_wLog('_reqAccessToken Exception:'.$e->getMessage(), $this->_logName);
			return null;
		}
		
		if(empty($result['access_token']))
		{
			$this->_accessToken = null;
		}else{
			$this->_accessToken = $result['access_token'];
		}
		return $this->_accessToken;
	}
	
	/**
	 * 设置/存储AccessToken
	 * @return bool 
	 */
	public function setAccessToken()
	{
		//设置AccessToken；考虑到token有两个小时的失效属性，需存储一下
		$rToken = Yii::app()->redis->get(self::REDIS_KEY_TOKEN);
		
		if(empty($rToken))
		{
			$this->_accessToken = $this->_reqAccessToken();
			Yii::app()->redis->set(self::REDIS_KEY_TOKEN, $this->_accessToken, 1800);
			
			$this->_wLog('req token again...', $this->_logName);
		}else{
			$this->_accessToken = $rToken;
		}
		
		//$this->_wLog('token:'.$this->_accessToken, $this->_logName);
		
		return true;
	}
	
	/**
	 * 请求微信JS接口的临时票据
	 * @return string jsapi_ticket
	 */
	private function _reqJSApiTicket()
	{
		try
		{
			$reqUrl = sprintf($this->_reqUrls['jsapi_ticket'], urlencode($this->_accessToken));
			$res = $this->_httpRequest($reqUrl);
			$result = json_decode($res, TRUE);
			
		}catch (Exception $e)
		{
			$this->_wLog('_reqJSApiTicket Exception:'.$e->getMessage(), $this->_logName);
			return null;
		}
		
		if(empty($result['ticket']))
		{
			$this->_jsapiTicket = null;
		}else{
			
			$this->_wLog('Req Ticket:'.$result['ticket'], $this->_logName);
			$this->_jsapiTicket = $result['ticket'];
			return $this->_jsapiTicket;
		}
	}
	
	/**
	 * http请求实现
	 * @param string $url 请求uri
	 * @param string $data post数据
	 * @return string 请求返回
	 */
	private function _httpRequest($url, $data=null)
	{
		$curl = curl_init();
		
		curl_setopt($curl,CURLOPT_URL, $url);
		curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, FALSE);
		
		if(!empty($data))
		{
			curl_setopt($curl, CURLOPT_POST, TRUE);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		}		
				
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		$output = curl_exec($curl);
		curl_close($curl);
		
		return $output;
		
	}
	/**
	 * 获取Access Token
	 * @return string Token值
	 */
	public function getAccessToken()
	{
		return $this->_accessToken;
	}
	
	/**
	 * 构造‘订阅’的模板消息Body
	 * @param string $toUser 目标用户（openid）
	 * @param string $url 跳转url
	 * @param string $keyword1 推送内容
	 * @param string $keyword2 受理时间
	 * @return array[][] 消息Body
	 */
	private function _createTemplateData_Rss($toUser, $url, $keyword1='订阅活动列表', $keyword2='')
	{
		$template = array(
					'touser' => $toUser,
					'template_id' => $this->_rssTemplateId,
					'url' => $url,
				
					'data' => array(
							'first' => array('value'=>'您好，本期板凳订阅活动已推送！', 'color'=>'#173177'),
							'keyword1' => array('value'=>$keyword1, 'color'=>'#173177'),
							'keyword2' => array('value'=>$keyword2, 'color'=>'#173177'),
							'keyword3' => array('value'=>'板凳', 'color'=>'#173177'),
 							'remark' => array('value'=>'点击“详情”查看本期订阅内容', 'color'=>'#173177'),
							),
        			);
		
		return $template;
	}
	/**
	 * 构造通用模板消息Body
	 * @param string $toUser 目标用户（openid）
	 * @param string $templateId 模板id
	 * @param string $url 跳转url
	 * @param array[][] $data 消息内容
	 * @return array[][] 消息Body
	 */
	private function _createTemplateData($toUser, $templateId, $url, $data)
	{
		$template = array(
				'touser' => $toUser,
				'template_id' => $templateId,
				'url' => $url,
				'data' => $data,
		);
	
		return $template;
	}
	
	/**
	 * 下发‘订阅’的模板消息
	 * @param string $toUser 目标用户（openid）
	 * @param string $url 跳转url
 	 * @param string $keyword1 订阅内容
	 * @param string $keyword2 受理时间
	 * @return int 接口返回值（0-ok；其他-错误码）
	 * @link  微信错误码参考：http://mp.weixin.qq.com/wiki/10/6380dc743053a91c544ffd2b7c959166.html
	 */
	public function sendTemplateMsg_Rss($toUser, $url, $keyword1, $keyword2)
	{
		if(empty($toUser))
		{
			return 40003;
		}
		
		$this->_wLog('send_template_msg_rss:'.$toUser, $this->_logName);
		
		$template = $this->_createTemplateData_Rss($toUser, $url, $keyword1, $keyword2);
		$reqUrl = sprintf($this->_reqUrls['templage_send'], $this->_accessToken);
		$res = $this->_httpRequest($reqUrl, json_encode($template));
		$result = json_decode($res, TRUE);
		
		if(!empty($result['errcode']) and $result['errcode'] == 40001)
		{
			$this->_wLog('error:40001', $this->_logName);
			
			//token失效，重新设置
			Yii::app()->redis->delete(self::REDIS_KEY_TOKEN);
		}

		return $result['errcode'] ;
	}
	
	/**
	 * 下发通用的模板消息
	 * @param string $toUser 目标用户（openid）
	 * @param string $templateId 模板id
	 * @param string $url 跳转url
	 * @param array[][] $data 消息内容
	 * @return int 接口返回值（0-ok；其他-错误码）
	 * @link  微信错误码参考：http://mp.weixin.qq.com/wiki/10/6380dc743053a91c544ffd2b7c959166.html
	 */
	public function sendTemplateMsg($toUser, $templateId, $url, $data)
	{
		if(empty($toUser))
		{
			return 40003;
		}
		
		$this->_wLog('send_template_msg:'.$toUser, $this->_logName);
		
		$template = $this->_createTemplateData($toUser, $templateId, $url, $data);
		$reqUrl = sprintf($this->_reqUrls['templage_send'], $this->_accessToken);
		$res = $this->_httpRequest($reqUrl, json_encode($template));
		$result = json_decode($res, TRUE);
		
		if(!empty($result['errcode']) and $result['errcode'] == 40001)
		{
			$this->_wLog('error:40001', $this->_logName);
			
			//token失效，重新设置
			Yii::app()->redis->delete(self::REDIS_KEY_TOKEN);
		}
		
		return $result['errcode'] ;
	}

	public function wxsendTemplateMsg($toUser, $template)
	{
		if(empty($toUser))
		{
			return 40003;
		}

		$this->_wLog('send_template_msg:'.$toUser, $this->_logName);

		$reqUrl = sprintf($this->_reqUrls['wx_access_token'], ConfigParse::getWeixinKey('appId'),ConfigParse::getWeixinKey('secret'));
		$res = $this->_httpRequest($reqUrl);
		$result = json_decode($res, TRUE);

		$reqUrl = sprintf($this->_reqUrls['wx_message_send'], $result['access_token']);
		$res = $this->_httpRequest($reqUrl, json_encode($template));
		$result = json_decode($res, TRUE);

		if(!empty($result['errcode']) and $result['errcode'] == 40001)
		{
			$this->_wLog('error:40001', $this->_logName);

			//token失效，重新设置
			Yii::app()->redis->delete(self::REDIS_KEY_TOKEN);
		}

		return $result['errcode'] ;
	}

	/**
	 * 设置code
	 * @param string $code code码
	 */
	public function setCode($code)
	{
		$this->_code = $code;
	}
	/**
	 * 获取用户openid
	 * @return string code码
	 */
	public function getCode()
	{
		return $this->_code;
	}
	
	/**
	 * 设置用户openid
	 * @param string $openid 用户openid
	 */
	public function setOpenid($openid)
	{
		$this->_openid = $openid;				
	}
	/**
	 * 获取用户openid
	 * @return string 用户openid
	 */
	public function getOpenid()
	{
		return $this->_openid;
	}
	
	/**
	 * 生成可以获得code的url
	 * @param string $redirectUrl 回调url
	 * @return 请求code Url
	 */
	public function createOauthUrlForCode($redirectUrl)
	{
		$url = sprintf($this->_reqUrls['authorize'], $this->_appId, $redirectUrl);
		return $url;
	}

	/**
	 * 生成可以获得openid的url（通过OAuth2.0认证方式）
	 * @param string $code
	 * @return 请求Token Url
	 */
	public function createOauthUrlForOpenid($code)
	{
		$url = sprintf($this->_reqUrls['oauth_access_token'], $this->_appId, $this->_appSecret, $code);
		return $url;
	}
	
	/**
	 * 请求code（通过OAuth2.0认证方式）
	 * @param string $redirectUrl code回调url（OAuth2.0认证流程：获取到code->获取openid/token->请求其他接口）
	 * @todo 下发code请求后，需要在回调接口（$redirectUrl）中完善以下逻辑
	 * 		 1、获取code，直接$_GET['code']即可；
	 * 		 2、请求openid，执行$wx->reqOpenidOnOAuth($code);
	 *		 3、拿到openid，可请求其他接口操作;
	 */
	public function reqCodeOnOAuth($redirectUrl)
	{
		$reqUrl = $this->createOauthUrlForCode($redirectUrl);
		Header("Location: $reqUrl");
	}
	
	/**
	 * 请求openid（通过OAuth2.0认证方式，反复关注/取消关注公众号，openid保持不变）
	 * @param string $code
	 * @return string openid
	 */
	public function reqOpenidOnOAuth($code='')
	{
		$code = empty($code) ? $this->_code : $code;
		
		$reqUrl = $this->createOauthUrlForOpenid($code);
		$res = $this->_httpRequest($reqUrl);
		$result = json_decode($res, TRUE);
	
		$this->_openid = isset($result['openid']) ? $result['openid'] : '';
		return $this->_openid ;
	}
	
	/**
	 * 获取用户信息
	 * @param string $openid
	 * @return array[] 用户信息
	 */
	public function getUInfo($openid='')
	{
		$openid = empty($openid) ? $this->_openid : $openid;

		$this->_wLog('get_user_info:'.$openid, $this->_logName);

		$reqUrl = sprintf($this->_reqUrls['subs_userinfo'], $this->_accessToken, $openid);
		$res = $this->_httpRequest($reqUrl);
		$result = json_decode($res, TRUE);

		if(!empty($result['errcode']) and $result['errcode'] == 40001)
		{
			$this->_wLog('error:40001', $this->_logName);

			//token失效，重新设置
			Yii::app()->redis->delete(self::REDIS_KEY_TOKEN);
		}
		
		return $result;
	}
	
	/**
	 * 构造随机字符串
	 * @param int $length 字符串长度
	 * @return string 随机字符串
	 */
	private function _createRandStr($length=16)
	{
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$str = '';
		for ($i = 0; $i < $length; $i++) 
		{
			$str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
		}
		return $str;
	}
	
	/**
	 * 获取调用微信JS接口的临时票据
	 * jsapi_ticket的有效时长为2小时，且有调用次数限制（1000000次/天），所以需进行缓存处理
	 * @return string jsapi_ticket
	 */
	private function getJsApiTicket()
	{
		$rTicket = Yii::app()->redis->get(self::REDIS_KEY_JSAPI_TICKET);
		
		if(empty($rTicket))
		{
			$this->_jsapiTicket = $this->_reqJSApiTicket();
			//lzz update at 2016-11-08，暂时只缓存10s（查找ticket为空的问题）
			Yii::app()->redis->set(self::REDIS_KEY_JSAPI_TICKET, $this->_jsapiTicket, 5000);
			//Yii::app()->redis->set(self::REDIS_KEY_JSAPI_TICKET, $this->_jsapiTicket, 10);
			
		}else{
			$this->_jsapiTicket = $rTicket;
		}
			
		return $this->_jsapiTicket;
	}
	
	/**
	 * 获取微信JS-SDK权限验证签名，本操作是微信js接口调用的基础
	 * 微信JS-SDK是微信公众平台面向网页开发者提供的基于微信内的网页开发工具包
	 * @return array[] 签名信息
	 */
	public function getSignPackage()
	{
		try{
			$jsapiTicket = $this->getJsApiTicket();
			
			if(empty($jsapiTicket))
			{
				$this->_wLog('getSignPackage Error: ticket empty!', $this->_logName);
				return array();
			}
			
			//URL 一定要动态获取，不能 hardcode.
			$protocol = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off" || $_SERVER["SERVER_PORT"] == 443) ? "https://" : "http://";
			$url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
			$timestamp = time();
			$nonceStr = $this->_createRandStr();
			
			//这里参数的顺序要按照 key 值 ASCII 码升序排序
			$string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
			$signature = sha1($string);
			
			$signPackage = array(
					'appId'     => $this->_appId,
					'nonceStr'  => $nonceStr,
					'timestamp' => $timestamp,
					'url'       => $url,
					'signature' => $signature,
					'rawString' => $string
			);
			
			return $signPackage;
					
		}catch (Exception $e)
		{
			$this->_wLog('getSignPackage Exception:'.$e->getMessage(), $this->_logName);
			return array();
		}

	}
	/**
	 * 判断用户是否关注了我们的服务号（此处暂不考虑订阅号，如要适用订阅号，需先进行绑定操作，然后用unionid来代替openid）
	 * @param string $openid 用户openid（如果没有，需重新请求）
	 * @return bool 1:是；0:否
	 */
	public function isFocus($openid)
	{
		$ret = 0;
	
		if(!empty($openid))
		{
			$uInfo = $this->getUInfo($openid);
			$ret = isset($uInfo['subscribe']) ? $uInfo['subscribe'] : 0;
		}
	
		return $ret;
	}
	
	/**
	 * 是否为微信访客
	 * @return int 1:是；0:否
	 */
	public static function isWxVisitor()
	{
		//有微信浏览器标识，则为微信用户访问，否则为wap用户
		if( FALSE !== strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') )
		{
			return 1;
		}else{
			return 0;
		}
	}

	/*
	 * 微信小程序推送消息
	 * */
	//public static function

	/**
	 * 获取用户信息
	 * @param string $openid
	 * @return array[] 用户信息
	 */
	public function getUserInfo($openid='')
	{
		$openid = empty($openid) ? $this->_openid : $openid;

		$this->_wLog('get_user_info:'.$openid, $this->_logName);

		$reqUrl = sprintf($this->_reqUrls['subs_userinfo'], $this->_accessToken, $openid);
		$res = $this->_httpRequest($reqUrl);
		$result = json_decode($res, TRUE);
		if(!empty($result['errcode']) and $result['errcode'] == 40001)
		{
			$this->_wLog('error:40001', $this->_logName);

			//token失效，重新设置
			Yii::app()->redis->delete(self::REDIS_KEY_TOKEN);
			$this->_wLog('get_user_info:'.$openid, $this->_logName);

			$reqUrl = sprintf($this->_reqUrls['subs_userinfo'], $this->_accessToken, $openid);
			$res = $this->_httpRequest($reqUrl);
			$result = json_decode($res, TRUE);
		}
		return $result;
	}
}