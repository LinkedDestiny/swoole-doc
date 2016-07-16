<?php
/**
 * author: shenzhe
 * Date: 13-6-17
 * route处理类
 */
namespace Swoole\Core;
use Swoole\Controller\IController,
    Swoole\Core\Factory,
    Swoole\Core\Config;

class Route
{
    public static function route($server, $socket)
    {
        $action = Config::get('ctrl_path', 'ctrl') . '\\' . $server['ctrl'];
        $class = new $action($socket);
        $before = $class->_before();
        $view = $exception = null;
        if ($before) {
            try {
                $method = $server['method'];
                if (\method_exists($class, $method)) {
                    $view = $class->$method($server);
                } else {
                    throw new \Exception("no method {$method}");
                }
            } catch (\Exception $e) {
                $exception = $e;
            }
        }
        $class->_after();
        if ($exception !== null) {
            throw $exception;
        }
        if (null === $view) {
            return null;
        }
        return json_encode($view, JSON_UNESCAPED_UNICODE);
    }
}
