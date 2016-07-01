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
            'task_worker_num' => 8
        ));
        $this->serv->on('Start', array($this, 'onStart'));
        $this->serv->on('Connect', array($this, 'onConnect'));
        $this->serv->on('Receive', array($this, 'onReceive'));
        $this->serv->on('Close', array($this, 'onClose'));
        // bind callback
        $this->serv->on('Task', array($this, 'onTask'));
        $this->serv->on('Finish', array($this, 'onFinish'));
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

    public function onReceive( swoole_server $serv, $fd, $from_id, $data ) {
        echo "Get Message From Client {$fd}:{$data}\n";
        $this->test = new Test();
        var_dump($this->test);
        
        $serv->task( serialize($this->test) );
    }

    public function onTask($serv,$task_id,$from_id, $data) {
        echo "This Task {$task_id} from Worker {$from_id}\n";
        echo "Data: {$data}\n";

        $data = unserialize($data);
        $data->index = 2;
        $this->test = new Test();
        $this->test->index = 2;
        var_dump($data);
        var_dump($this->test);

        return "Finished";
    }
    public function onFinish($serv,$task_id, $data) {
        echo "Task {$task_id} finish\n";
        echo "Result: {$data}\n";
        
        var_dump($this->test);
    }
}
$server = new Server();