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
        if (is_array($data)) {
            $data = json_encode($data);
        }
        echo "Send data: {$data} \n";
        $rs = $this->client->send( $data );
        $data = $this->client->recv();
        if (!$data) {
            for ($cnt = 0; $cnt < 3; $cnt++) {
                $data = $this->client->recv();
                if ($data) {
                    break;
                }
            }
            echo "receive faild! \n";
            return false;
        } else {
            //echo "Client Recv data: ".var_dump($data)." \n";
            echo "Client Recv data: ".$data." \n";
        }
        return $data;
    }

    public function isConnected() {
        return $this->client->isConnected();
    }
}

/*
function test_client() {
    $client = new swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC);
    if (!$client->connect('127.0.0.1', 9500)) {
        exit('client connect faild!');
    }

    for ($i = 0; $i < 5; $i++) {
        $client->send(str_repeat($i,100));
        $rs = $client->recv();
        if ($rs === false) {
            echo "recv faild \n";
            break;
        }
        echo "recv[$i] ", $rs, " len:".strlen($rs)."\n";
    }
    $client->send("HELLO WORLD");
    $data = $client->recv(9000,0);
    var_dump($data);
    $client->close();
    unset($client);
}
test_client();
 */
 ?>
