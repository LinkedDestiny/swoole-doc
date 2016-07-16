<?php

namespace task\Adapter;

use task\Task;
use database\RedisFactory as RFactory;
use common\PushClient;

class Chat extends Task
{

	public function __construct() {

	}

	public function sendMessage() {
		$list = $this->params['list'];
		$data = $this->params['data'];
		foreach ($list as $fd) {
			$this->server->push( $fd , $data );
		}
	}
}