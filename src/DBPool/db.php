<?php 
error_reporting(E_ALL);ini_set('display_errors',1);

require("db_client_sync.php");
require("config.php");

/**
 * DB 
 * DB 基础类 类似于我们程序中的那个pdo类
 * 
 * @package 
 * @version $id$
 * @copyright 1997-2005 The PHP Group
 * @author zhanglei5 <zhanglei5@group.com> 
 * @license PHP Version 3.0 {@link http://www.php.net/license/3_0.txt}
 */
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
        //$this->_makeCurKey();
        //$this->send_data['cur_key'] = $this->cur_key;
        $this->send_data['param'] = array();
        $this->config = $config;
        $this->cli = new Client($this->config);
        $this->cli->connect();
    }



    function __call($name, $arguments) {
        $allow_function = array("query","exec", "beginTransaction","commit","errorCode","errorInfo","getAttribute","getAvailableDrivers",
            "inTransaction",
            "lastInsertId","prepare","quote","rollBack","setAttribute",
            "release");
        if (in_array($name, $allow_function)) {
            $this->send_data['func_name'] = $name; 
            $this->send_data['param'] = $arguments;
            $rs = $this->cli->send($this->send_data);
        } else {
            echo "deny function name {$name} \n";
        }
    }

    function __destruct() {
        echo "db destruct release()\n";
        $this->release();
    }
}

 ?>
