<?php
class Cache{
	private $cache;
	
	public function __construct($cacheId, $config){
		$cacheId = '#'. $cacheId;
		$elements = qp($config, 'caches')->find($cacheId)->children('property');
		$cacheType = qp($elements)->find("property[name=type]")->attr('value');

		switch(strtolower($cacheType)){
			case 'file':
				$filepath = qp($elements)->find("property[name=filepath]")->attr('value');
				if(empty($filepath))
				throw new BatisException('cache tag '. $cacheId . ' filepath attribute does not exist');
				else
				$this->cache = new FileCache($filepath);
				break;
			case 'memcache':
				foreach ($elements as $element){
					$key = $element->attr('name');
					$$key = $element->attr('value');
				}

				if(empty($host) || empty($port))
				throw new BatisException('cache tag '. $cacheId . 'host or port attribute does not exist');
				else
				$this->cache =  new MemcacheCache($host, $port);
				break;
		}
		return $this;
	}

	public function set($key, $value, $expire=null, $compression=false){
		$this->cache->set ( $key, $value, $compression, $expire );
	}
	
	public function get($key){
		return $this->cache->get($key);
	}
	
	public function add($key, $value, $expire=null, $compression=false){
		$this->cache->add ( $key, $value, $compression, $expire );
	}
	
	public function delete($key){
		$this->cache->delete($key);
	}
	
	public function flush(){
		$this->cache->flush();
	}
}