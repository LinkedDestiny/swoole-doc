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
        echo $serv->worker_pid . PHP_EOL;

        $case = intval( $data );

        switch ( $case ) {
            case 1:
                swoole_async_readfile( __DIR__."/Test.txt", function($filename, $content) {
                     echo "$filename: $content";
                });
                break;
            case 2:
                swoole_async_writefile('test_2.log', "This is a test log", function($filename) {
                    echo "wirte ok.\n";
                });
                break;
            case 3:
                swoole_async_read( __DIR__."/Test.txt" , function($filename, $content){
                    if( empty( $content ) ) {
                        return false;
                    } else {
                        echo "$filename: $content";
                        return true;
                    }
                } , 16 );
                break;
            default:
                // 注：此处存在一个Bug，如果文件不存在并且没有指定offset（或者指定为-1），会引发一个错误。这里需要注意一下。
                swoole_async_write( 'test_1.log', "This is a test log\n" , -1 , function( $filename, $writen){
                    var_dump( func_get_args() );
                });
                break;
        }
    }

    public function onClose( $serv, $fd, $from_id ) {
        echo "Client {$fd} close connection\n";
    }
}

new Server();