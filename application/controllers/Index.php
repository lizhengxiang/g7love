<?php
/**
 * Created by PhpStorm.
 * User: lizhengxiang
 * Date: 17-2-22
 * Time: 下午9:01
 */
class IndexController extends Yaf_Controller_Abstract {
    private $_user;
    public function init(){
        //Yaf_Dispatcher::getInstance()->disableView();
        $this->_user = new AdminModel();
    }
    public function indexAction() {//默认Action
        return $this->_user->Registered();
        $this->getView()->assign("content", "Hello lizhengxiang");
    }

    public function searchAction() {//默认Action
        //return "lizhengxiang";
        $this->getView()->assign("content", "Hello 3333");
    }
}