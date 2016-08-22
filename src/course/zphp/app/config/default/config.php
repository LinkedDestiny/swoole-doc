<?php

    return array(
        'server_mode' => 'Socket',
        'app_path'=>'apps',
        'ctrl_path'=>'ctrl',
        'project'=>array(
            'name'=>'zphp-test',                 
        	'view_mode'=>'Json',
        	'ctrl_name'=>'a',				
        	'method_name'=>'m',				
        ),
        'socket' => array(
            'host' => '0.0.0.0',
            'port' => 8992,
            'daemonize' => 0,
            'worker_num' => 4,
            'work_mode' => 3,
            'dispatch_mode' => 2,

            // swoole set

            'adapter' => 'Swoole',
            'server_type' => 'http',
            'client_class' => 'socket\\HttpServer',
            'protocol' => 'Json',

            'start_hook' => 'socket\\HttpServer::start_hook',
            'start_hook_args' => true
        ),
        'port' => array(
            'host' => '0.0.0.0',
            'port' => 8993,
            
        )
    );
