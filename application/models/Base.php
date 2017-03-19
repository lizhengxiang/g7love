<?php
/**
 * Created by PhpStorm.
 * User: lizhengxiang
 * Date: 17-3-5
 * Time: 下午6:12
 */

Class BaseModel{
    public $dao;
    public $user;

    public function __construct($args)
    {
        $this->dao = Yaf_Registry::get('_db');
        $this->isLogin($args);
    }

    /*
     * 计算时间差,返回时间差
     */

    public function timeDifference($startdate,$enddate){
        $timediff = $enddate-$startdate;
        $days = intval($timediff/86400);
        if($days>0){
            $timeDifference = $days.'天前';
        }else{
            $remain = $timediff%86400;
            $hours = intval($remain/3600);
            if($hours>0){
                $timeDifference = $hours.'小时前';
            }else{
                $remain = $remain%3600;
                $mins = intval($remain/60);
                if($mins>0){
                    $timeDifference = $mins.'分钟前';
                }else{
                    $secs = $remain%60;
                    $timeDifference = $mins.'秒钟前';
                }
            }
        }
        return $timeDifference;
    }

    /*
     * 封装返回的结果
     * $status＝１表示成功，$status＝０表示未登录，$status＝１0非法操作
     * @todo $status＝１0　要不要将该用户进行退出操作，并锁定该用户30min，若非登陆用户则进行Ip锁定
     */
    public function result($data,$status,$code){
        $result = [];
        $result['code'] = $code;
        $result['status'] = $status;
        $result['data'] = $data;
        return json_encode($result);
    }


    /**
     * User: lizhengxinag
     * createtime: 17-03-19 15:25
     * functionRole:忽略验证
     */
    public function ignoreValidation(){
        return [
            'Home_loginjudge',
            'Registered_provinces',
            'Dynamic_getdynamic',
            'Login_login'
        ];
    }

    /**
     * User: lizhengxinag
     * createtime: 17-03-19 15:25
     * functionRole:验证登录
     */
    public function isLogin($request){
        $user = new StdClass();
        if(!empty($request['Token'])){
            $user->id = 100;
            $user->username = 'wj';
            $this->user = $user;
        }else if(in_array($request['controller'].'_'.$request['action'],$this->ignoreValidation())){
            $user->id = '';
            $user->username = '';
        }else{
            echo $this->result('',0,0);exit();
        }
    }
    /*
     *更具id和userid检查该用户有没有权限修改帖子的权限
     * return １表示有权限，0表示没有权限
     */

    /*public function validateDynamic($id){
        $count = (new \yii\db\Query())
            ->from('dynamic')
            ->where('id=:id and userid=:userid')
            ->addParams([':id' => $id,':userid' => Yii::$app->user->getId()]);
        return $val = $count->count();
    }*/
    
}