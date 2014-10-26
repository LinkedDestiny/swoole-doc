<?php

namespace parser;

interface ProtocolParser
{
	public function setFd( $fd );

	public function parse( $_data );

	public function setServer( $_server );

	public function getServer();

	public function getCtrl();

	public function getMethod();

	public function getParams();

	public function display($model);
}