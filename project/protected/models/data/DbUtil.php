<?php
class DbUtil{
	/**
	* @return array all rows of the query result. Each array element is an array representing a row.
	* An empty array is returned if the query results in nothing.
	*/
	public static function queryAll($sql,$limit=0){
		$connection=Yii::app()->db;		
		$command = $connection->createCommand($sql);
		$rows = array();

		$rows = $command->queryAll();		
		return $rows;
	}
	
	public static function queryRow($sql){
		$connection=Yii::app()->db;		
		$command = $connection->createCommand($sql);
		$rows = array();
		$rows = $command->queryRow();		//// 查询并返回结果中的第一行
		return $rows;
	}
	
	public static function queryScalar($sql){
		$connection=Yii::app()->db;		
		$command = $connection->createCommand($sql);
		$value = null;
		$value = $command->queryScalar();		//// 查询并返回结果中的第一行
		return $value;
		
	}
		
	
	public static function execute($sql){
		$connection=Yii::app()->db;		
		$command = $connection->createCommand($sql);
		$rowCount=$command->execute();
		return $rowCount;	
	}
	
	public static function lastInsertID(){
		return Yii::app()->db->getLastInsertID();
	}
}