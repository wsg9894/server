<?php

/**
 * ErrorParse - 业务层错误码说明，此错误码只针对业务层－接口层交互使用，不暴露给用户
 * @author luzhizhong
 * @version V1.0
 */

class ErrorParse
{
	//错误定义（此处做为配置使用）
	private static $_errors = array(
			
			'ok' => array('no' => 1,'exp' => '正确'),
	
			'param_error' => array('no' => -1,'exp' => '参数错误'),
			'param_lost' => array('no' => -101,'exp' => '缺少必备参数'),
			'opera_repeat' => array('no' => -102,'exp' => '重复操作'),
			'verify_err' => array('no' => -103,'exp' => '验证失败'),		//安全性验证
					
			'user_error' => array('no' => -2,'exp' => '用户数据有误'),
			'user_not_exist' => array('no' => -201,'exp' => '用户不存在'),
			'user_not_login' => array('no' => -202,'exp' => '用户未登录'),
			'user_login_invalid' => array('no' => -203,'exp' => '用户名或密码错误'),
			'limit_invite_over' => array('no' => -204,'exp' => '邀请好友已达每日上限'),
			
			'db_error' => array('no' => -3,'exp' => '数据库错误'),
			'db_insert_err' => array('no' => -301,'exp' => '数据表插入错误'),
			'db_not_exist' => array('no' => -302,'exp' => '无此数据'),
			'db_update_err' => array('no' => -303,'exp' => '数据表修改错误'),

			'goods_error' => array('no' => -4,'exp' => '商品数据有误'),
			'goods_not_exist' => array('no' => -401,'exp' => '商品不存在'),
			'goods_stock_empty' => array('no' => -402,'exp' => '商品库存为零'),
			'goods_endtime_over' => array('no' => -403,'exp' => '商品已过期（抢兑结束）'),
			'user_score_not_enough' => array('no' => -404,'exp' => '当前积分不足'),
			'limit_day_over' => array('no' => -405,'exp' => '今日兑换已达上限'),
			'limit_user_over' => array('no' => -406,'exp' => '每用户兑换已达上限'),
			'goods_do_not_sale' => array('no' => -407,'exp' => '商品已下架'),
			'goods_not_start' => array('no' => -408,'exp' => '抢兑尚未开始'),
			'goods_exchangenum_error' => array('no' => -409,'exp' => '商品兑换数量有误'),//1、库存不足；2、已达今日限量；3、E豆不足

			'cinema_movie_error' => array('no' => -5,'exp' => '影院/影片/排期数据有误'),
			'no_sel_seat' => array('no' => -501,'exp' => '未选择座位'),
			'roommovie_invalid' => array('no' => -502,'exp' => '无效排期/排期已过期'),
			'seat_invalid' => array('no' => -503,'exp' => '无效座位/座位已售出'),

			'order_error' => array('no' => -6,'exp' => '订单错误'),
			'order_not_exist' => array('no' => -601,'exp' => '订单不存在'),

			'partner_error' => array('no' => -8,'exp' => '下游接口方数据有误'),
			'coupon_not_exist' => array('no' => -801,'exp' => '卡不存在'),
			'coupon_pw_invalid' => array('no' => -802,'exp' => '卡密码错误'),
			'coupon_is_binding' => array('no' => -803,'exp' => '卡已经绑定'),
				
			'unknown_err' => array('no' => -9,'exp' => '未知错误'),
			'process_exception' => array('no' => -901,'exp' => '流程执行异常'),
		    'order_nofind' => array('no' => -111,'exp' => '订单异常'),
	);
	
	/**
	 * 获取错误列表
	 *
	 * @return array[][] 错误列表
	 */
	public static function getErrors()
	{
		return ErrorParse::$_errors;
	}
	
	/**
	 * 获取某一错误NO
	 *
	 * @param string $errKey 错误key;
	 * @return int 错误NO
	 */
	public static function getErrorNo($errKey)
	{
		if(array_key_exists($errKey,ErrorParse::getErrors()))
		{
			return ErrorParse::$_errors[$errKey]['no'];
		}
		else
		{
			return ErrorParse::$_errors['param_error']['no'];
		}
	}

	/**
	 * 获取某一错误描述
	 *
	 * @param string $errKey 错误key;
	 * @return string 错误描述
	 */
	public static function getErrorDesc($errKey)
	{
		if(array_key_exists($errKey,ErrorParse::getErrors()))
		{
			return ErrorParse::$_errors[$errKey]['exp'];
		}
		else
		{
			return ErrorParse::$_errors['param_error']['exp'];
		}
	}
	
}