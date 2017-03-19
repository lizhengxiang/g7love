<?php
/**
 * Created by PhpStorm.
 * User: lizhengxiang
 * Date: 17-3-11
 * Time: 上午9:05
 */
class LoginController extends BaseController {
    private $_Login;
    public function init(){
        if ($this->getRequest()->isXmlHttpRequest()) {
            Yaf_Dispatcher::getInstance()->disableView();
        }
        $this->_Login = new LoginModel($this->getToken());
    }

    public function loginAction() {
        $request = $this->getRequest()->getPost();
        $dat =  $this->_Login->login($request);
        $this->display($dat);
    }
}