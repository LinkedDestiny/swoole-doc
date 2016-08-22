<?php
/**
 * Created by PhpStorm.
 * User: lancelot
 * Date: 16-7-30
 * Time: 下午8:13
 */
class Server
{
    private $serv;

    /**
     * @var PDO
     */
    private $pdo;

    public function __construct()
    {
        $this->serv = new swoole_websocket_server("0.0.0.0", 9501);
        $this->serv->set([
            'worker_num' => 1,
            'dispatch_mode' => 2,
            'daemonize' => 0,
        ]);

        $this->serv->on('message', array($this, 'onMessage'));
        $this->serv->on('Request', array($this, 'onRequest'));

        $port1 = $this->serv->listen("0.0.0.0", 9503, SWOOLE_SOCK_TCP);
        $port1->set(
            [
                'open_eof_split'=> true,
                'package_eof' => "\r\n"
            ]
        );
        $port1->on('Receive', array($this, 'onTcpReceive'));

        $this->serv->start();
    }

    public function onMessage(swoole_websocket_server $_server, $frame)
    {
        foreach($_server->connections as $fd)
        {
            $info = $_server->connection_info($fd);
            var_dump($info);
        }
    }

    public function onRequest($request, $response)
    {
        foreach($this->serv->connections as $fd)
        {
            $info = $this->serv->connection_info($fd);
            switch($info['server_port'])
            {
                case 9501:
                {
                    // websocket
                    if($info['websocket_status'])
                    {

                    }
                    $response->end("");
                }

                case 9503:
                {
                    // TCP
                }
            }

            var_dump($info);
        }
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


}

new Server();