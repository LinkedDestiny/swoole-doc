<?php
require_once "../vendor/autoload.php";

use \Hprose\Future;
use \Hprose\Swoole\Client;
use \Hprose\Http\Client as HttpClient;

$test = new Client("http://127.0.0.1:9501");

$http = new HttpClient("http://127.0.0.1:9501", false);


var_dump($http->sum(1,2));
$test->sum(1,2)
    ->then(function($result) use ($test) {
        var_dump($result);
        //swoole_event_exit();
    });
$test->getResult("show tables")
    ->then(function($result) use ($test) {
        var_dump($result);
        swoole_event_exit();
    });

 $test->subscribe($topic, function($msg){
 		
 })