<?php
/**
 * Created by PhpStorm.
 * User: lizhengxiang
 * Date: 17-3-6
 * Time: 下午9:59
 */
Class RegisteredModel extends BaseModel{
    /*
     * 判断登陆　2016-10-12 23:44
     */
    public function provinces($args){
        $updateParams = array(
            'timestamp'=>$time,
            'logger'=>'Mr.jinyong',
            'message'=>'update',
            'thread' =>'5501',
            'line'	=>50
        );
        $result = $this->dao->selectList("registered.provinces",$updateParams);
        return $this->result($result,1,0);
    }
}