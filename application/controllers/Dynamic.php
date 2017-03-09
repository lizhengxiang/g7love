<?php
/**
 * Created by PhpStorm.
 * User: lizhengxiang
 * Date: 17-3-8
 * Time: 下午9:03
 */
class DynamicController extends BaseController{
    private $_Dynamic;
    public function init(){
        if ($this->getRequest()->isXmlHttpRequest()) {
            Yaf_Dispatcher::getInstance()->disableView();
        }
        $this->_Dynamic = new DynamicModel();
    }

    public function getdynamicAction() {
        $request = $this->getRequest()->getPost();
        $data =  $this->_Dynamic->getdynamic($request);
        $this->display($data);
    }
    
    public function evaluationAction(){
        $request = $this->getRequest()->getPost();
        $user = null;
        if ($user) {
            $request['code'] = 0;
            $request['status'] = 0;
            $this->display(json_encode($request));
        }
        $data =  $this->_Dynamic->evaluation($request);
        $this->display($data);
    }
}