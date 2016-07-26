<?php
use Swoole\Entrance;
define('NOW_TIME', time());
$config =  array(
    'server_mode' => 'Socket',
    'app_path' => 'app',
    'ctrl_path' => 'ctrl',
    'socket' => array(
        'host' => '0.0.0.0', //socket 监听ip
        'port' => 10100, //socket 监听端口
        'socket_adapter' => 'Swoole', //socket 驱动模块
        'client_class' => 'socket\\Server', //socket 回调类
        // swoole server config
        'daemonize' => 0, //是否开启守护进程
        'work_mode' => 3,
        'worker_num' => 8,
        'max_request' => 10000,
        'dispatch_mode' => 2,
        'task_worker_num' => 8,
        'open_length_check' => true,
        'package_length_offset' => 0,
        'package_body_offset' => 4,
        'package_length_type' => 'N'
    ),
);
$publicConfig = array( 
    'redis.php'
);
foreach($publicConfig as $file) {
    $file = Entrance::getRootPath() . DS . 'config' . DS . 'public'. DS . $file;
    $config += include "{$file}";
}

return $config;
