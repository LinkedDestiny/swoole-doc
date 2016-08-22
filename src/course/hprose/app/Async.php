<?php
/**
 * Created by PhpStorm.
 * User: lancelot
 * Date: 16-8-6
 * Time: 下午4:02
 */
require_once "../vendor/autoload.php";

use Hprose\Future;

$p2 = Future\sync(function() {
    sleep(1);
    return array(Future\value(1), Future\value(2));
});
$p2->then(function($value) {
    var_dump($value);
});
var_dump("hello");