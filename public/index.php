<?php
/**
 * Created by PhpStorm.
 * User: lizhengxiang
 * Date: 17-2-22
 * Time: 下午8:47
 */
define("APP_PATH",  realpath(dirname(__FILE__) . '/../')); /* 指向public的上一级 */
$app  = new Yaf_Application(APP_PATH . "/conf/application.ini");
//Yaf_Dispatcher::getInstance()->flushInstantly(TRUE);
$app->bootstrap()->run();