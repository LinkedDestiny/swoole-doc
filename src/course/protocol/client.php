<?php

class Client
{
	private $client;


	public function __construct() {
		$this->client = new swoole_client(SWOOLE_SOCK_TCP);
	}

	public function connect() {
		if( !$this->client->connect("127.0.0.1", 9502 , 1) ) {
			echo "Error: {$this->client->errMsg}[{$this->client->errCode}]\n";
		}

		$msg_eof = "This is a Msg\r\n";

		$i = 0;
		while( $i < 100 ) {
			$this->client->send( $msg_eof );
			$i ++;
		}
	}
}

$client = new Client();
$client->connect();

