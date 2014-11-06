<?php 
class Client {
    private $client;

    protected $port;    //    不是mysql的端口 而是server监听的端口
    protected $timeout; //连接超时 单位s
    protected $server_ip;
    protected $send_data;

    public function __construct(array $config = array()) {
        $this->port = isset($config['port']) ? $config['port'] : 9500; // server监听的端口 决定我们要连接哪个db 因为db连接都是server里
        $this->timeout = isset($config['timeout']) ? $config['timeout'] : 1;
        $this->server_ip = isset($config['server_ip']) ? $config['server_ip'] : "127.0.0.1";
        $this->send_data = $config['send_data'];

        $this->client = new swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);    //尝试异步 send的时候 总是提示未连接 只能在onConnect里send.

        //$this->client = new swoole_client(SWOOLE_SOCK_TCP );    //同步 一连就断了。

        $this->client->on('Connect',array($this,'onConnect'));
        $this->client->on('Receive',array($this,'onReceive'));
        $this->client->on('Close',array($this,'onClose'));
        $this->client->on('Error',array($this,'onError'));

    }

    public function connect() {
        $fp = $this->client->connect($this->server_ip, $this->port, $this->timeout);
        if (!$fp) {
            echo "Error:{$fp->errMsg} [{$fp->errCode}]\n";
            return;
        }
    }

    public function onReceive($cli, $data) {
        //$cli->send("Get");
        echo " Client Receive data: {$data}\n";
    }

    public function onConnect($cli) {
        $this->send($this->send_data);
        $this->time = time();
    }

    public function onClose( $cli) {
        echo "Client close connection\n";
    }

    public function onError() {

    }

    public function send($data) {
        if (is_array($data)) {
            $data = json_encode($data);
        }
        echo "Send data: {$data}";
        $this->client->send( $data );
    }

    public function isConnected() {
        return $this->client->isConnected();
    }
}

 ?>
