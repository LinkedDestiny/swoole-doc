<?php
/**
 * Created by PhpStorm.
 * User: lancelot
 * Date: 16-8-6
 * Time: 下午4:45
 */

require_once __DIR__."/../SwooleServer.php";

class Api
{
    public function sum($a, $b)
    {
        return $a + $b;
    }

    public function sub($a, $b)
    {
        return $a - $b;
    }

    public function getResult($sql)
    {
        $statement = SwooleServer::$PDO->prepare($sql);
        $statement->execute();
        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function publish($topic, $id, $msg, $offset)
    {
        // offset , msg
        $serv->push($topic, $id, $msg);
    }   
}