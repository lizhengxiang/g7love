<?php
/**
 * Created by PhpStorm.
 * User: lizhengxiang
 * Date: 17-2-23
 * Time: 下午10:14
 */
Class AdminModel extends BaseModel{
    public function Registered(){
        $result = $this->dao->selectList("log.test",[]);
        return $result;
    }
}