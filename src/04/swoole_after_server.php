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
            'debug_mode'=> 1,
        ));

        $this->serv->on('Start', array($this, 'onStart'));
        $this->serv->on('Connect', array($this, 'onConnect'));
        $this->serv->on('Receive', array($this, 'onReceive'));
        $this->serv->on('Close', array($this, 'onClose'));
        // bind callback
        $this->serv->on('Timer' , array($this, 'onTimer'));
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

        $param = array(
            'fd' => $fd,
            'msg' => $data
        );
        $str = json_encode( $param );
        $serv->after( 1000 , array($this, 'onAfter') , $str );
    }

    public function onClose( $serv, $fd, $from_id ) {
        echo "Client {$fd} close connection\n";
    }

    public function onAfter( $data ) {
        $param = json_decode( $data, true );
        $this->serv->send( $param['fd'] , $param['msg']);
    }

    public function onTimer( $serv, $interval ) {
        // Do nothing.
    }
}

new Server();

// use src/o2/swoole_async_client to test the server.

?>
