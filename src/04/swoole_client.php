<?php

class Client
{
	private $client;
  
	public function __construct() {
    $this->client = new swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);

    $this->client->on('Connect', array($this, 'onConnect'));
    $this->client->on('Receive', array($this, 'onReceive'));
    $this->client->on('Close', array($this, 'onClose'));
    $this->client->on('Error', array($this, 'onError'));
	}
	
	public function connect($host , $port) {
		$fp = $this->client->connect( $host , $port , 1);
		if( !$fp ) {
			echo "Error: {$fp->errMsg}[{$fp->errCode}]\n";
			return;
		}
	}

	public function onReceive( $cli, $data ) {
    echo "Get Message From Server: {$data}\n";
  }

  public function onConnect( $cli) {
    fwrite(STDOUT, "Enter Msg:");
    swoole_event_add(STDIN, function($fp){
		    global $cli;
        fwrite(STDOUT, "Enter Msg:");
        $msg = trim(fgets(STDIN));
  	    $cli->send( $msg );
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
$cli->connect($argv[1] , $argv[2] );
