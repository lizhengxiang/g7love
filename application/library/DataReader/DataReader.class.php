<?php
class DataReader{
	private $objs;
	private $classMethods;
	
	function __construct($result, $objectName, $query){
		$objectFilePath = DAOPATH . $objectName . '.php';
		if(!file_exists($objectFilePath)){
			throw new BatisException("ObjectFileNotExists：". $objectFilePath);
		}

		require_once($objectFilePath);

		$this->classMethods = get_class_methods($objectName);
		
		switch($query){
			case 'query':
				$objs = array();
				
				foreach($result as $key=>$row){
					$obj = $this->setProperty($row, $objectName);
					$objs[$key] = $obj;
				}
				break;
			case 'queryRow':
				$objs = $this->setProperty($result, $objectName);
				break;
		}
		
		$this->objs = $objs;
		return $this;
	}
	
	public function getObjs(){
		return $this->objs;
	}
	
	private function setProperty($row, $objectName){
		$obj = new $objectName();
		foreach($row as $propertyName=>$value){
			$methodName = 'set'.strtoupper(substr($propertyName, 0, 1)).substr($propertyName, 1, strlen($propertyName));
				
			if(in_array($methodName, $this->classMethods))
			$obj->$methodName($value);
			else
			throw new BatisException("in $objectName class methods name $methodName doesn't exists");
		}
		return $obj;
	}
}