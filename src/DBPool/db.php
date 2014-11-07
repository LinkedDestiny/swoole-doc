<?php 
error_reporting(E_ALL);ini_set('display_errors',1);

require("db_client_sync.php");
require("config.php");

class DB {
    private $cli;
    private $config;
    private $send_data = array();   // 需要发送到服务端的数据
    public $cur_key;


    /**
     * _makeCurKey 每次生成一个随机字符串
     * 
     * @date 2014-11-07
     * @param int $len 随机数字符串长度 也可以截取一下
     * @access private
     * @return void
     */
    private function _makeCurKey($len = 8) {
        $cur_key = "";
        for ($i = 0; $i < $len; $i++) {
            $cur_key .= chr(mt_rand(33,126));
        }
        $this->cur_key = md5($cur_key.intval(rand(1,100)+time())/rand(1,100));
    }

    function connect($config = array()) {
        $this->_makeCurKey();
        $this->send_data['cur_key'] = $this->cur_key;
        $this->send_data['param'] = array();
        $this->config = $config;
        $this->cli = new Client($this->config);
        $this->cli->connect();
    }

    function query($sql) {
        $this->send_data['func_name'] = __FUNCTION__;
        $this->send_data['param'] = array('sql'=> $sql);

        $rs = $this->cli->send($this->send_data);
        return $rs;
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

    function release() {
        $this->send_data['func_name'] = __FUNCTION__;
        $this->send_data['param'] = array();
        $rs = $this->cli->send($this->send_data);
    }

    function commit() {
        $this->send_data['func_name'] = __FUNCTION__;
        $this->send_data['param'] = array();
        $rs = $this->cli->send($this->send_data);
    }
}

 ?>
