<?php

class Server
{
    private $serv;

    public function __construct() {
        $this->serv = new swoole_server("0.0.0.0", 9501);
        $this->serv->set(array(
            'worker_num' => 8,
            'daemonize' => false,
            'max_request' => 10000,
            'dispatch_mode' => 2,
            'package_max_length' => 8192,
            'open_length_check'=> true,
            'package_length_offset' => 0,
            'package_body_offset' => 4,
            'package_length_type' => 'N'
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
        $length = unpack("N" , $data)[1];
        echo "Length = {$length}\n";
        $msg = substr($data,-$length);
    	echo "Get Message From Client {$fd}:{$msg}\n";
    }

    public function onClose( $serv, $fd, $from_id ) {
        echo "Client {$fd} close connection\n";
    }
}

new Server();