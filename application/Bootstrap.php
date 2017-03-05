<?php
/**
 * Created by PhpStorm.
 * User: lizhengxiang
 * Date: 17-2-22
 * Time: 下午9:31
 * 所有在Bootstrap类中, 以_init开头的方法, 都会被Yaf调用,
 * 这些方法, 都接受一个参数:Yaf_Dispatcher $dispatcher
 * 调用的次序, 和申明的次序相同
 */
class Bootstrap extends Yaf_Bootstrap_Abstract{

    private $_config;
    public function _initConfig() {
        $this->_config = Yaf_Application::app()->getConfig();
        Yaf_Registry::set("config", $this->_config);
    }

    public function _initDefaultName(Yaf_Dispatcher $dispatcher) {
        $dispatcher->setDefaultModule("Index")->setDefaultController("Index")->setDefaultAction("index");
    }

    /*public function _initRoute(Yaf_Dispatcher $dispatcher) {
        $router = Yaf_Dispatcher::getInstance()->getRouter();
        $router->addConfig(Yaf_Registry::get("config")->routes);
    }*/

    public function _initDb(Yaf_Dispatcher $dispatcher){
        $db = new PhpBatis(APP_PATH.'/application/config.xml');
        Yaf_Registry::set('_db', $db);
    }
}