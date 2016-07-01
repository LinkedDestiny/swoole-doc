<?php
/**
 * Created by PhpStorm.
 * User: lancelot
 * Date: 16-6-18
 * Time: 下午4:11
 */

class Client
{
    private $client;
    public function __construct() {
        $this->client = new swoole_client(SWOOLE_SOCK_TCP);
    }
    public function connect() {
        if( !$this->client->connect("127.0.0.1", 9501 , 1) ) {
            echo "Error: {$fp->errMsg}[{$fp->errCode}]\n";
        }
        fwrite(STDOUT, "请输入消息：");
        $msg = trim(fgets(STDIN));
        $this->client->send( $msg );
        sleep(1);
        $message = $this->client->recv();
        echo "Get Message From Server:{$message}\n";
    }
    public function test() {
        $this->client = new swoole_client(SWOOLE_SOCK_TCP);

    }
}
$client = new Client();
$client->connect();