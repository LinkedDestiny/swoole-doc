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
    public static function route($server)
    {
        $action = Config::get('ctrl_path', 'ctrl') . '\\' . $server->getCtrl();
        $class = Factory::getInstance($action);
        if (!($class instanceof IController)) {
            throw new \Exception("ctrl error");
        }
        $class->setServer($server);
        $before = $class->_before();
        $view = $exception = null;
        if ($before) {
            try {
                $method = $server->getMethod();
                if (\method_exists($class, $method)) {
                    $view = $class->$method();
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
            return;
        }
        return $server->display($view);
    }
}
