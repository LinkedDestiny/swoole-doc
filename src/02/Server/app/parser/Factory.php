<?php
/**
 * User: Lancelot
 */
namespace parser;

use Swoole\Core\Factory as CFactory;

class Factory
{
	public static function getInstance( $adapter = 'Base' ) {
		if( 'Base' == $adapter )
			$class_name = __NAMESPACE__ . "\\{$adapter}Parser";
		else
			$class_name = __NAMESPACE__ . "\\Adapter\\{$adapter}Parser";
		return CFactory::getInstance( $class_name );
	}
}