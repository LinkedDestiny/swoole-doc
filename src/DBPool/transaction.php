<?php 
require("db.php");

$key = 'db1';

$db = new DB();
$db->connect($config[$key]);
// $db->beginTransaction();
for ($i = 1; $i <= 3; $i++) {
    $sql = "insert into `test` values ({$i},'pool{$i}') ";
    $db->exec($sql);
}
//$db->commit();
// $db->rollBack();
//$db->release();



 ?>
