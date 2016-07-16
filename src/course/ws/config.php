<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/6/14
 * Time: 上午9:34
 */

define('HOST', '0,0,0,0');
define('PORT', '10000');

define('DATABASE_DSN', 'mysql:host=127.0.0.1;port=3306;dbname=Test');
define('DATABASE_USER', 'root');
define('DATABASE_PWD', '');
define('DATABASE_NAME', 'Test');

$cfg_table = [
    'table1' => ['field1', 'field2','field3', 'field4','field5'],
    'table2' => ['field1', 'field2','field3', 'field4','field5'],
//    'table3' => ['field1', 'field2','field3', 'field4','field5'],
//    'table4' => ['field1', 'field2','field3', 'field4','field5'],
];