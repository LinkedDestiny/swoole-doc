<?php

namespace db;
use Swoole\Core\Factory as CFactory;

class Factory
{
    public static function getInstance($adapter = 'Chat')
    {
        $className = __NAMESPACE__ . "\\Adapter\\{$adapter}";
        return CFactory::getInstance($className);
    }
}