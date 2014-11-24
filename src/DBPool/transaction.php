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
//try{
 $db->beginTransaction();
for ($i = 1; $i <= 3; $i++) {
    $sql = "insert into `test` values ({$i},'pool{$i}') ";
    $db->exec($sql);
}
//$db->commit();
 $db->rollBack();
/*
} catch (Exception $e) {
    print $e->getMessage();
    exit();
}
 */
// $db->release();

 ?>
