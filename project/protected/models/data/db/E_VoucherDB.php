<?php

/**
 * E_VoucherDB - 现金券售卖信息DB操作类
 * @author luzhizhong
 * @version V1.0
 */
class E_VoucherDB extends CActiveRecord
{

	/**
	 * Returns the static model of the specified AR class.
	 * @return CActiveRecord the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * 获取表名
	 *
	 * @return string 表名
	 */
	public function tableName()
	{
		return '{{e_voucher}}';
	}
	
	/**
	 * 如果表中没有主键，可以在此设置
	 *
	 * @return string 主键名
	 */
  
	public function primaryKey()
	{		
		//对于复合主键，要返回一个类似如下的数组
		//return array('pk1', 'pk2');
	}
  
	/**
	 * 关联查询设置
	 * 切记，当使用关联查询时，如果两表有相同的字段命名，则主表的字段名要加 ‘t.’，从表用‘别名.’即可
	 *
	 * @return array[] 关联查询配置
	 */
/* 
	public function relations() {
	}
 */	
	/**
	 * 属性的验证规则
	 *
	 * @return array[] 规则列表
	 */
	public function rules()
	{
		return array(
			//array('username, password', 'required'),	//不能为空
			//array('username', 'length', 'max'=>25),		//字段长度验证
		);
		
	}
	
	/**
	 * 获取单条记录（by 条件）
	 * 
	 * @param string $condition 查询条件设定，相当于SQL中的WHERE，多条件以"and"分割；形如：id=:id and IdCardNo=:IdCardNo
	 * @param array[] $params 查询参数数组，对应$condition中的值定义；形如：array(':id'=>2,':IdCardNo'=>'test')
	 * @param array[] $relations 关联查询数组
	 * @return object 结果记录。可在此基础上进行删除、更改等操作；也可访问->attributes直接返回结果数据
	 */	
	public function getOneRecordByCondition($condition,$params,$relations=array())
	{
		return $this->model()->with($relations)->find($condition, $params);
	}

	/**
	 * 获取单条查询数据（by 条件）
	 * 
	 * @param string $condition 查询条件设定，相当于SQL中的WHERE，多条件以"and"分割；形如：id=:id and IdCardNo=:IdCardNo
	 * @param array[] $params 查询参数数组，对应$condition中的值定义；形如：array(':id'=>2,':IdCardNo'=>'test')
	 * @param array[] $relations 关联查询数组 
	 * @return array[] 查询数据
	 */	
	public function getOneResultByCondition($condition,$params,$relations=array())
	{		
		$record = $this->getOneRecordByCondition($condition, $params, $relations);		
		if(empty($record))
		{
			return array();
		}
			
		$result = $record->attributes;		//主表数据
		if(!empty($relations))
		{
			foreach ($relations as $relation)
			{
				if(empty($relation))
				{
					continue;
				}
				$tempAttriArr = $record[$relation]->attributes;		//关联表数据
				if(!empty($tempAttriArr))
				{
					//汇总所有关联表信息
					$result = array_merge($tempAttriArr, $result);
				}
			}
		}
		//return empty($record) ? array() : $record->attributes;
		return $result;

	}

	/**
	 * 获取单条记录（by Sql语句）
	 * 
	 * @param string $sql SQL语句
	 * @param array[] $relations 关联查询数组
	 * @return object 结果记录，可在此基础上进行删除、更改等操作；也可访问->attributes直接返回查询数据
	 */	
	public function getOneRecordBySql($sql,$relations=array())
	{
		return $this->model()->with($relations)->findBySql($sql);
	}

	/**
	 * 获取单条查询数据（by Sql语句）
	 *
	 * @param string $sql SQL语句
	 * @param array[] $relations 关联查询数组
	 * @return array[] 查询数据
	 */
	public function getOneResultBySql($sql,$relations=array())
	{
		$record = $this->getOneRecordBySql($sql, $relations);		
		if(empty($record))
		{
			return array();
		}
			
		$result = $record->attributes;		//主表数据
		if(!empty($relations))
		{
			foreach ($relations as $relation)
			{
				if(empty($relation))
				{
					continue;
				}
				$tempAttriArr = $record[$relation]->attributes;		//关联表数据
				if(!empty($tempAttriArr))
				{
					//汇总所有关联表信息
					$result = array_merge($tempAttriArr, $result);
				}
			}
		}
		//return empty($record) ? array() : $record->attributes;
		return $result;
			}
	
	/**
	 * 获取多条记录（by 条件）
	 *
	 * @param string $condition 查询条件设定，相当于SQL中的WHERE，多条件以"and"分割；形如：id=:id and IdCardNo=:IdCardNo
	 * @param array[] $params 查询参数数组，对应$condition中的值定义；形如：array(':id'=>2,':IdCardNo'=>'test')
	 * @param array[] $relations 关联查询数组
	 * @return object 结果记录。可在此基础上进行删除、更改等操作；也可访问->attributes直接返回结果数据
	 */
	public function getAllRecordByCondition($condition,$params,$relations=array())
	{
		return $this->model()->with($relations)->findAll($condition, $params);
	}
	
	/**
	 * 获取多条查询数据（by 条件）
	 *
	 * @param string $condition 查询条件设定，相当于SQL中的WHERE，多条件以"and"分割；形如：id=:id and IdCardNo=:IdCardNo
	 * @param array[] $params 查询参数数组，对应$condition中的值定义；形如：array(':id'=>2,':IdCardNo'=>'test')
	 * @param array[] $relations 关联查询数组
	 * @return array[][] 查询数据
	 */
	public function getAllResultByCondition($condition,$params,$relations=array())
	{
		$results = array();
		$records = $this->getAllRecordByCondition($condition, $params, $relations);
		
		foreach ($records as $record )
		{
			$result = $record->attributes;		//主表数据
			if(!empty($relations))
			{
				foreach ($relations as $relation)
				{
					if(empty($relation))
					{
						continue;
					}
					$tempAttriArr = $record[$relation]->attributes;		//关联表数据
					if(!empty($tempAttriArr))
					{
						//汇总所有关联表信息
						$result = array_merge($tempAttriArr, $result);
					}
				}
			}				
			$results[] = $result;
		}
		return $results;
	}

	/**
	 * 获取多条记录（by Sql语句）
	 *
	 * @param string $sql SQL语句
	 * @param array[] $relations 关联查询数组
	 * @return object 结果记录，可在此基础上进行删除、更改等操作；也可访问->attributes直接返回查询数据
	 */
	public function getAllRecordBySql($sql,$relations=array())
	{
		return $this->model()->with($relations)->findAllBySql($sql);
	}
	
	/**
	 * 获取多条查询数据（by Sql语句）
	 *
	 * @param string $sql SQL语句
	 * @param array[] $relations 关联查询数组
	 * @return array[][] 查询数据
	 */
	public function getAllResultBySql($sql,$relations=array())
	{
		$results = array();
		$records = $this->getAllRecordBySql($sql, $relations);
		
		foreach ($records as $record )
		{
			$result = $record->attributes;		//主表数据
			if(!empty($relations))
			{
				foreach ($relations as $relation)
				{
					if(empty($relation))
					{
						continue;
					}
					$tempAttriArr = $record[$relation]->attributes;		//关联表数据
					if(!empty($tempAttriArr))
					{
						//汇总所有关联表信息
						$result = array_merge($tempAttriArr, $result);
					}
				}
			}				
			$results[] = $result;
		}
		
		return $results;	
	}
	
	/**
	 * 获取结果数量（by 条件）
	 *
	 * @param string $condition 查询条件设定，相当于SQL中的WHERE，多条件以"and"分割；形如：id=:id and IdCardNo=:IdCardNo
	 * @param array[] $params 查询参数数组，对应$condition中的值定义；形如：array(':id'=>2,':IdCardNo'=>'test')
	 * @return int 结果数量
	 */
	public function getCountByCondition($condition,$params)
	{
		return $this->model()->count($condition, $params);
	}	
	/**
	 * 获取结果数量（by Sql语句）
	 *
	 * @param string $sql SQL语句，形如：SELECT COUNT(id) FROM 
	 * @return int 结果数量
	 */
	public function getCountBySql($sql)
	{
		return $this->model()->countBySql($sql);
	}
	
	/**
	 * 检查是否有符合指定的条件：至少一条（by 条件）
	 *
	 * @param string $condition 查询条件设定，相当于SQL中的WHERE，多条件以"and"分割；形如：id=:id and IdCardNo=:IdCardNo
	 * @param array[] $params 查询参数数组，对应$condition中的值定义；形如：array(':id'=>2,':IdCardNo'=>'test')
	 * @return bool 是/否
	 */
	public function checkExistsByCondition($condition, $params)
	{
		return $this->model()->exists($condition, $params);
	}	
		
	
	/**
	 * 删除单条（by 记录）
	 * 
	 * @param object $record 结果记录obj
	 * @return bool 删除结果
	 */	
	public function delOneByRecord($record)
	{
		$retu = true;
		if(count($record))
			$retu = $record->delete();
		
		return $retu;
	}

	/**
	 * 删除单条（by 条件）
	 * 
	 * @param string $condition 查询条件设定，相当于SQL中的WHERE，多条件以"and"分割；形如：id=:id and IdCardNo=:IdCardNo
	 * @param array[] $params 查询参数数组，对应$condition中的值定义；形如：array(':id'=>2,':IdCardNo'=>'test')
	 * @return bool 删除结果
	 */
	public function delOneByCondition($condition, $params)
	{
		$retu = true;
		
		$record = $this->getOneRecordByCondition($condition,$params);
		if(count($record))
			$retu = $record->delete();
	
		return $retu;
	}

	/**
	 * 删除多条（by 条件）
	 * 
	 * @param string $condition 查询条件设定，相当于SQL中的WHERE，多条件以"and"分割；形如：id=:id and IdCardNo=:IdCardNo
	 * @param array[] $params 查询参数数组，对应$condition中的值定义；形如：array(':id'=>2,':IdCardNo'=>'test')
	 * @return int 删除的行数
	 */
	public function delAllByCondition($condition, $params)
	{
		return $this->model()->deleteAll($condition, $params);
	}
	
	/**
	 * 更新（by 条件）
	 *
	 * @param array[] $attributes 待修改的属性列表，相当于SQL中的UPDATE SET；形如：array('RegEmail'=>'test123','Pass'=>'55555');
	 * @param string $condition 查询条件设定，相当于SQL中的WHERE，多条件以"and"分割；形如：id=:id and IdCardNo=:IdCardNo
	 * @param array[] $params 查询参数数组，对应$condition中的值定义；形如：array(':id'=>2,':IdCardNo'=>'test')
	 * @return int 修改的有效行数
	 */
	public function updateByCondition($attributes,$condition, $params)
	{
		return $this->model()->updateAll($attributes,$condition, $params);
	}
		
}