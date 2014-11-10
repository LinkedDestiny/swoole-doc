<?php 
error_reporting(E_ALL);ini_set('display_errors',1);
require("db.php");

$key = 'db1';

$db = new DB();
$db->connect($config[$key]);
 $db->beginTransaction();
for ($i = 0; $i < 4; $i++) {
    $sql = "insert into `test` values ({$i},'{$i}') ";
    $db->exec($sql);
}
//$db->commit();
 $db->rollBack();
$db->release();



 ?>
