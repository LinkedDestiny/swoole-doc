<?php 
error_reporting(E_ALL);ini_set('display_errors',1);
require("db.php");

$key = 'db1';

$db = new DB();
$db->connect($config[$key]);
for ($i = 0; $i <10000; $i++) {
    $rs = $db->query("select * from test;");
}
$db->release();



 ?>
