<?php

class Test
{
    public $index = 0;
}

class Server
{
    private $serv;
    private $test;

    public function __construct() {
        $this->serv = new swoole_server("0.0.0.0", 9501);
        $this->serv->set(array(
            'worker_num' => 8,
            'daemonize' => false,
            'max_request' => 10000,
            'dispatch_mode' => 2,
        ));
        $this->serv->on('Start', array($this, 'onStart'));
        $this->serv->on('Connect', array($this, 'onConnect'));
        $this->serv->on('Receive', array($this, 'onReceive'));
        $this->serv->on('Close', array($this, 'onClose'));
        $this->serv->on('WorkerStart', array($this, 'onWorkerStart'));

        $this->serv->start();
    }
    public function onStart( $serv ) {
        echo "Start\n";
    }
    public function onConnect( $serv, $fd, $from_id ) {
        echo "Client {$fd} connect\n";
    }
    public function onClose( $serv, $fd, $from_id ) {
        echo "Client {$fd} close connection\n";
    }

    public function onWorkerStart( $serv , $worker_id) {
        if( $worker_id == 0 )
        {
            $this->test=new Test();
            $this->test->index = 1;
            swoole_timer_tick(1000, array($this, 'onTick'), "Hello");
        }
    }

    public function onReceive( swoole_server $serv, $fd, $from_id, $data ) {
        echo "Get Message From Client {$fd}:{$data}\n";
      
        echo "Continue Handle Worker\n";
    }

    public function onTick($timer_id,  $params = null) {
        echo "Timer {$timer_id} running\n";
        echo "Params: {$params}\n";
        
        echo "Timer running\n";
        echo "recv: {$params}\n";

        var_dump($this->test);
    }
}

$server = new Server();