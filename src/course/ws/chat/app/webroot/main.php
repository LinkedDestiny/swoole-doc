<?php
use ZPHP\ZPHP;
$rootPath = dirname(__DIR__);
require '/Users/lidanyang/project/swoole/swoole-doc/src/course/ws/chat/vendor/zphp/zphp'. DIRECTORY_SEPARATOR . 'ZPHP' . DIRECTORY_SEPARATOR . 'ZPHP.php';
ZPHP::run($rootPath);