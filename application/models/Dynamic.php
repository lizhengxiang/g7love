<?php
/**
 * Created by PhpStorm.
 * User: lizhengxiang
 * Date: 17-3-8
 * Time: 下午9:05
 */
class DynamicModel extends BaseModel{
    public function __construct($args)
    {
        parent::__construct($args);
    }

    public function getdynamic($args){
        $args['userid'] = $this->user->id;
        $result = $this->dao->selectList("dynamic.getdynamic",$args);
        /*
         * 这里需要处理该用户有没有登陆判断有没有对动态操作AND动态发表离当前时间
         * @todo 计算时间有可能会耗时，dai观察看看会不会影响速度
         */
        if(!empty($this->user->id)){
            $len = sizeof($result);
            for ($i=0; $i<$len; $i++){
                $query['user']=$this->user->id;
                $query['dynamicId']=$result[$i]['id'];
                $row = $this->dao->selectOne("dynamic.dynamiclog",$query);
                if(!empty($row)){
                    $result[$i]['reportNumTag'] = $row['reportNum'];
                    $result[$i]['praiseTag'] = $row['praise'];
                    $result[$i]['forwardingNumTag'] = $row['forwardingNum'];
                    $startdate=strtotime($result[$i]['createtime']);
                    $enddate=time();
                    $result[$i]['time'] = $this->timeDifference($startdate,$enddate);
                }else{
                    $startdate=strtotime($result[$i]['createtime']);
                    $enddate=time();
                    $result[$i]['time'] = $this->timeDifference($startdate,$enddate);
                    $result[$i]['reportNumTag'] =0;
                    $result[$i]['praiseTag'] = 0;
                    $result[$i]['forwardingNumTag'] =0;
                }
            }
        }else{
            $len = sizeof($result);
            for ($i=0; $i<$len; $i++){
                $startdate=strtotime($result[$i]['createtime']);
                $enddate=time();
                $result[$i]['time'] = $this->timeDifference($startdate,$enddate);
                $result[$i]['reportNumTag'] =0;
                $result[$i]['praiseTag'] = 0;
                $result[$i]['forwardingNumTag'] =0;
            }
        }
        return $this->result($result,1,0);
    }

    /*
     * 处理点赞等操作
     */
    public function doEevaluation($args){
        //检查该用户是否第一次操作
        $args['userid'] = $this->user->id;
        if($args['type'] == 1){
            $args['praise'] = 1;
        }elseif($args['type'] == 2){
            $args['reportNum'] = 1;
        }
        $data = $this->dao->selectOne("dynamic.selectDynamic",$args);
        $val = $data['total'];
        if(!$val && $args['type'] == 1){
            $tmp = $this->dao->selectOne("dynamic.selectDynamic",['id'=>$args['id'],'userid'=>$args['userid']]);
            //更新点赞,更新两个表，没有当前记录则需要创建
            if(!$tmp['total']){
                $this->dao->insert("dynamic.insertDynamiclogPraise",$args);
            }else{
                $this->dao->update("dynamic.updateDynamiclogPraise",$args);
            }
            $this->dao->update("dynamic.updateDynamicPraise",$args);
            return 1;
        }elseif(!$val && $args['type'] == 2){
            $tmp = $this->dao->selectOne("dynamic.selectDynamic",['id'=>$args['id'],'userid'=>$args['userid']]);
            //@todo　这里需要考虑举报达到五次后怎么处理
            //更新点赞,更新两个表，没有当前记录则需要创建
            if(!$tmp['total']){
                $this->dao->insert("dynamic.insertDynamiclogReportNum",$args);
            }else{
                $this->dao->update("dynamic.updateDynamiclogReportNum",$args);
            }
            $this->dao->update("dynamic.updateDynamicReportNum",$args);
            return 1;
        }else{
            //非法操作
            return 10;
        }
    }

    /*
     * 点赞，举报，转发
     */
    public function evaluation($args){
        if($args['arg']=='like' && preg_match('/^\d*$/',$args['id'])){
            //更具id和类型来操作更新数据 type=1表示点赞
            $args['type'] = 1;
            return $this->result($this->doEevaluation($args),1,0);
        }else if($args['arg']=='report' && preg_match('/^\d*$/',$args['id'])){
            //更具id和类型来操作更新数据 type=2表示举报
            $args['type'] = 2;
            return $this->result($this->doEevaluation($args),1,0);
        }else{
            //表示该用户在做非法操作，暂时status=10表示非法操作
            //@todo 考虑要不要对非法操作用户非法操作一天达到多少次，锁定该用户的账号30min
            return $this->result('',10,0);
        }
    }
}