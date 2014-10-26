<?php
/**
 * User: shenzhe
 * Date: 13-6-17
 */


namespace Swoole\Server;

use Swoole\Socket\Factory as SFactory;
use Swoole\Core\Config;
use Swoole\Core\Factory as CFactory;

class Socket
{
    public function run()
    {
        $config = Config::get('socket');
        if (empty($config)) {
            throw new \Exception("socket config empty");
        }
        $socket = SFactory::getInstance($config['socket_adapter'], $config);
        $client = CFactory::getInstance($config['client_class']);
        $socket->setClient($client);
        $socket->run();
    }
}
