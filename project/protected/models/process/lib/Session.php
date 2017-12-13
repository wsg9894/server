<?php

/**
 * Session - Session操作类
 * @author luzhizhong
 * @deprecated 在php 5.4版本，session_is_registered、session_register、session_unregister均已弃用
 * 			        可恶的php，居然不向下兼容
 * @version V1.0
 */

class Session 
{
 	function __construct()
	{
		$this->session_start();
	}
	function __destruct()
	{
	}
	
	/**
	 * 返回session
	 * @param string $name
	 * @return unknown
	 */
	public function get($name)
	{
		return $this->is_registry($name)? $_SESSION[$name]:"";
	}
	
	/**
	 * 添加session
	 * @param string $name
	 * @param object $value
	 * @return bool
	 */
	public function add($name,$value)
	{
		$_SESSION[$name] = $value;
		return $this->session_register($name);
	}

	/**
	 * 删除session中的一项
	 * @param unknown_type $name
	 * @return bool
	 */
	public function delete($name)
	{
		return $this->session_unregister($name);
	}
	
	/**
	 * 初始化session
	 * @return bool
	 */
	public function session_start()
	{
		//session_start();
		//if (!isset($_SESSION)) 
		if (!session_id())
		{
			session_start();
		}
		
	}

	/**
	 * 销毁session
	 * @return bool
	 */
	public function session_destroy()
	{
		return session_destroy();
	}

	/**
	 * 注册session
	 * @param string $name
	 * @return bool
	 */
	public function session_register($name)
	{
		//return session_register($name);
		return isset($_SESSION[$name]) ? 1 : 0; 
	}

	/**
	 * 注销session
	 * @param string $name
	 * @return bool
	 */
	public function session_unregister($name)
	{
		//return session_unregister($name);
		unset($_SESSION[$name]);
		return true;
	}

	/**
	 * session解码
	 * @param object $data
	 * @return bool
	 */
	public function session_decode($data)
	{
		return session_decode($data);
	}
	
	/**
	 * session编码
	 * @param object $date
	 * @return bool
	 */
	public function session_encode($data)
	{
		return session_encode($data);
	}
	
	/**
	 * 是否注册了变量
	 * @param string $name
	 * @return bool
	 */
	public function is_registry($name)
	{
/*		
		if (PHP_VERSION < 4.3)
		{
			return session_is_registered($name);
		}else
		{
			return isset($_SESSION) && array_key_exists($name, $_SESSION);
		}
*/
		return isset($_SESSION) && array_key_exists($name, $_SESSION);
	}
}
?>