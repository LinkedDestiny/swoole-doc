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
            'open_length_check'=> false,
            'package_length_offset' => 0,
            'package_body_offset' => 8,
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
    	$request = unpack("N" , $data );
        print_r($request);
        $length = $request[1];
        $cmd = $request[2];
        $filename = substr($data,-$length);
        echo "length = {$length}\n";
        echo "$filename\n";
        $serv->sendfile( $fd , __DIR__ . "/" .  $filename );
    }

    public function onClose( $serv, $fd, $from_id ) {
        echo "Client {$fd} close connection\n";
    }
}

new Server();