<?php

namespace DynamicQuery;

class DynamicQuery extends \Pixie\QueryBuilder\QueryBuilderHandler {
	public function __construct($pixie_or_method, $database=null, $host=null, $user=null, $pass=null){
		if(is_object($pixie_or_method)){
			$this->connection = $pixie_or_method;
		} else if($pixie_or_method=='sqlite'){
			$this->connection = new \Pixie\Connection($pixie_or_method, [
				'driver'   => $pixie_or_method,
				'database' => $database
			]);
		} else {
			$this->connection = new \Pixie\Connection($pixie_or_method, [
				'driver'    => $pixie_or_method, // Db driver
				'host'      => $host,
				'database'  => $database,
				'username'  => $user,
				'password'  => $pass,
				'charset'   => 'utf8', // Optional
				'collation' => 'utf8_unicode_ci', // Optional
			]);
		}
		parent::__construct($this->connection);
	}

	public function __call($method, $args){
		return $this->table($method);
	}
	public function __get($method){
		return call_user_func([$this, $method]);
	}

	public function callback($class, $changes){
		if(!isset($this->statements['tables']) or !isset($this->statements['tables'][0]))return;
		$table = $this->statements['tables'][0];
		if($this->adapter=='mysql'){
			$columns = $this->query('show columns from '.$table.' where `Key` = "PRI"')->get();
			if(!isset($columns[0]) or !isset($columns[0]->Field))return;
			$pk = $columns[0]->Field;
		} else if($this->adapter=='sqlite'){
			$columns = $this->query('PRAGMA table_info('.$table.')')->get();
			$pk = false;
			foreach($columns as $column){
				if($column->pk){
					$pk = $column->name;
					break;
				}
			}
			if(!$pk)return;
		}
		if(isset($changes[$pk]))unset($changes[$pk]);
		$this->where($pk, $class->{$pk})->update($changes);
	}

	public function convertResult($result){
		$class = new \DynamicObject\DynamicObject($result, [$this, 'callback']);
		return $class;
	}
	public function convertResults($results){
		$dynamic_results = [];
		foreach($results as $result)
			$dynamic_results[] = $this->convertResult($result);
		return $dynamic_results;
	}

	public function getDynamic(){
		$results = parent::get();
		return $this->convertResults($results);
	}
	public function firstDynamic(){
		$results = parent::first();
		return $this->convertResult($results);
	}
	public function findDynamic($value, $fieldName = 'id'){
		$results = parent::find($value, $fieldName);
		return $this->convertResult($results);
	}
	public function findAllDynamic($fieldName, $value){
		$results = parent::findAll($fieldName, $value);
		return $this->convertResults($results);
	}
}