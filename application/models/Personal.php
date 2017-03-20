<?php
/**
 * Created by PhpStorm.
 * User: lizhengxiang
 * Date: 17-3-20
 * Time: 下午8:46
 */
Class PersonalModel extends BaseModel{
    public function __construct($args)
    {
        parent::__construct($args);
    }

    /**
     * User: lizhengxinag
     * createtime: 17-03-20 20:58
     * functionRole:给主页点赞，点赞每个用户给他人每天只能点一次赞,点赞也可以自己给自己点赞
     */
    public function thumbUp($args){
        if(empty($args['userid'])){
            $args['userid'] = $this->user->id;
        }
        if(preg_match('/^\d*$/',$args['userid'])){
            $args['thumbupuserid']=$this->user->id;
            if($args['thumbupuserid'] == null){
                return $this->result('',0,0);
            }
            $time = date("Y-m-d",time());
            $args['startTime'] = $time.' 00:00:00';
            $args['endTime'] = $time.' 23:59:59';
            $count = $this->dao->selectOne("personal.getThumbUp",$args);
            $tag = $count['total'];
            if(!$tag){
                $this->dao->update("personal.addThumbUp",$args);
                $this->dao->insert("personal.ThumbUplog",$args);
                $data=[];
                //表示该用户点赞成功
                $data['thump'] = 1;
                return $this->result($data,1,0);
            }
            $data=[];
            //表示该用户今天已经点赞
            $data['thump'] = 0;
            return $this->result($data,1,0);
        }else{
            return $this->result('',10,0);
        }
    }

    /**
     * User: lizhengxinag
     * createtime: 17-03-20 21:21
     * functionRole:获取自己自己或别人的基本信息
     * return 基本信息＋该用户是否是自己（否则返回有没有关注）等基本信息
     */
    public function GetUserInformation($args)
    {
        $userid = isset($args['userid'])?$args['userid']:$this->user->id;
        if(preg_match('/^\d*$/',$userid)){
            //根据用户userid查找该用户的基本信息
            $row =  $this->dao->selectOne("personal.getUserInformation",$userid);
            if($row){
                if($userid == $this->user->id){
                    //表示该用户是自己
                    $row['self'] = 1;
                    return $this->result($row,1,0);
                }else{
                    //表示该用户不是自己
                    //@todo 这里需要处理下状态该用户不是自己的关注好友
                    $row['self'] = 2;
                    return $this->result($row,1,0);
                }
            }else{
                return $this->result('',10,0);
            }
        }elseif ($userid == null){
            //如果$userid == null则表示该用户没有登陆，应该提示该用户登陆
            return $this->result('',0,0);
        }
        //表示该用户在做非法操作
        return $this->result('',10,0);
    }
}