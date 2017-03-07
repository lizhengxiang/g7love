<?php
/**
 * Created by PhpStorm.
 * User: lizhengxiang
 * Date: 17-3-6
 * Time: 下午6:19
 */
Class BaseController extends Yaf_Controller_Abstract{
    public function display($args){
        echo $args;die;
    }
}