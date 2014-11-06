<?php 
require_once "db_server.php";
require_once "config.php";

$cfg_key = $argv[1];
if (empty($config[$cfg_key])) {
    echo "请输入配置的KEY! \n";
} else {
    $server = new DBServer($config[$cfg_key]);
    $server->run();
}

 ?>
