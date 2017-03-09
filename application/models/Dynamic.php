<?php
/**
 * Created by PhpStorm.
 * User: lizhengxiang
 * Date: 17-3-8
 * Time: 下午9:05
 */
class DynamicModel extends BaseModel{

    public function getdynamic($args){
        $result = $this->dao->selectList("dynamic.getdynamic",$args);
        /*
         * 这里需要处理该用户有没有登陆判断有没有对动态操作AND动态发表离当前时间
         * @todo 计算时间有可能会耗时，dai观察看看会不会影响速度
         */
        $user =100;//  Yii::$app->user->getId();
        if(!empty($user)){
            $len = sizeof($result);
            for ($i=0; $i<$len; $i++){
                $query['userid']=$user;
                $query['dynamicId']=$result[$i]['id'];
                $row = $this->dao->selectOne("dynamic.dynamiclog",$args);
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
        $args['userid'] = 100;
        if($args['type'] == 1){
            $args['praise'] = 1;
        }elseif($args['type'] == 2){
            $args['reportNum'] = 1;
        }
        $data = $this->dao->selectOne("dynamic.selectDynamic",$args);
        $val = $data['total'];
        if(!$val && $args['type'] == 1){
            //更新点赞,更新两个表，没有当前记录则需要创建
            if(!$val){
                Yii::$app->db->createCommand('insert INTO dynamiclog(praise,dynamicId,userid) VALUES (1,:dynamicId,:userid)',[
                    ':dynamicId' => $args['id'],':userid' => Yii::$app->user->getId()
                ])->execute();
            }else{
                Yii::$app->db->createCommand('update dynamiclog set praise=1 WHERE dynamicId=:dynamicId AND userid=:userid',[':dynamicId' => $args['id'],':userid' =>Yii::$app->user->getId()])
                    ->execute();
            }
            //更新动态表
            Yii::$app->db->createCommand('update dynamic set praise=praise+1,updatetime=now()  WHERE id=:dynamicId',[':dynamicId' => $args['id']])
                ->execute();
            return 1;
        }elseif(!$val && $args['type'] == 2){
            //@todo　这里需要考虑举报达到五次后怎么处理
            //更新点赞,更新两个表，没有当前记录则需要创建
            if(!$val){
                Yii::$app->db->createCommand('insert INTO dynamiclog(reportNum,dynamicId,userid) VALUES (1,:dynamicId,:userid)',[
                    ':dynamicId' => $args['id'],':userid' => Yii::$app->user->getId()
                ])->execute();
            }else{
                Yii::$app->db->createCommand('update dynamiclog set reportNum=1 WHERE dynamicId=:dynamicId AND userid=:userid',[':dynamicId' => $args['id'],':userid' =>Yii::$app->user->getId()])
                    ->execute();
            }
            //更新动态表
            Yii::$app->db->createCommand('update dynamic set reportNum=reportNum+1,updatetime=now()  WHERE id=:dynamicId',[':dynamicId' => $args['id']])
                ->execute();
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