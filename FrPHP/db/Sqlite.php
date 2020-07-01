<?php

// +----------------------------------------------------------------------
// | FrPHP { a friendly PHP Framework } 
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2099 http://frphp.jizhicms.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 留恋风 <2581047041@qq.com>
// +----------------------------------------------------------------------
// | Date：2019/11/16
// +----------------------------------------------------------------------


namespace FrPHP\db;

use PDO;
use PDOException;
use PDOStatement;

class Sqlite { 

    private static $instance = false;
    private $pdo;
    private $Statement;
    private $arrSql;

    public function __construct(){ 
        class_exists('PDO') or exit("请在php.ini里面开启php_pdo扩展！");
        extension_loaded('pdo_sqlite') or exit("请在php.ini里面开启pdo_sqlite扩展！");
        $sqlite = APP_PATH.DB_PATH.'/'.DB_NAME.'.db';
        if(file_exists($sqlite)){ 
            try {
                $this->pdo = new PDO('sqlite:'.$sqlite); 
            } catch (PDOException $e) {
                exit('<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />数据库无法链接，如果您是第一次使用，请先配置数据库！');
            }
            
        }else{ 
            exit('<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />数据库无法链接，如果您是第一次使用，请先配置数据库！');

        } 

    } 

    public static function getInstance(){
        if(self::$instance===false){
            self::$instance = new self();
        }
        return self::$instance;
    }
 
        //执行 SQL 语句，返回PDOStatement对象,可以理解为结果集
    public function query($sql){
        $this->arrSql[] = $sql;
        $this->Statement = $this->pdo->query($sql);
      //  $this->Statement->setFetchMode(PDO::FETCH_NUM); 
        if ($this->Statement) {
            return $this;
        }else{
            $msg = $this->pdo->errorInfo();
            if($msg[2]){
                $log_name = date('Y-m-d-H-i-s-').time();
				register_log('数据库错误：' . $msg[2] . end($this->arrSql),$log_name);
				exit;
            }
			
        }
    }
    
    
    //执行SQL语句返回数组
    public function getArray($sql){

        if(!$result = $this->query($sql))return array();
        $rows = array();
        while($rows[] = $this->Statement->fetch(PDO::FETCH_ASSOC)){}
        $this->Statement=null;
        array_pop($rows);
        return $rows;
    }
    
    //执行一条 SQL 语句，并返回受影响的行数
    public function exec($sql)
    {
        $this->arrSql[] = $sql;
        $n = $this->pdo->exec($sql);
        $msg = $this->pdo->errorInfo();
        
        if(!$n){
            $msg = $this->pdo->errorInfo();
            if($msg[2]){
				$log_name = date('Y-m-d-H-i-s-').time();
				register_log('数据库错误：' . $msg[2] . end($this->arrSql),$log_name);
				exit;
				
			} 
        }
        return $n;
    }
    
    //获取插入影响行数
    public function lastInsertId(){
        return $this->pdo->lastInsertId();
    }
    
    //获取表信息
    public function getTable($table){ 
        $stmt = $this->getArray("pragma table_info({$table})");
        if(!$stmt){
            $msg = $this->pdo->errorInfo();
            if($msg[2]){
				$log_name = date('Y-m-d-H-i-s-').time();
				register_log('数据库错误：' . $msg[2] . end($this->arrSql),$log_name);
				exit;
			}
        }
        $rows=array();
        foreach ($stmt as $key => $value) {
             $rows[$value['name']] = null;
        }

        return $rows;
    }

} 

?>