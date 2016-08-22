<?php
namespace socket;

use ZPHP\Socket\Callback\SwooleHttp;
use ZPHP\Core\Config as ZConfig;
use ZPHP\Common\Formater;
use ZPHP\Common\Log;
use ZPHP\Protocol;
use ZPHP\Core;

class HttpServer extends SwooleHttp
{

    public function onStart()
    {
        $server = func_get_args()[0];
        parent::onStart($server);
        echo 'server start, swoole version: ' . SWOOLE_VERSION . PHP_EOL;
    }

    public function onRequest($request, $reponse)
    {
        $content = "";
        do
        {
            $path_info = explode ("/", $request->server['path_info']);
            $ctrl = $path_info[1];
            $method = $path_info[2];

            if( isset($request->post) ){
                Protocol\Request::parse($request->post);
            } else {
                Protocol\Request::parse($request->rawContent());
            }

            Protocol\Request::setCtrl($ctrl);
            Protocol\Request::setMethod($method);
            Protocol\Request::setViewMode('Json');
            Protocol\Request::setSocket($this->serv);

            //\ob_start();
            $content = Core\Route::route();
            //$content = \ob_get_contents();
            //\ob_end_clean();
        } while(0);
        $reponse->end($content);
    }

    public static function start_hook($server)
    {
        Config::get('port');
        $port = $server->listen();

        // 3rd lib
        
    }
}