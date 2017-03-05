<?php
interface CacheImpl{
	public function set($key, $value, $expire=null, $compression=false);
	
	public function get($key);
	
	public function add($key, $value, $expire=null, $compression=false);
	
	public function delete($key);
}