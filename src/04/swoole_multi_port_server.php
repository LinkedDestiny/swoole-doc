<?php
/**
 * Multiple Port Listen Example.
 *
 * @author Lancelot https://github.com/LinkedDestiny
 */
class Server
{
    private $serv;

    public function __construct() {
        $this->serv = new swoole_server("192.168.1.124", 9501);
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

        $this->serv->addlistener("127.0.0.1" , 9502 , SWOOLE_TCP );

        $this->serv->start();
    }

    public function onStart( $serv ) {
        echo "Start\n";
    }

    public function onConnect( $serv, $fd, $from_id ) {
        echo "Client {$fd} connect\n";
    }

    public function onReceive( swoole_server $serv, $fd, $from_id, $data ) {
        $info = $serv->connection_info($fd, $from_id);
        //来自9502的内网管理端口
        if($info['from_port'] == 9502) {
            $serv->send($fd, "welcome admin\n");
        }
        //来自外网
        else {
            $serv->send($fd, 'Swoole: '.$data);
        }
    }

    public function onClose( $serv, $fd, $from_id ) {
        echo "Client {$fd} close connection\n";
    }
}

new Server();

// use src/o2/swoole_async_client to test the server.
?>