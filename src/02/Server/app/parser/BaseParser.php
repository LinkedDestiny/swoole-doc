<?php
/**
 * user: Lancelot
 *
 * description: check the type of json is right or not.
 */
namespace parser;

use Swoole\Common\Route as ZRoute;
use Swoole\Core\Config;

class BaseParser implements ProtocolParser
{

	private static $JSON_TYPE = array('Chat');

	private $type;
	protected $fd;
	protected $data;
	protected $ctrl;
	protected $method;
	protected $server;

	public function parse( $_data ) {
		
		if( !is_array( $_data ) ) {
			$arr = unpack("N/a*" , $_data );
			print_r($arr);
			$this->data = \json_decode( $arr['1'] , true );
		} else {
			$this->data = $_data;
		}

		if(!is_array($this->data)) {
            return null;
        }
		if( !isset( $this->data['json'] ) ) {
			return null;
		}
		$this->type = $this->data['json'];
		if( !in_array( $this->type , self::$JSON_TYPE , true ) ) {
			return null;
		}
		
		$parser = Factory::getInstance( $this->type );
		
		return $parser;
	}

	public function setFd( $_fd ) {
		$this->fd = $_fd;
	}

	public function setServer( $_server ) {
		$this->server = $_server;
	}

	public function getServer() {
		return $this->server;
	}

	public function getCtrl() {
		return $this->ctrl;
	}

	public function getMethod() {
		return $this->method;
	}

	public function getParams() {
		return $this->data;
	}

	public function display( $model ) {
		// TODO parse Protocol
		return $model;
	}

}