<?php 
error_reporting(E_ALL);ini_set('display_errors',1);

require("db_client.php");
require("config.php");
class DB {
    private $cli;
    private $config;
    function connect($config) {
        $this->config = $config;
        /*
        $this->cli = new Client($this->config);
        $this->cli->connect();
         */
    }
    function query($sql) {
        $data['func_name'] = 'query';
        $data['param'] = array('sql'=> $sql);

        $this->config['send_data'] = $data;
        $this->cli = new Client($this->config);
        $this->cli->connect();
//        $this->cli->send($data);
        /*
        panduan:
        if ($this->cli->isConnected()) {
            $this->cli->send($data);
        } else {
            echo "未连接呢！\n";
            $this->cli->connect();
            goto panduan;
        }
         */
    }

    function commit() {
        $data['func_name'] = 'query';
        $data['param'] = array('sql'=> $sql);
    }
}

$key = 'db1';

$db = new DB();
$db->connect($config[$key]);
$rs = $db->query("select * from test;");


 ?>
