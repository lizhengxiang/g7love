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

    /*public function _initDefaultName(Yaf_Dispatcher $dispatcher) {
        $dispatcher->setDefaultModule("Index")->setDefaultController("Index")->setDefaultAction("index");
    }*/

    public function _initRoute(Yaf_Dispatcher $dispatcher) {

        Yaf_Dispatcher::getInstance()->getRouter()->addRoute(
            "supervar",new Yaf_Route_Supervar("r")
        );

        Yaf_Dispatcher::getInstance()->getRouter()->addRoute(
            "simple", new Yaf_Route_simple('m', 'c', 'a')
        );
    }

    public function _initDb(Yaf_Dispatcher $dispatcher){
        $db = new FPBatis(APP_PATH.'/application/sqlMap.xml');
        Yaf_Registry::set('_db', $db);

    }
}