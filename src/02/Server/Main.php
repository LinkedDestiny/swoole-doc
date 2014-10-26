<?php

use Swoole\Entrance;

$rootPath = __DIR__;
require $rootPath . DIRECTORY_SEPARATOR . 'Swoole' . DIRECTORY_SEPARATOR . 'Entrance.php';

Entrance::run($rootPath);