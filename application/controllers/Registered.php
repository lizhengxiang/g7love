<?php
/**
 * Created by PhpStorm.
 * User: lizhengxiang
 * Date: 17-3-6
 * Time: 下午9:56
 */
class RegisteredController extends BaseController {
    private $_Registered;
    public function init(){
        if ($this->getRequest()->isXmlHttpRequest()) {
            Yaf_Dispatcher::getInstance()->disableView();
        }
        $this->_Registered = new RegisteredModel();
    }

    public function provincesAction() {
        $request = [];
        $request['parentid'] = 20;
        $dat =  $this->_Registered->provinces($request);
        $this->display($dat);
    }
}