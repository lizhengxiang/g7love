<?php
/**
 * Created by PhpStorm.
 * User: lizhengxiang
 * Date: 17-3-11
 * Time: 上午9:06
 */

Class LoginModel extends BaseModel{
    public function __construct($args)
    {
        parent::__construct($args);
    }

    /*
     * 判断登陆　2016-10-12 23:44
     */
    public function login($args){

        $row = $this->dao->selectOne("registered.login",$args['LoginForm']);
        $userid = $row['id'];
        $Token = $userid;
        if($userid != ''){
            //表示当前用户已经
            return $this->result($Token,1,0);
        }else{
            return $this->result('登陆失败，用户名或密码错误',1,0);
        }
    }
}