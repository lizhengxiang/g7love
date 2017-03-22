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
        $this->_Home = new HomeModel($this->getToken());
    }

    public function loginjudgeAction() {//默认Action
        $dat =  $this->_Home->LoginJudge();
        $this->display($dat);
    }
    
    public function postingAction(){
        $request = $this->getRequest()->getPost();
        $data =  $this->_Home->Posting($request);
        $this->display($data);
    }
}