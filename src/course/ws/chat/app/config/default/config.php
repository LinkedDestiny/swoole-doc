<?php

    return array(
    'server_mode' => 'Http',
    'app_path'=>'apps',
    'ctrl_path'=>'ctrl',
    'project'=>array(
        'name'=>'chatroom',
        'view_mode'=>'Json',
        'ctrl_name'=>'a',
        'method_name'=>'m',
    ),
    'socket' => array(
        'host' => '0.0.0.0',
        'port' => 10100,
        'daemonize' => 0,

        'work_mode' => 3,

        'worker_num' => 1,
        'dispatch_mode' => 2,

        'task_worker_num' => 1,

        'adapter' => 'Swoole',
        'server_type' => 'http',
        'protocol' => 'Json',
        'call_mode' => 'ROUTE',
        'client_class' => 'socket\\WebSocketServer',
    ),
);
