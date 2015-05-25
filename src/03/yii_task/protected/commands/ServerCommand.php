<?php
class ServerCommand extends CConsoleCommand
{
    public $config = array(
       'worker_num' => 1,
       'task_worker_num' => 2,
       'task_ipc_mode' => 1,
       'heartbeat_check_interval' => 300,
       'heartbeat_idle_time'      => 300,
    );

    // 接收每一种event请求的个数
    public static $cnt = array();

    // 各种类型event的基数   做取模后 相加 如email是5个
    public static $event_base = array();

    public static $q_config = array();
    public $num = 0;


    public function actionRun()
    {
        $serv = new swoole_server("127.0.0.1", 9550);
        self::$q_config = require('config.php');
        $task_num = 0;
        foreach (self::$q_config as $key => $val) {
            self::$event_base[$key] = $task_num;
            self::$cnt[$key] = 0;
            $task_num += $val;
        }
        $this->config['task_worker_num'] = $task_num;
        $serv->set($this->config);

        $serv->on('Start', array($this, 'my_onStart'));
        $serv->on('Connect', array($this, 'my_onConnect'));
        $serv->on('Receive', array($this, 'my_onReceive'));
        $serv->on('Close', array($this, 'my_onClose'));
        $serv->on('Shutdown', array($this, 'my_onShutdown'));
        $serv->on('Timer', array($this, 'my_onTimer'));
        $serv->on('WorkerStart', array($this, 'my_onWorkerStart'));
        $serv->on('WorkerStop', array($this, 'my_onWorkerStop'));
        $serv->on('Task', array($this, 'my_onTask'));
        $serv->on('Finish', array($this, 'my_onFinish'));
        $serv->on('WorkerError', array($this, 'my_onWorkerError'));
        $serv->start();
    }

    function my_onStart($serv)
    {
        $redis = new Redis();
        $redis->pconnect('127.0.0.1', 6379);
        $redis->flushAll();
        $work_arr = array();

        for ($i = 0; $i < $serv->setting['task_worker_num']; $i++) {
            $redis->lpush('free', $i);
            $work_arr[] = $i;
        }

        echo "MasterPid={$serv->master_pid}|Manager_pid={$serv->manager_pid}\n";
        echo "Server: start.Swoole version is [".SWOOLE_VERSION."]\n";
    }

    function my_onShutdown($serv)
    {
        echo "Server: onShutdown\n";
    }

    function my_onTimer($serv, $interval)
    {
        echo "Server:Timer Call.Interval=$interval\n";
    }

    function my_onClose($serv, $fd, $from_id)
    {
        //echo "Client: fd=$fd is closed.\n";
    }

    function my_onConnect($serv, $fd, $from_id)
    {
        //throw new Exception("hello world");
    //  echo "Client:Connect.\n";
    }

    function my_onWorkerStop($serv, $worker_id)
    {
        echo "WorkerStop[$worker_id]|pid=".posix_getpid().".\n";
    }

    function my_onWorkerStart($serv, $worker_id)
    {
        global $argv;
        $pid = getmypid();
        if($worker_id >= $serv->setting['worker_num']) {
            echo "php {$argv[0]} task worker {$pid}\n";
            swoole_set_process_name("php {$argv[0]} task worker");
        } else {
            echo "php {$argv[0]} event worker {$pid}\n";
            swoole_set_process_name("php {$argv[0]} event worker");
        }
        echo "WorkerStart|MasterPid={$serv->master_pid}|Manager_pid={$serv->manager_pid}|WorkerId=$worker_id | CurPid:{$pid} \n";
        //$serv->addtimer(500); //500ms
    }

    // 获得project_id 和 task_id的对应关系
    function getMap($pid = 0){
        if ($pid <= 0) return ;
        $redis = new Redis();
        $redis->pconnect('127.0.0.1', 6379);
        $map = $redis->get('map');
        if (!$map) {
            $tid = $redis->lpop('free');
            $map = array($pid => $tid);
            $redis->set( 'map' , json_encode($map) );
        } else {
            $map = json_decode($map, true);
            $tid = $map[$pid];
        }
    //var_dump('map', $map, 'tid', $tid,'pid:', $pid);
        return $tid;
    }

    function getTaskId($type)
    {
        self::$cnt[$type]++;
        $mod = self::$cnt[$type] % self::$q_config[$type];
        $tid = $mod + self::$event_base[$type];
        return $tid;
    }

    function my_onReceive(swoole_server $serv, $fd, $from_id, $rdata)
    {
        //echo "receive data: $rdata \n";
        $data = json_decode($rdata, true);
        if (isset($data['class'])) {
            $type = $data['class'];
            if (!isset(self::$cnt[$type])) {
                // 没有专属处理进程，则使用公共进程
                $type = 'Common';
            }

            $tid = $this->getTaskId($type);

            //var_dump(self::$cnt, self::$event_base, $tid, $data);
            $data['fd'] = $fd;
            $rs = $serv->task($data, $tid);
            //$rs = $serv->taskwait($data,0.5, $tid);
//            $serv->send($fd, PHP_EOL .$rs);
            return ;
        } else {
            echo "没有相应 事件处理类, 报警\n";
        }
        return;

    }

    function my_onTask(swoole_server $serv, $task_id, $from_id, $data)
    {
        $dir = dirname(__DIR__).'/event/';
        $class = $data['class'].'Event';
        include_once($dir.$class.'.php');

        $obj = new $class();
        $rs = $obj->run($data);
        $rs = array('rs'=> $rs, 'fd' => $data['fd']);
        return $rs;
    }

    function my_onFinish(swoole_server $serv, $task_id, $data)
    {
        $is_send = 0;
        $rs = $data['rs'];
        if (is_array($data['rs'])) {
            // 失败了
            if ($rs['err_no'] > 0) {
                $tid = $this->getTaskId('Retry');
                echo "faild tid: {$tid} \n";

                $task_data['class'] = $rs['class'];
                $task_data['param'] = $rs['param'];
                $task_data['fd'] = $data['fd'];
                $task_data['retry_cnt'] = $rs['retry_cnt'];

                if ($rs['retry_cnt'] < 3) {
                    $serv->task($task_data, $tid);
                } else {
                    $is_send = 1;
                    echo "超过3次，需要报警! \n";
                }
            } else {
                // 第一次就成功了。
                $is_send = 1;
            }

            $rs = json_encode($data['rs']);
        } else {
            $is_send = 1;
        }

        if ($is_send > 0) {
            $serv->send($data['fd'], $rs);
        }
    }

    function my_onWorkerError(swoole_server $serv, $data)
    {
        echo "worker abnormal exit. WorkerId=$worker_id|Pid=$worker_pid|ExitCode=$exit_code\n";
    }


}
