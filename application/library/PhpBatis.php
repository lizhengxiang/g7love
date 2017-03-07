<?php
/**
 * Created by PhpStorm.
 * User: lizhengxiang
 * Date: 17-2-23
 * Time: 下午10:03
 */

require 'Data/libs/QueryPath/QueryPath.php';
require 'Data/DbConnection.class.php';
require 'Data/Common.class.php';
require 'Data/DbCommand.class.php';
require 'Data/DbException.class.php';
require 'Data/BatisException.class.php';
require 'DataReader/DataReader.class.php';

require 'Cache/Cache.class.php';
require 'Cache/CacheImpl.class.php';
require 'Cache/FileCache.class.php';
require 'Cache/MemcacheCache.class.php';

class PhpBatis{
    public $configFilePath;
    public $connectionId;
    private $_conn;
    private static $mapper;

    /**
     *
     * 初始化phpBatis及相关组件
     * @param String $configFilePath 主配置文件路径
     * @param String $connectionId 通过选择database标签Id号，切换数据源
     * @throws BatisException 当配置文件不存在时
     */
    public function __construct($configFilePath, $connectionId=null){
        if(file_exists($configFilePath)){
            $this->connectionId = $connectionId;
            $this->configFilePath = $configFilePath;
            $this->typeAliases();
            $this->connections();
            return $this;
        }else{
            //throw new BatisException("FileNotExists:".$configFilePath);
            return false;
        }
    }

    /**
     * 初始化全局别名
     * key值必须唯一,否则将被覆盖
     */
    private function typeAliases(){
        $qp = qp($this->configFilePath, 'typeAliases');//->children();
        foreach ($qp as $child) {
            define($child->attr('key'), $child->attr('value'));
        }
    }

    private function connections(){
        $qp = qp($this->configFilePath, 'databases')->children();
        $sourceCount = $qp->size();

        /**
         * 指定数据源
         */
        if(!empty($this->connectionId)){
            $database = $qp->find("database[id={$this->connectionId}]")->children();
            $this->openConn($database);
            return $this;
        }

        /**
         * 默认加载default=true的数据源
         */
        if($sourceCount > 1){
            $database = $qp->find("database[default=true]")->children();
            $this->openConn($database);
            return $this;
        }else if($sourceCount == 0){
            throw new DbException("Error XML:Data source is not configured");
        }
    }

    private function openConn($database){
        foreach ($database as $child) {
            $key = $child->attr('name');
            $value = $child->attr('value');
            $$key= $value;
        }
        $this->_conn = new DbConnection($dsn,$username,$password, isset($charset) ? $charset : 'UTF8');
    }

    /**
     * 设置Mapper配置文件路径
     * @param String mapper配置文件路径
     * @throws BatisException 当mapper配置文件不存在时
     */
    public function setMapper($mapperXML){
        if(!file_exists($mapperXML)){
            throw new BatisException("FileNotExists:". $mapperXML);
            return false;
        }
        self::$mapper = qp($mapperXML, 'mapper');
        return $this;
    }

    public function queryColumn($sqlId, $parameter=null, &$result=null){
        $result = $this->replaceTagAndExecutor($sqlId, 'queryColumn', $parameter);
        return $result;
    }

    public function  selectOne($sqlId, $parameter=null, &$result=null){
        $sqlIdArr = explode('.',$sqlId);
        return $this->setMapper(APP_PATH."/application/mapper/".$sqlIdArr[0].".xml")->queryOne($sqlIdArr[1], $parameter,$result);
    }

    public function queryOne($sqlId, $parameter=null, $result=null){
        $result = $this->replaceTagAndExecutor($sqlId, 'queryRow', $parameter);
        return $result;
    }

    public function  selectList($sqlId, $parameter=null, $result=null){
        $sqlIdArr = explode('.',$sqlId);
        return $this->setMapper(APP_PATH."/application/mapper/".$sqlIdArr[0].".xml")->queryList($sqlIdArr[1], $parameter,$result);
    }

    public function queryList($sqlId, $parameter=null, &$result=null){
        $result = $this->replaceTagAndExecutor($sqlId, 'query', $parameter);
        return $result;
    }


    public function  selectPage($sqlId, $parameter=null, $pageSize=10, $page=0){
        $sqlIdArr = explode('.',$sqlId);
        return $this->setMapper(APP_PATH."/application/mapper/".$sqlIdArr[0].".xml")->queryPagedList($sqlIdArr[1], $parameter,$pageSize, $page);
    }

    public function queryPagedList($sqlId, $parameter=null, $pageSize=10, $page=0){

    }

    public function insert($sqlId, $parameter=null){
        $sqlIdArr = explode('.',$sqlId);
        return $this->setMapper(APP_PATH."/application/mapper/".$sqlIdArr[0].".xml")->insertData($sqlIdArr[1], $parameter);
    }

    public function insertData($sqlId, $parameter=null){
        return $this->exec($sqlId, $parameter);
    }

    public function update($sqlId, $parameter=null){
        $sqlIdArr = explode('.',$sqlId);
        return $this->setMapper(APP_PATH."/application/mapper/".$sqlIdArr[0].".xml")->updateData($sqlIdArr[1], $parameter);
    }

    public function updateData($sqlId, $parameter=null){
        return $this->exec($sqlId, $parameter);
    }

    public function delete($sqlId, $parameter=null){
        $sqlIdArr = explode('.',$sqlId);
        return $this->setMapper(APP_PATH."/application/mapper/".$sqlIdArr[0].".xml")->deleteData($sqlIdArr[1], $parameter);
    }

    public function deleteData($sqlId, $parameter=null){
        return $this->exec($sqlId, $parameter);
    }

    private function exec($sqlId, $parameter=null){
        $qp = qp(self::$mapper)->find('#' . $sqlId);
        $parameterClass = $qp->attr('parameterClass');
        $sqlText		= $qp->text();

        preg_match_all( "/#(.*)#/", $sqlText, $match);

        $sqlText 		= phpBatis::replaceSqlTag($sqlText, $match);

        $command = $this->_conn->createCommand($sqlText);
        if(!empty($parameter)){
            foreach($parameter as $bindKey=>$bindValue)
                $command->bindParameter(':'.$bindKey, $bindValue);
        }
        $command->execute();
        return true;
    }

    public function beginTransaction(){
        $this->_conn->beginTransaction();
    }

    public function commitTransaction(){
        $this->_conn->commit();
    }

    public function rollBackTransaction(){
        $this->_conn->rollBack();
    }

    public function replaceTagAndExecutor($sqlId, $query, &$parameter=null){
        $qp = qp(self::$mapper)->find('#' . $sqlId);
        $parameterClass = $qp->attr('parameterClass');
        $resultClass    = $qp->attr('resultClass');
        $cacheId		= $qp->attr('cacheId');
        $isCache		= $qp->attr('cache');
        $cacheTime		= $qp->attr('cacheTime');
        $prepare		= $qp->attr('prepare');
        $sqlText		= $qp->text();

        $isCache = ($isCache == 'true') ? true : false;
        $prepare = ($prepare == 'true') ? true : false;

        preg_match_all( "/#(.*)#/", $sqlText, $match );

        $sqlText 		= phpBatis::replaceSqlTag($sqlText, $match);
        $cacheKey 		= phpBatis::getCacheKey($sqlText, $parameter) . $query;

        try{
            $command = null;
            $result = null;
            $needCache = false;

            if(!$prepare){
                foreach($parameter as $bindKey=>$bindValue)
                    $sqlText = preg_replace("/$bindKey/", $bindValue, $sqlText);
            }
var_dump($sqlText);exit();
            if($isCache) {
                $cacheObj = new Cache($cacheId, $this->configFilePath);
                $result = unserialize( $cacheObj->get($cacheKey) );
                if( empty($result) ){
                    $command = $this->_conn->createCommand($sqlText);
                    if(!empty($parameter) && $prepare){
                        foreach($parameter as $bindKey=>$bindValue)
                            $command->bindParameter($bindKey, $bindValue);
                    }
                    $needCache = $cacheObj;
                }
            }else{
                $command = $this->_conn->createCommand($sqlText);
                if(!empty($parameter) && $prepare){
                    foreach($parameter as $bindKey=>$bindValue)
                        $command->bindParameter($bindKey, $bindValue);
                }
            }
        }catch(Exception $e){
            throw new BatisException('Exception：', $e->getMessage());
        }

        if($isCache){
            if($result == null){
                $result = $command->$query();
                $cacheObj->set($cacheKey, serialize($result), $cacheTime);
            }
        }else
            $result = $command->$query();

        if(!empty($resultClass)){
            $dataReader = new DataReader($result, $resultClass, $query);
            return $dataReader->getObjs();
        }
        else
            return $result;
    }

    /**
     * 替换文本中所有#id#为Mapper配置文件中指定sql标签的文本
     * @param String $sqlText
     * @param Array $match
     * @throws BatisException 当指定id的sql标签不存在时
     */
    public static function replaceSqlTag($sqlText, $match){
        if(!empty($match)){
            foreach($match[1] as $id){
                $key = '#'. $id .'#';
                if(!empty($replaces))
                    if(key_exists($key, $replaces)) continue;

                if( ($search = qp(self::$mapper)->find('sql[id=' . $id .']')->text()) == null){
                    throw new BatisException('Sql Tag Error : ' . $id . 'Does not exist');
                    return false;
                }else{
                    $replaces[$key] = $search;
                }
            }

            if(!empty($replaces))
                foreach($replaces as $k=>$v){
                    $sqlText = str_replace($k, $v, $sqlText);
                }
            return $sqlText;
        }
    }

    public static function getCacheKey($text, $parameter){
        return $text.serialize($parameter);
    }
}