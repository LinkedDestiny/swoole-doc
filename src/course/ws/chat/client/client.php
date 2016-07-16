<?php
require __DIR__ . "/WebSocketClient.php";

class Client
{
    private $client;
    private $channel = 0;

    private $online_list;

    public function __construct() {
        $this->client = new WebSocketClient("127.0.0.1", 10100);
    }

    public function connect() {
        $this->client->connect($this);
    }

    public function onReceive( $cli, $data ) {
        $frame = $this->client->recv($data);
        if( empty($frame->data) ) return;
        $param = json_decode($frame->data, true);
        var_dump($param);
        if( $param['op'] == 'online' ) {
            echo "New User {$param['name']} online!\n";
            $this->online_list[$param['fd']] = $param['name'];
        }
        else if($param['op'] == 'recv') {
            echo "{$this->online_list[$param['from']]} say: {$param['msg']}\n";
        }
        else if($param['op'] == 'onlineList') {
            $list = $param['list'];
            echo "Online: \n";
            foreach ($list as $fd => $name) {
                $this->online_list[$fd] = $name;
                echo "$name\n";
            }
        }
        else if($param['op'] == 'offline') {
            echo "{$this->online_list[$param['fd']]} offline!\n";
            unset($this->online_list[$param['fd']]);
        }
    }

    public function onConnect( $cli) {
        $this->client->sendHeader($this->client->createHeader());

        fwrite(STDOUT, "Enter your name: ");
        $msg = trim(fgets(STDIN));
        $data = json_encode( array(
            'json' => 'Chat',
            'ctrl' => 'Chat',
            'method'=> 'online',
            'name' => $msg
        ));
        $this->client->send( $data );

        swoole_event_add(STDIN, function($fp){
            $msg = trim(fgets(STDIN));
            if($msg=='exit'){
                $data = json_encode( array(
                    'json' => 'Chat',
                    'ctrl' => 'Chat',
                    'method'=> 'offline',
                    'name' => $msg
                ));
                $this->client->send( $data );
                exit();
            }
            $data = json_encode( array(
                'json' => 'Chat',
                'ctrl' => 'Chat',
                'method'=> 'send',
                'sendto'=> $this->channel,
                'msg' => $msg
            ));
            $this->client->send( $data );
        });


    }

    public function onClose( $cli) {
        echo "Client close connection\n";
    }

    public function onError() {
        var_dump("error");
    }

    public function send($data) {
        $this->client->send( $data );
    }

    public function isConnected() {
        return $this->client->isConnected();
    }
}

$cli = new Client();
$cli->connect();

