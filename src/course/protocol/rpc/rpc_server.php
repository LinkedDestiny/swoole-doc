<?php
/**
 * Created by PhpStorm.
 * User: lancelot
 * Date: 16-7-30
 * Time: 下午10:31
 */

require "vendor/autoload.php";

use Hprose\Swoole\Server as SwooleServer;
use Hprose\Swoole\Socket\Service;


class Server
{
    private $serv;

    public function __construct() {
        $this->serv = new swoole_http_server("0.0.0.0", 9501);
        $this->serv->set(array(
            'worker_num' => 1,
            'daemonize' => false,
            'max_request' => 10000,
            'dispatch_mode' => 2,
        ));

        $this->serv->on('Start', array($this, 'onStart'));
        $this->serv->on('Connect', array($this, 'onConnect'));
        $this->serv->on('Request', array($this, 'onRequest'));
        $this->serv->on('Close', array($this, 'onClose'));

        // add rpc port
        $port = $this->serv->listen("0.0.0.0", 9502, SWOOLE_SOCK_TCP);
        $port->set(
            ['open_eof_split'=> false,]
        );
        $rpc_service = new Service();
        $rpc_service->socketHandle($port);

        $rpc_service->addFunction(array($this, 'upload'));


        // add udp port
        $udp_port =  $this->serv->listen("0.0.0.0", 9503, SWOOLE_SOCK_UDP);
        $udp_port->on('packet', function ($serv, $data, $addr) {
            var_dump($data, $addr);
            
        });
        $this->serv->start();
    }

    public function onStart( $serv ) {
        echo "Start\n";
    }

    public function onConnect( $serv, $fd, $from_id ) {
        echo "Client {$fd} connect\n";

    }

    public function onRequest($request, $response) {
        var_dump($request->fd);

    }

    public function upload($data ) {
        var_dump($data);
        return $data;
    }


    public function onClose( $serv, $fd, $from_id ) {
        echo "Client {$fd} close connection\n";
    }
}

new Server();