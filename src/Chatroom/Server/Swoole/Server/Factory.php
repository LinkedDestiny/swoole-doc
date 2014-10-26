<?php
/**
 * User: shenzhe
 * Date: 13-6-17
 */
namespace Swoole\Server;
use Swoole\Core\Factory as CFactory;

class Factory
{
    public static function getInstance($adapter = 'Http')
    {
        $className = __NAMESPACE__ . "\\{$adapter}";
        return CFactory::getInstance($className);
    }
}