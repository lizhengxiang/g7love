<?php
/**
 * Created by PhpStorm.
 * User: lizhengxiang
 * Date: 17-3-6
 * Time: 下午9:59
 */
Class RegisteredModel extends BaseModel{
    public function __construct($args)
    {
        parent::__construct($args);
    }

    /**
     * 获取省份，学校
     */
    public function provinces($args){
        $parentid = isset($args['parentid'])? $args['parentid']: 0 ;
        $result = $this->dao->selectList("registered.provinces",$parentid);
        return $this->result($result,1,0);
    }
}