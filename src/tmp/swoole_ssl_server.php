<?php

class Server
{
    private $serv;

    public function __construct() {
        $this->serv = new swoole_server("0.0.0.0", 9501 ,SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL );

        $dir = __DIR__ . "/cert/";
        $this->serv->set(array(
            'worker_num' => 8,
            'daemonize' => false,
            'max_request' => 10000,
            'dispatch_mode' => 2,
            'open_ssl'=> true,
            'ssl_cert_file' => $dir . "test.crt",
            'ssl_key_file' => $dir . "test.key"
        ));

        $this->serv->on('Start', array($this, 'onStart'));
        $this->serv->on('Connect', array($this, 'onConnect'));
        $this->serv->on('Receive', array($this, 'onReceive'));
        $this->serv->on('Close', array($this, 'onClose'));

        $this->serv->start();
    }

    public function onStart( $serv ) {
        echo "Start\n";
    }

    public function onConnect( $serv, $fd, $from_id ) {
        echo "Client {$fd} connect\n";
       
    }

    public function onReceive( swoole_server $serv, $fd, $from_id, $data ) {
    	echo "Get Message From Client {$fd}:{$data}\n";
        $this->serv->send( $fd, "Hello client\n");
    }

    public function onClose( $serv, $fd, $from_id ) {
        echo "Client {$fd} close connection\n";
    }
}

new Server();