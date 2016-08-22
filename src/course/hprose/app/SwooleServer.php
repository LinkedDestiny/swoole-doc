<?php
/**
 * Created by PhpStorm.
 * User: lancelot
 * Date: 16-8-6
 * Time: 下午4:26
 */
require_once "../vendor/autoload.php";

use Hprose\Swoole\Server;
use Hprose\Swoole\Socket\Service;

class SwooleServer
{
    private $serv;
    public static $PDO;

    public function __construct() {
        $this->serv = new Server("http://0.0.0.0:9501");
        $this->serv->set(array(
            'worker_num' => 1,
            'daemonize' => false,
            'max_request' => 10000,
            'dispatch_mode' => 2,
        ));

        $this->serv->on('Start', array($this, 'onStart'));
        $this->serv->on('WorkerStart', array($this, 'onWorkerStart'));

        // 加载配置文件
        $config = require "config.php";

        // 在这里注册对外服务接口
        foreach($config['api_list'] as $controller)
        {
            require_once __DIR__ . "/ctrl/$controller.php";
            $this->serv->add( new $controller() );
        }

        $port = $this->serv->listen("0.0.0.0", 9502, SWOOLE_SOCK_TCP);
        $rpc_service = new Service();
        $rpc_service->socketHandle($port);
        foreach($config['api_list'] as $controller)
        {
            require_once __DIR__ . "/ctrl/$controller.php";
            $rpc_service->add( new $controller() );
        }

        $this->serv->start();
    }

    public function onStart( $serv ) {
        echo "Start\n";
        //INIT

    }

    public function onWorkerStart( $serv, $worker_id ) {
        echo "Worker {$worker_id} start\n";

        // TODO 在这里加载可能的全局变量
        if(!$serv->taskworker){
            SwooleServer::$PDO = new PDO(
                "mysql:host=localhost;port=3306;dbname=Test",
                "root",
                "123456",
                array(
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8';",
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_PERSISTENT => true
                )
            );
            echo "Worker\n";
        }
    }
}

new SwooleServer();