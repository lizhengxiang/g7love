<?php
/**
 * Created by PhpStorm.
 * User: lizhengxiang
 * Date: 17-2-23
 * Time: 下午10:14
 */
Class AdminModel{
    protected $_table = "registered";
    protected $_index = "username";
    private $_db;
    public function __construct()
    {
        //var_dump("ddd");exit();
        $this->_db = Yaf_Registry::get('_db');
    }

    public function Registered(){
        return $this->_table;
        //$result = $this->_db->select($this->_table);

    }
}