<?php 
require("db.php");

$key = 'db1';

$db = new DB();
$db->connect($config[$key]);
$i = 1;
/*
    $sql = "insert into `test` values ({$i},'pool{$i}') ";
    $db->exec($sql);
 */
 $db->beginTransaction();
for ($i = 1; $i <= 2; $i++) {
    $sql = "insert into `test` values ({$i},'pool{$i}') ";
    $db->exec($sql);
}
//$db->commit();
 $db->rollBack();
// $db->release();

 ?>
