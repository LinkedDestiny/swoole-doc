<?php

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
	}

    public function test() {
        $this->client = new swoole_client(SWOOLE_SOCK_TCP);
        
    }
}

$client = new Client();
$client->connect();

