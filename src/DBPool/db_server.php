<?php 
class DBServer 
{
    protected $pool_size;
    protected $free_pool = array();
    protected $busy_pool = array();
    protected $wait_queue = array(); //等待队列
    protected $wait_queue_max = 100; //等待队列的最大长度，超过后将拒绝新的请求
    protected $db_host;
    protected $db_user;
    protected $db_pwd;
    protected $db_name;
    protected $db_port; //  mysql的端口

    protected $port;    //  server监听的端口
    protected $serv;
    private $map;       //  fd 和 task的对应关系
    private $pdo;
    private $table;


    function __construct(array $config) {
        $this->port = isset($config['port']) ? $config['port'] : 9500; // server监听的端口
        $this->pool_size = isset($config['pool_size']) ? $config['pool_size'] : 20;    
        $this->db_host = isset($config['db_host']) ? $config['db_host'] : "127.0.0.1";
        $this->db_user= isset($config['db_user']) ? $config['db_user'] : "root";
        $this->db_pwd = isset($config['db_pwd']) ? $config['db_pwd'] : "";
        $this->db_name = isset($config['db_name']) ? $config['db_name'] : "test";
        $this->db_port = isset($config['db_port']) ? $config['db_port'] : 3306;

        $this->table = new swoole_table(1024);
        $this->table->column('worker_id',swoole_table::TYPE_INT, 4);
        $this->table->create();
    }

    function run() {
        $this->serv = new swoole_server("127.0.0.1", $this->port);
        $this->serv->set( array(
            'worker_num'=>1,
            'task_worker_num' => 8,
            'dispatch_mode' => 2,
            //'daemonize'=>1
        ));
        $this->serv->on('Start', array($this, 'onStart'));
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

    public function onWorkerStart( $serv , $worker_id) {
        echo "onWorkerStart\n";
        cli_set_process_title("php5 worker {$worker_id}");
        // 判定是否为Task Worker进程
        if( $worker_id >= $serv->setting['worker_num'] ) { 
            cli_set_process_title("php5 task {$worker_id}");
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
    } 


    public function onConnect( $serv, $fd, $from_id ) {
        echo "Client {$fd} from:{$from_id} connect\n";
    }

    public function onReceive( swoole_server $serv, $fd, $from_id, $data ) {
        $data = array('fd' => $fd,'send_data' => $data);

        if ( !$this->table->get($fd) ) {
            echo "not have key \n";
            $worker_id = rand(0,$this->serv->setting['task_worker_num']-1);
            $this->table->set($fd,array('worker_id'=>$worker_id));
        }
        $table_data = $this->table->get($fd);
        $worker_id = $table_data['worker_id'];
        echo "receive  worker_id:{$worker_id} ".json_encode($table_data)."\n";
        $this->serv->task(json_encode($data), $worker_id);
    }

    public function doQuery($fd, $data) {
        $rs = "";

        if (is_array($data)) {
            $cur_key = $fd;
            $func_name = $data['func_name'];
            $param = implode(',', $data['param']);

            if ($func_name == "release") {
                echo $rs = "doQuery: release \n";
                $this->table->del($fd);
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
                    //echo $rs = "doQuery: data:: isempty \n";
                    $this->serv->send($fd, $rs);
                } else {
                    //echo "doQuery: func_name: {$func_name} data:: ".json_encode($rs)."\n";
                    $this->serv->send($fd, json_encode($rs));
                }
            }
        }
    }


    public function onClose( $serv, $fd, $from_id ) {
        //echo "Client {$fd}  from {$from_id} close connection\n";
    }

    public function onTask($serv, $task_id, $from_id, $data) {
        $data = json_decode( $data , true );
        $send_data = json_decode( $data['send_data'], true);
        echo "Server On Task, task_id:{$task_id} from_id: {$from_id} ".json_encode($data)." \n";
        $this->doQuery($data['fd'], $send_data);
        //$serv->send($data['fd'], " On Task");

        /*
        $sql = json_decode( $data , true );
        
        $statement = $this->pdo->prepare($sql['sql']);
        $statement->execute($sql['param']);     
        $serv->send( $sql['fd'],"Insert");
         */
        return true;
    }

    public function onFinish($serv,$task_id, $data) {
        //echo "Task Id:{$task_id} On Finish,".json_encode($data)." \n";
    }

}

 ?>
