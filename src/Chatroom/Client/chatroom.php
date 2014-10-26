<?php

class Client
{
	private $client;
	private $channel = 0;

	private $online_list;

	public function __construct() {
		$this->client = new swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);

        $this->client->on('Connect', array($this, 'onConnect'));
        $this->client->on('Receive', array($this, 'onReceive'));
        $this->client->on('Close', array($this, 'onClose'));
        $this->client->on('Error', array($this, 'onError'));
	}
	
	public function connect() {
		$fp = $this->client->connect("127.0.0.1", 8888 , 1);
		if( !$fp ) {
			echo "Error: {$fp->errMsg}[{$fp->errCode}]\n";
			return;
		}
	}

	public function onReceive( $cli, $data ) {
	
        $param = json_decode(unpack("N/a*" , $data)['1'] , true);

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
        fwrite(STDOUT, "Enter your name: ");  
		$msg = trim(fgets(STDIN));
		$data = json_encode( array(
			'json' => 'Chat',
    		'ctrl' => 'Chat',
    		'method'=> 'online',
    		'name' => $msg
    	));
    	$data = pack("Na*", strlen($data) , $data );
    	$cli->send( $data );

    	swoole_event_add(STDIN, function($fp){
    		global $cli;
			$msg = trim(fgets(STDIN));
			$data = json_encode( array(
			    'json' => 'Chat',
	    		'ctrl' => 'Chat',
				'method'=> 'send',
				'sendto'=> $this->channel,
	    		'msg' => $msg
	    	));
	    	$data = pack("Na*", strlen($data) , $data );
	    	$cli->send( $data );
		});


    }

    public function onClose( $cli) {
        echo "Client close connection\n";
    }

    public function onError() {

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
