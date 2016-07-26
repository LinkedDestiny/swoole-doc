<?php

namespace task;

abstract class Task
{
	protected $params;
	protected $server;

	public function setParams( $_params ) {
		$this->params = $_params;
	}

	public function setServer( $_server ) {
		$this->server = $_server;
	}
}