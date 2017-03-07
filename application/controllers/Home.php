<?php
/**
 * Created by PhpStorm.
 * User: lizhengxiang
 * Date: 17-3-6
 * Time: 下午8:20
 */
class HomeController extends BaseController {
    private $_Home;
    public function init(){
        if ($this->getRequest()->isXmlHttpRequest()) {
            Yaf_Dispatcher::getInstance()->disableView();
        }
        $this->_Home = new HomeModel();
    }

    public function loginjudgeAction() {//默认Action
        $dat =  $this->_Home->LoginJudge();
        $this->display($dat);
    }
}