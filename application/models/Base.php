<?php
/**
 * Created by PhpStorm.
 * User: lizhengxiang
 * Date: 17-3-5
 * Time: 下午6:12
 */

Class BaseModel{
    public $dao;
    public function __construct()
    {
        $this->dao = Yaf_Registry::get('_db');
    }
}