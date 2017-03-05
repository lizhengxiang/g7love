<?php 
class BatisException extends Exception{
	public function __construct($message, $code=0){
		echo $message . '：' . $code;
	}
}