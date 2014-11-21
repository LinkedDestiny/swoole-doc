<?php 
class DBServer 
{
    protected $task_worker_num;
    protected $work_num;
    protected $free_table;
    protected $map_table;       //  fd 和 task的对应关系

    protected $busy_table;
    protected $wait_queue = array(); //等待队列
    protected $wait_queue_max = 100; //等待队列的最大长度，超过后将拒绝新的请求
    protected $db_host;
    protected $db_user;
    protected $db_pwd;
    protected $db_name;
    protected $db_port; //  mysql的端口

    protected $port;    //  server监听的端口
    protected $serv;
    private $pdo = null;
    protected $request_cnt;

    function __construct(array $config) {
        $this->port = isset($config['port']) ? $config['port'] : 9500; // server监听的端口
        $this->worker_num = isset($config['worker_num']) ? $config['worker_num'] : 1;
        $this->task_worker_num = isset($config['task_worker_num']) ? $config['task_worker_num'] : 8;    
        $this->db_host = isset($config['db_host']) ? $config['db_host'] : "127.0.0.1";
        $this->db_user= isset($config['db_user']) ? $config['db_user'] : "root";
        $this->db_pwd = isset($config['db_pwd']) ? $config['db_pwd'] : "";
        $this->db_name = isset($config['db_name']) ? $config['db_name'] : "test";
        $this->db_port = isset($config['db_port']) ? $config['db_port'] : 3306;

        $this->free_table = new swoole_table(1024);
        $this->free_table->column('task_id',swoole_table::TYPE_STRING, 100);
        $this->free_table->create();

        for ($i = 0; $i < $this->task_worker_num; $i++) {
            $free[] = $i; 
        }
        $arr = array('free'=>$free,'busy'=>array());

        $this->free_table->set("task_id",array('task_id'=> json_encode($arr)));

        $this->map_table = new swoole_table(1024);      //  记录 fd 和 busy_id的对应关系
        $this->map_table->column('busy_id',swoole_table::TYPE_INT, 4);
        $this->map_table->create();
    }

    function run() {
        $this->serv = new swoole_server("127.0.0.1", $this->port);
        $this->serv->set( array(
            'worker_num'=>$this->worker_num,
            'task_worker_num' => $this->task_worker_num,
            'dispatch_mode' => 2,
            //'daemonize'=>1
        ));
        $this->serv->on('Start', array($this, 'onStart'));
        $this->serv->on('ManagerStart', array($this, 'onManagerStart'));
        $this->serv->on('WorkerStart', array($this, 'onWorkerStart'));
        $this->serv->on('Receive', array($this, 'onReceive'));

        // Task 回调的2个必须函数
        $this->serv->on('Task', array($this, 'onTask'));
        $this->serv->on('Finish', array($this, 'onFinish'));

        $this->serv->start();
    }

    public function onStart($serv) {
        echo "master_pid:{$serv->master_pid}   manager_pid:{$serv->manager_pid} \n";
        cli_set_process_title("php5 master {$serv->master_pid}");
    }

    public function onManagerStart($serv) {
        cli_set_process_title("php5 manager");
    }

    public function onWorkerStart( $serv , $worker_id) {

        // 判定是否为Task进程
        if( $worker_id >= $serv->setting['worker_num'] ) {  
            echo "----onTaskStart worker_id: {$worker_id} \n";
            cli_set_process_title("php5  task_id {$worker_id}");
        } else {
            echo "--onWorkerStart worker_id: {$worker_id} \n";
            cli_set_process_title("php5 worker {$worker_id}");
        }
    } 


    public function onConnect( $serv, $fd, $from_id ) {
        echo "Client {$fd} from:{$from_id} connect\n";
    }


    private function getFreeTaskId($fd) {

        $task = $this->free_table->get("task_id");
        $task = json_decode($task['task_id'], true);
        if ( !$this->map_table->get($fd) ) {
            echo "not have key, first \n";
            $task['busy'] = $worker_id = array_shift($task['free']);
            $this->free_table->set("task_id",array("task_id"=>json_encode($task))); 
            //$worker_id = rand(0,$this->serv->setting['task_worker_num']-1); //第一次所以可以随机
            $this->map_table->set($fd,array('busy_id'=>$worker_id));
        }

        $task = json_decode($this->free_table->get("task_id")['task_id'], true);
        $table_data = $this->map_table->get($fd);
        $worker_id = $table_data['busy_id'];
        
        return $worker_id;
    }

    /**
     * onReceive 
     * 
     * @date 2014-10-11
     * @param swoole_server $serv 
     * @param mixed $fd       TCP客户端连接的文件描述符
     * @param mixed $from_id  TCP连接所在的Reactor线程ID
     * @param mixed $data 
     * @access public
     */
    public function onReceive( swoole_server $serv, $fd, $from_id, $data ) {
        $data = json_encode(array('fd' => $fd,'send_data' => $data));
        $worker_id = $this->getFreeTaskId($fd);
        $this->serv->task($data, $worker_id);
        $this->request_cnt++;
    }

    public function process() {
        while (count($this->wait_queue) > 0) {
            $wait_data = array_shift($this->wait_queue);
            foreach ($wait_data as $row) {
                $this->doQuery($fd, $row);
            }
        }

    }

    public function doQuery($serv, $fd, $from_id, $data) {
        $rs = "";
        if (is_array($data)) {
            $func_name = $data['func_name'];
            $param = implode(',', $data['param']);
            if ($func_name == "release") {
                if ( $this->map_table->get($fd)) {
                    $this->map_table->del($fd);
                }
            } else {    //执行一般pdo方法
                if ($param != "" ) {
                    $rs = $st = $this->pdo->$func_name($param);
                } else {
                    $rs = $st = $this->pdo->$func_name();
                }

                if ( $func_name == 'query' ) {  // query 是返回结果集
                    $rs = $st->fetchAll();
                }

                if ($rs == "") {
                    $serv->send($fd, $rs);
                } else {
                    if ( is_array($rs)) {
                        $rs = json_encode($rs);
                    }
                    //$sf = $serv->send($fd, $rs, $from_id);
                    $serv->send($fd, $rs, $from_id);
                }
            }
        }
    }


    public function onClose( $serv, $fd, $from_id ) {
        //echo "Client {$fd}  from {$from_id} close connection\n";
    }

    /**
     * onTask 
     * 
     * @param mixed $serv 
     * @param mixed $task_id  是任务ID(每个worker过来的好像会自增)，由swoole扩展内自动生成，用于区分不同的任务。$task_id和$from_id组合起来才是全局唯一的，不同的worker进程投递的任务ID可能会有相同
     * @param mixed $from_id  来自于哪个worker进程
     * @param mixed $data 
     * @access public
     */
    public function onTask($serv, $task_id, $from_id, $data) {
            if ($this->pdo == null) {
        //        echo "Task create new pdo \n";
                $this->pdo = new PDO(
                    "mysql:host=localhost;port=3306;dbname=test", 
                    "root", 
                    "",
                    array(
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8';",
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_PERSISTENT => true
                    )   
                );  
            }
        $data = json_decode( $data , true );
        $send_data = json_decode( $data['send_data'], true);
        $this->doQuery($serv, $data['fd'], $from_id, $send_data);
    }

    public function onFinish($serv,$task_id, $data) {
        echo "Task Id:{$task_id} On Finish, \n";
    }

}

 ?>
