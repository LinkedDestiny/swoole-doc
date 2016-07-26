<?php
/**
 * Created by PhpStorm.
 * User: lancelot
 * Date: 16-7-9
 * Time: 下午6:15
 */

$serv = new swoole_http_server("127.0.0.1", 9501);

$serv->set([
    'worker_num' => 1
]);
$serv->on('Start' , function(){
    swoole_set_process_name('simple_route_master');
});

$serv->on('ManagerStart' , function(){
    swoole_set_process_name('simple_route_manager');
});

$serv->on('WorkerStart' , function(){
    swoole_set_process_name('simple_route_worker');
    var_dump(spl_autoload_register(function($class){
        $baseClasspath = \str_replace('\\', DIRECTORY_SEPARATOR , $class) . '.php';

        $classpath = __DIR__ . '/' . $baseClasspath;
        if (is_file($classpath)) {
            require "{$classpath}";
            return;
        }
    }));

});

$serv->on('Request', function($request, $response) {

    $path_info = explode('/',$request->server['path_info']);

    if( isset($path_info[1]) && !empty($path_info[1])) {  // ctrl
        $ctrl = 'ctrl\\' . $path_info[1];
    } else {
        $ctrl = 'ctrl\\Index';
    }
    if( isset($path_info[2] ) ) {  // method
        $action = $path_info[2];
    } else {
        $action = 'index';
    }

    $result = "Ctrl not found";
    if( class_exists($ctrl) )
    {
        $class = new $ctrl();

        $result = "Action not found";

        if( method_exists($class, $action) )
        {
            $result = $class->$action($request);
        }
    }

    $response->end($result);
});

$serv->start();
