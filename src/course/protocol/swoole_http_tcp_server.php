<?php
/**
 * Created by PhpStorm.
 * User: lancelot
 * Date: 16-7-30
 * Time: 下午8:00
 */

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


        $port = $this->serv->listen("0.0.0.0", 9502, SWOOLE_SOCK_TCP);
        $port->set(
            [
                'open_eof_split'=> true,
                'package_eof' => "\r\n"
            ]
        );
        $port->on('Receive', array($this, 'onTcpReceive'));

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

    public function onTcpReceive( swoole_server $serv, $fd, $from_id, $data ) {
        var_dump($data);
        $data_list = explode("\r\n", $data);
        foreach ($data_list as $msg) {
            if( !empty($msg) ) {
                echo "Get Message From Client {$fd}:{$msg}\n";
            }

        }
    }


    public function onClose( $serv, $fd, $from_id ) {
        echo "Client {$fd} close connection\n";
    }
}

new Server();