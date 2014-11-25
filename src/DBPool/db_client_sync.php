<?php 
class Client {
    private $client;

    protected $port;    //    不是mysql的端口 而是server监听的端口
    protected $timeout; //连接超时 单位s
    protected $server_ip;
    protected $send_data;
    protected $cmd_list;    //  

    public function __construct(array $config = array()) {
        $this->port = isset($config['port']) ? $config['port'] : 9500; // server监听的端口 决定我们要连接哪个db 因为db连接都是server里
        $this->timeout = isset($config['timeout']) ? $config['timeout'] : 1;
        $this->server_ip = isset($config['server_ip']) ? $config['server_ip'] : "127.0.0.1";
        $this->send_data = isset($config['send_data']) ? $config['send_data'] : "";
        $this->client = new swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC);
        $this->connect();
    }

    public function connect() {
        $fp = $this->client->connect($this->server_ip, $this->port, $this->timeout);
        if (!$fp) {
            echo "Error:{$fp->errMsg} [{$fp->errCode}]\n";
            return;
        }
    }

    public function send($data) {
        $need_recv = true;
        if ($data['func_name'] == 'release') {
            $need_recv = false;
        }
        if (is_array($data)) {
            $data = json_encode($data);
        }
//        echo "Send data: {$data} \n";
        $rs = $this->client->send( $data );
        if ($need_recv) {
            $recv_data = $this->client->recv();
            if (!$recv_data) {
                echo " error_code:{$this->client->errCode}";
            }

            return $recv_data;
        }
    }

    public function isConnected() {
        return $this->client->isConnected();
    }
}

 ?>
