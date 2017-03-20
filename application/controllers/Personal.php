<?php
/**
 * Created by PhpStorm.
 * User: lizhengxiang
 * Date: 17-3-20
 * Time: 下午8:45
 */
class PersonalController extends BaseController {
    private $_Personal;
    public function init(){
        if ($this->getRequest()->isXmlHttpRequest()) {
            Yaf_Dispatcher::getInstance()->disableView();
        }
        $this->_Personal = new PersonalModel($this->getToken());
    }

    public function thumbupAction() {
        $request = $this->getRequest()->getPost();
        $dat =  $this->_Personal->thumbup($request);
        $this->display($dat);
    }
    
    public function getuserinformationAction(){
        $request = $this->getRequest()->getPost();
        $dat =  $this->_Personal->getuserinformation($request);
        $this->display($dat);
    }
}