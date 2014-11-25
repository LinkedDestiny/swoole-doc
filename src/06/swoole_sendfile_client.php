<?php

class Client
{
	private $client;
	const DOWNLOAD = 1;
	const UPLOAD = 2;
  
	public function __construct() {
    $this->client = new swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);

    $this->client->on('Connect', array($this, 'onConnect'));
    $this->client->on('Receive', array($this, 'onReceive'));
    $this->client->on('Close', array($this, 'onClose'));
    $this->client->on('Error', array($this, 'onError'));
	}
	
	public function connect() {
		$fp = $this->client->connect("127.0.0.1", 9501 , 1);
		if( !$fp ) {
			echo "Error: {$fp->errMsg}[{$fp->errCode}]\n";
			return;
		}
	}

	public function onReceive( $cli, $data ) {
	    echo "Get Message From Server: {$data}\n";
	}

  public function onConnect( $cli) {
  	fwrite(STDOUT, "Enter cmd:");
    swoole_event_add(STDIN, function($fp){

		global $cli;
        $cmd = trim(fgets(STDIN));
        if( is_numeric( $cmd ) ) {
        	$filename = "Test.txt";
        	$i = intval( $cmd );
        	if( $i == Client::UPLOAD) {
        		$cli->sendfile( $filename );
        	}
        	else if($i == Client::DOWNLOAD) {
				$cli->send( Client::DOWNLOAD , $filename );
        	}
        }
    });
  }

  public function onClose( $cli) {
      echo "Client close connection\n";
  }

  public function onError() {

  }

  public function send( $cmd , $filename) {
  	$data = pack("NN" , strlen($filename) , $cmd ). $filename;
  	$this->client->send( $data );
  }

  public function sendfile( $filename ) {
  	$this->client->sendfile( $filename );
  }
}

$cli = new Client();
$cli->connect();
