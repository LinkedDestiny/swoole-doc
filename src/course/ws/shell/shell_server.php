<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/7/15
 * Time: 上午10:30
 */
include '../config.php';

class Server
{
    private $server;

    private $process;

    private $async_process = [];

    public function __construct()
    {
        $this->server = new swoole_websocket_server(HOST, PORT);
        $this->server->set([
            'worker_num' => 2,
            'dispatch_mode' => 2,
            'daemonize' => 0,
        ]);

        $this->server->on('message', array($this, 'onMessage'));
        $this->server->on('request', array($this, 'onRequest'));
        $this->server->on('workerstart', array($this, 'onWorkerStart'));

        $this->process = new swoole_process(array($this, 'onProcess') , true);
        $this->server->addProcess($this->process);
        $this->server->start();
        //必须为false，非阻塞模式
    }

    public function onWorkerStart(swoole_server $server, $worker_id)
    {
        swoole_process::signal(SIGCHLD, function($sig) {
            //必须为false，非阻塞模式
            while($ret =  swoole_process::wait(false)) {
                echo "PID={$ret['pid']}\n";
            }
        });

    }

    public function onMessage(swoole_websocket_server $server, $frame)
    {
        var_dump($frame->data);
        $data = json_decode($frame->data, true);
        var_dump($data);
        $cmd = $data['cmd'];

        $is_block = isset( $data['is_block'] ) ? $data['is_block'] : 0;
        if( $is_block ) {
            if( isset($this->async_process[$frame->fd]))
            {
                $process = $this->async_process[$frame->fd];
            } else {
                $process = new swoole_process(array($this, 'onTmpProcess') , true , 2);
                $process->start();
                $this->async_process[$frame->fd] = $process;
                swoole_event_add($process->pipe, function()use($process, $frame){
                    $data  = $process->read();
                    var_dump($data);
                    $this->server->push($frame->fd, $data);
                });
            }

            $process->write($cmd);
            sleep(1);
        } else {
            $this->process->write($cmd);
            $data  = $this->process->read();
            $this->server->push($frame->fd, $data);
        }


    }

    public function onRequest(swoole_http_request $request, swoole_http_response $response)
    {
        $path_info = $request->server['path_info'];
        if($path_info === '/shell.html')
        {
            $response->end(file_get_contents('shell.html'));
        }
        foreach ($this->server->connections as $connection)
        {
            $connection_info = $server->connection_info($connection);
            if( isset($connection_info['websocket_status']) 
                && $connection_info['websocket_status'] == WEBSOCKET_STATUS_FRAME )
            {
                // ws connection
                $this->server->push($connection, json_encode($result));
            }
            
        }
    }

    public function onProcess(swoole_process $worker)
    {
        while(true)
        {
            $cmd  = $worker->read();
            if($cmd == 'exit')
            {
                $worker->exit();
                break;
            }
            passthru($cmd);
        }
    }

    public function onTmpProcess(swoole_process $worker)
    {
        $cmd  = $worker->read();
        $handle = popen($cmd , 'r');

        swoole_event_add($worker->pipe, function()use($worker, $handle){
            $cmd  = $worker->read();
            if($cmd == 'exit')
            {
                $worker->exit();
            }
            fwrite($handle,$cmd);
        });


        while(!feof($handle)) {
            $buffer = fread($handle, 18192);
            echo $buffer;
        }

    }
}

new Server();