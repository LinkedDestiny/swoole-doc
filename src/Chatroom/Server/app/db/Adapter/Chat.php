<?php

namespace db\Adapter;
use db\Redis as ZRedis;
use Swoole\Core\Config as ZConfig;

class Chat
{
	const USER_INFO = 'hash_user_';
	const FD_CHANNEL = 'str_fd_channel_';
	const CHANNEL_FD = "set_channel_";
	const ONLINE = "set_online";
	const DEFAULT_CHANNEL = 0;

	private $redis;

	public function __construct() {
		if( empty( $this->redis ) ) {
            $this->redis = ZRedis::getInstance( ZConfig::get('redis') );
            $db = ZConfig::getField('redis', 'db', 0);
            if(!empty($db)) {
                $this->redis->select($db);
            }
        }
	}

	public function online( $fd ) {
		if( $this->ping() ) {
			$key = self::FD_CHANNEL . $fd;
			$channel_key = self::CHANNEL_FD . self::DEFAULT_CHANNEL;
			$this->redis->set( $key , self::DEFAULT_CHANNEL );
			$this->redis->sadd( $channel_key , $fd );
			$this->redis->sadd( self::ONLINE , $fd );
			
			if( !$this->enterChannel( $fd , self::DEFAULT_CHANNEL) ) {
				return false;
			}
			return true;
		}
		else {
			return false;
		}
	}

	public function offline( $fd ) {
		if( $this->ping() ) {
			$key = self::FD_CHANNEL . $fd;

			if( !$this->leftChannel( $fd ) ) {
				return false;
			}
			$channel = $this->redis->get($key);
			$channel_key = self::CHANNEL_FD . $channel;
			$this->redis->del( $key );
			$this->redis->srem( $channel_key , $fd );
			$this->redis->srem( self::ONLINE , $fd );

			return true;
		}
		else {
			return false;
		}
	}

	public function setUserInfo( $fd , $info ) {
		if( $this->ping() && $this->checkInfo($info) ) {
			$key = self::USER_INFO . $fd;
			foreach ($info as $field => $value) {
				$this->redis->hset( $key , $field , $value );
			}
			return true;
		}
		else {
			return false;
		}
	}

	public function getUserInfo( $fd , $field ) {
		if( $this->ping() ) {
			$key = self::USER_INFO . $fd;
			return $this->redis->hget( $key , $field );
		}
		else {
			return "";
		}
	}

	public function enterChannel( $fd , $channel ) {
		if( $this->ping() ) {
			if($this->leftChannel($fd)) {
				$new_channel_key = self::CHANNEL_FD . $channel;
				$this->redis->sadd( $new_channel_key , $fd );
				return true;
			}
		}
		return false;
	}

	public function leftChannel( $fd ) {
		if( $this->ping() ) {
			$key = self::FD_CHANNEL . $fd;
			$old_channel = $this->redis->get( $key );
			$old_channel_key = self::CHANNEL_FD . $old_channel;
			
			$this->redis->srem( $old_channel_key , $fd );
			return true;
		}
		else {
			return false;
		}
	}

	public function getFdList( $channel = self::DEFAULT_CHANNEL) {
		if( $this->ping() ) {
			$channel_key = self::CHANNEL_FD . $channel;
			return $this->redis->smembers( $channel_key );
		} else {
			return array();
		}
	}

	public function getChannel( $fd ) {
		if( $this->ping() ) {
			$key = self::FD_CHANNEL . $fd;
			return $this->redis->get( $key );;
		}
		else {
			return -1;
		}
	}

	public function getOnlineList() {
	if( $this->ping() ) {
			return $this->redis->smembers( self::ONLINE );
		} else {
			return array();
		}
	}

	private function ping() {
		$result = $this->redis->ping();
		return $result == "+PONG";
	}

	private function checkInfo( $info ) {
		if( !isset( $info['name'] ) ) {
			return false;
		}
		return true;
	}
}