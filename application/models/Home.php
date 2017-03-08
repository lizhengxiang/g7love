<?php
/**
 * Created by PhpStorm.
 * User: lizhengxiang
 * Date: 17-3-6
 * Time: 下午8:22
 */
Class HomeModel extends BaseModel{
    /*
     * 判断登陆　2016-10-12 23:44
     */
    public function LoginJudge(){
        $userid = '';
        if($userid != ''){
            //表示当前用户已经
            return $this->result(1,1,0);
        }else{
            return $this->result(0,1,0);
        }
    }
}