<?php
/**
 * User: shenzhe
 * Date: 13-6-17
 * 所需扩展地址：https://github.com/matyhtf/swoole
 */


namespace Swoole\Socket;

use Swoole\Socket\IServer,
    Swoole\Socket\ICallback;

class Swoole
{
    private $client;
    private $config;
    private $serv;

    public function __construct(array $config)
    {
        if(!\extension_loaded('swoole')) {
            throw new \Exception("no swoole extension. get: https://github.com/matyhtf/swoole");
        }
        $this->config = $config;
        $this->serv = new \swoole_websocket_server($config['host'], $config['port'], $config['work_mode']);
        $this->serv->set($config);
    }

    public function setClient($client)
    {
        $this->client = $client;
        return true;
    }

    public function run()
    {
        $this->serv->on('Start', array($this->client, 'onStart'));
        $this->serv->on('Connect', array($this->client, 'onConnect'));
        $this->serv->on('Message', array($this->client, 'onReceive'));
        $this->serv->on('Close', array($this->client, 'onClose'));
        $handlerArray = array(
            'onTimer', 
            'onWorkerStart', 
            'onWorkerStop', 
            'onTask', 
            'onFinish',
        );
        foreach($handlerArray as $handler) {
            if(method_exists($this->client, $handler)) {
                $this->serv->on(\str_replace('on', '', $handler), array($this->client, $handler));
            }
        } 
        $this->serv->start();
    }
}
