<?php
namespace socket;

use ZPHP\Core\Config;
use ZPHP\Protocol;
use ZPHP\Core;
use ZPHP\Socket\Callback\SwooleWebSocket;

class WebSocketServer extends SwooleWebSocket
{


    public function onMessage($server, $frame)
    {
        $content = "";
        do
        {
            Protocol\Request::parse($frame->data);
            $content = Core\Route::route();
        } while(0);
        Protocol\Response::getResponse()->end($content);
    }
}