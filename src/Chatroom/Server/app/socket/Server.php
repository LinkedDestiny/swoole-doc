<?php

namespace socket;

use Swoole\Socket\ICallback;
use Swoole\Core\Config;
use Swoole\Core\Route;
use parser\Factory as JFactory;
use db\Factory as DBFactory;
use task\TaskCenter;

class Server implements ICallback
{ 
    private $redis;

    public function __construct() {

    }

    public function onStart()
    {
        echo 'server start, swoole version: ' . SWOOLE_VERSION . PHP_EOL;
    }

    public function onConnect()
    {
        $params = func_get_args();
        $fd = $params[1];
        echo "{$fd} connected\n";
    }

    public function onReceive($server, $frame)
    {
        var_dump($frame->data);
        $this->parse($frame->data , $frame->fd , $server );
    }

    public function onClose()
    {
      	$params = func_get_args();
        $serv = $params[0];
        $fd = $params[1];
        $param = array(
            'json' => 'Chat',
            'ctrl' => 'Chat',
            'method' => 'offline',
            'fd' => $fd
        );
        $this->parse($param , $fd , $serv );
    }

    private function parse($data ,$fd , $serv){
        if( !is_array($data) )
        {
            $data = json_decode($data, true);
        }
        $data['fd'] = $fd;
        if( $data ) {
            Route::route( $data , $serv);
        }
    }


    public function onWorkerStart()
    {
        $this->redis = DBFactory::getInstance('Chat');
    }

    public function onWorkerStop()
    {

    }

    public function onTask()
    {
        $params = func_get_args();
        $serv = $params[0];
        $_data = $params[3];
        $center = new TaskCenter();
        try {
            $center->parse( $_data );
            $result = $center->route( $serv );
            return $result;
        } catch (\Exception $e) {

        }
    }

    public function onFinish()
    {

    }
}
