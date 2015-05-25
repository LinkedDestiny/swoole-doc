<?php
class EventClient
{
    public $client;
    public function __construct() {
        // 向server发送 及接收返回要封装起来
        $this->client = new swoole_client(SWOOLE_SOCK_TCP);

        if (!$this->client->connect('127.0.0.1', 9550))
        {
            exit("event连接失败\n");
        }

    }

    public function send($data) {
        $this->client->send(json_encode($data));
        $recv = $this->client->recv();
        return $recv;
    }
}
