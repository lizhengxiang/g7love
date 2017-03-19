<?php
/**
 * Created by PhpStorm.
 * User: lizhengxiang
 * Date: 17-3-6
 * Time: 下午8:22
 */
Class HomeModel extends BaseModel{
    public function __construct($args)
    {
        parent::__construct($args);
    }
    /*
     * 判断登陆　2016-10-12 23:44
     */
    public function LoginJudge(){
        if($this->user->id != ''){
            //表示当前用户已经
            return $this->result(1,1,0);
        }else{
            return $this->result(0,1,0);
        }
    }
}