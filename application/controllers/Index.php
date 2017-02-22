<?php
/**
 * Created by PhpStorm.
 * User: lizhengxiang
 * Date: 17-2-22
 * Time: 下午9:01
 */

class IndexController extends Yaf_Controller_Abstract {
    public function indexAction() {//默认Action
        $this->getView()->assign("content", "Hello lizhengxiang");
    }

    public function searchAction() {//默认Action
        //return "lizhengxiang";
        $this->getView()->assign("content", "Hello 3333");
    }
}