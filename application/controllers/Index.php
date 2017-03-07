<?php
/**
 * Created by PhpStorm.
 * User: lizhengxiang
 * Date: 17-2-22
 * Time: 下午9:01
 */
class IndexController extends BaseController {
    private $_user;
    public function init(){
        if ($this->getRequest()->isXmlHttpRequest()) {
            Yaf_Dispatcher::getInstance()->disableView();
        }
        $this->_user = new AdminModel();
    }
    
    public function dexAction() {//默认Action
        $dat =  $this->_user->Registered();
        $this->display($dat);
    }

    public function addAction() {//默认Action
        $dat =  $this->_user->Registered();
        $this->display($dat);
    }

    public function abcAction() {//默认Action
        $dat = ["lizhengxiag"=>2993];// $this->_user->Registered();
        $this->display($dat);
    }
}