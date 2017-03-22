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

    /**
     * User: lizhengxinag
     * createtime: 17-03-22 23:30
     * functionRole:发表说说
     */
    public function Posting($args){
        $userid = $this->user->id;
        if($userid != ''){
            $parameter=[];
            $parameter['pic1'] =$args['data'][0];
            $parameter['pic2'] =$args['data'][1];
            $parameter['pic3'] =$args['data'][2];
            $parameter['pic4'] =$args['data'][3];
            $parameter['content'] =$args['count'];
            $parameter['userid'] =$userid;
            $data = $this->dao->insert("home.posting",$parameter);
            return $this->result($data,1,0);
        }else{
            return $this->result('',0,0);
        }
    }
}