<?php

class Server
{
    private $http;

    public function __construct() {
        $this->http = new swoole_http_server("127.0.0.1", 9501);

        $this->http->set(
            array(
                'worker_num' => 16,
                'daemonize' => false,
                'max_request' => 10000,
                'dispatch_mode' => 1
            )
        );

        $this->http->on('Start', array($this, 'onStart'));
        $this->http->on('request' , array( $this , 'onRequest'));
        $this->http->on('message' , array( $this , 'onMessage'));
        $this->http->start();
    }

    public function onStart( $serv ) {
        echo "Start\n";
    }

    public function onRequest($request, $response) {
        $response->end("<h1>Hello Swoole.</h1>");
    }

    public function onMessage($request, $response) {
        echo $request->message;
        $response->message(json_encode(array("data1", "data2")));
    }
}

new Server();