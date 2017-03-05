<?php
class FileCache implements CacheImpl{
	private $filepath;
	
	public function __construct($filepath){
		if(!is_dir($filepath))
		throw new Exception("$filepath Not a folder");
		
		if(!is_writable($filepath))
		throw new Exception("$filepath Can not write");
		
		$this->filepath = $filepath;
		return $this;
	}
	
	public function set($key, $value, $compression=false, $expire=null){
		if($expire!=null){
			$expire = (int)$expire;
			if(!is_int($expire)){
				throw new Exception("expire Must be an integer");
				return false;
			}
			$content = time()+$expire;
		}else{
			$content = '';
		}
		$content .= "<!--{expire}-->".$value;
		file_put_contents($this->filepath . md5($key), $content);
	}
	
	public function get($key){
		$file = $this->filepath . md5($key);
		if(file_exists($file)){
			$content = file_get_contents($file);
			if($content != ''){
				$content = explode("<!--{expire}-->", file_get_contents($file));
				
				if($content[0]!=''){
					if($content[0]-time() <= 0){
						unlink($file);
						return null;
					}
				}
				
				return $content[1];
				
			}else
			return null;
		}
	}
	
	public function add($key, $value, $expire=null, $compression=false){
		$file = $this->filepath + $key;
		if(file_exists($file)){
			throw new Exception("this key $key Already exists!");
		}else{
			$this->set($key, $value, $expire);
		}
	}
	
	public function delete($key){
		$file = md5($key);
		if(file_exists($file)){
			unlink($file);
		}else{
			return false;
		}
	}
	
	public function flush(){
		$result = glob($this->filepath."*");
		if(!empty($result))
		foreach($result as $itemFile){
			if(is_file($itemFile))
			unlink($itemFile);
		}
	}
}