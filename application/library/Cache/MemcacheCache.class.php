<?php
class MemcacheCache implements CacheImpl{
	private $memcache;
	
	public function __construct($host, $port = 11211){
		$memcache = new Memcache();
		$memcache->connect($host, $port);
		$this->memcache = $memcache;
	}
	
	public function set($key, $value, $expire=null, $compression=false){
		$this->memcache->set ( $key, $value, $compression, $expire );
	}
	
	public function get($key){
		return $this->memcache->get($key);
	}
	
	public function add($key, $value, $expire=null, $compression=false){
		$this->memcache->add ( $key, $value, $compression, $expire );
	}
	
	public function delete($key){
		$this->memcache->delete($key);
	}
	
	public function flush(){
		$this->memcache->flush();
	}
}