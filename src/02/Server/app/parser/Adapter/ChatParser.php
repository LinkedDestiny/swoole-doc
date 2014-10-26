<?php

namespace parser\Adapter;

use parser\BaseParser;

class ChatParser extends BaseParser
{		
	public static $PARAMS = array();

	public function parse( $_data ) {
		if( !is_array( $_data ) ) {
			$arr = unpack("N/a*" , $_data );

			$this->data = \json_decode( $arr['1'] , true );
		} else {
			$this->data = $_data;
		}
		
		if( !isset( $this->data['ctrl'] ) ) {
			return false;
		}
		if( !isset( $this->data['method'] ) ) {
			return false;
		}
		$this->ctrl = \str_replace('/', '\\',$this->data['ctrl'] );
		$this->method = $this->data['method'];
		$this->data['fd'] = $this->fd;
		foreach ( self::$PARAMS as $field ) {
			if( !isset( $this->data[ $field ] ) ) {
				return false;
			}
		}

		return true;
	}
}