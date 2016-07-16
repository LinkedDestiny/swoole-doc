<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/6/14
 * Time: 上午9:34
 */

include 'config.php';

class Server
{
    private $server;

    /**
     * @var PDO
     */
    private $pdo;

    public function __construct()
    {
        $this->server = new swoole_websocket_server(HOST, PORT);
        $this->server->set([
            'worker_num' => 8,
            'dispatch_mode' => 2,
            'daemonize' => 0,
        ]);

        $this->server->on('message', array($this, 'onMessage'));
        $this->server->on('open', array($this, 'update'));
        $this->server->on('workerstart', array($this, 'onWorkerStart'));
        //$this->server->on('handshake', array($this, 'user_handshake'));
        $this->server->start();
    }

    public function onWorkerStart(swoole_server $server, $worker_id)
    {
        if($worker_id == 0)
        {
            $this->server->tick(500, array($this, 'onTick'));
        }

        $this->pdo = new PDO(DATABASE_DSN, DATABASE_USER, DATABASE_PWD, array(
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES UTF8;",
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_PERSISTENT => true));
    }

    public function user_handshake(swoole_http_request $request, swoole_http_response $response)
    {
        //自定定握手规则，没有设置则用系统内置的（只支持version:13的）
        if (!isset($request->header['sec-websocket-key']))
        {
            //'Bad protocol implementation: it is not RFC6455.'
            $response->end();
            return false;
        }
        if (0 === preg_match('#^[+/0-9A-Za-z]{21}[AQgw]==$#', $request->header['sec-websocket-key'])
            || 16 !== strlen(base64_decode($request->header['sec-websocket-key']))
        )
        {
            //Header Sec-WebSocket-Key is illegal;
            $response->end();
            return false;
        }
        $key = base64_encode(sha1($request->header['sec-websocket-key']
            . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11',
            true));
        $headers = array(
            'Upgrade'               => 'websocket',
            'Connection'            => 'Upgrade',
            'Sec-WebSocket-Accept'  => $key,
            'Sec-WebSocket-Version' => '13',
            'KeepAlive'             => 'off',
        );
        foreach ($headers as $key => $val)
        {
            $response->header($key, $val);
        }
        $response->status(101);
        $response->end();
        return true;
    }

    public function onMessage(swoole_websocket_server $_server, $frame)
    {
        //$this->update();
    }

    public function update()
    {
        global $cfg_table;
        $result = array();
        foreach ($cfg_table as $table=>$fields)
        {
            $result[$table] = $this->select($table, $fields);
        }
        var_dump($result);
        foreach ($this->server->connections as $connection)
        {
            $this->server->push($connection, json_encode($result));
        }
    }

    private function select($table, $fields)
    {
        $field_list = implode(',', $fields);
        $sql = "select {$field_list} from {$table}";
        try{
            $statement = $this->pdo->prepare($sql);
            $statement->execute();

            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            if($result === false)
            {
                return array();
            }
            return $result;
        } catch (Exception $e) {
            return array();
        }
    }

    public function onTick()
    {
        $sql = "select is_update from tmp_record limit 1";
        $update = "update tmp_record set is_update=0";
        try{
            $statement = $this->pdo->prepare($sql);
            $statement->execute();

            $result = $statement->fetch(PDO::FETCH_ASSOC);
            if($result === false)
            {
                return;
            }
            if($result['is_update'] == 1)
            {
                $this->update();
            }

            $statement = $this->pdo->prepare($update);
            $statement->execute();

        } catch (Exception $e) {
            $this->pdo = new PDO(DATABASE_DSN, DATABASE_USER, DATABASE_PWD, array(
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES UTF8;",
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_PERSISTENT => true));
        }
    }
}

new Server();