<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);
class DBServer 
{
    protected $task_worker_num;
    protected $work_num;
    protected $free_table;
    protected $map_table;       //  fd 和 task的对应关系

    protected $busy_table;
    protected $wait_queue = array(); //等待队列
    //protected $wait_queue_max = 100; //等待队列的最大长度，超过后将拒绝新的请求
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
        $this->free_table->column('task_id',swoole_table::TYPE_STRING, 1000);
        $this->free_table->create();
        for ($i = 0; $i < $this->task_worker_num; $i++) {
            $free[] = $i; 
        }
        //$arr = array('free'=>$free,'busy'=>array());
        $this->free_table->set("task_id",array('task_id'=> json_encode($free)));

        $this->map_table = new swoole_table(1024);      //  记录 fd 和 busy_id的对应关系
        $this->map_table->column('busy_id',swoole_table::TYPE_STRING, 1000);
        $this->map_table->create();
        $this->map_table->set("busy_id",array('busy_id'=> json_encode(array())));
    }

    function run() {
        $this->serv = new swoole_server("127.0.0.1", $this->port);
        $this->serv->set( array(
            'worker_num'=>$this->worker_num,
            'task_worker_num' => $this->task_worker_num,
            'task_max_request' => 0,
            'max_request' => 0,
            'log_file' => '/home/dev/git/swoole-doc/src/DBPool/swoole.log',
            'dispatch_mode' => 2,
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
        //echo "master_pid:{$serv->master_pid}   manager_pid:{$serv->manager_pid} \n";
        cli_set_process_title("php5 master {$serv->master_pid}");
    }

    public function onManagerStart($serv) {
        cli_set_process_title("php5 manager");
    }

    public function onWorkerStart( $serv , $worker_id) {

        // 判定是否为Task进程
        if( $worker_id >= $serv->setting['worker_num'] ) {  
            //echo "----onTaskStart worker_id: {$worker_id} \n";
            cli_set_process_title("php5  task_id {$worker_id}");
        } else {
            //echo "--onWorkerStart worker_id: {$worker_id} \n";
            cli_set_process_title("php5 worker {$worker_id}");
        }
    } 


    public function onConnect( $serv, $fd, $from_id ) {
        //echo "Client {$fd} from:{$from_id} connect\n";
    }


    private function getFreeTaskId($fd) {
        $busy_arr = $this->_getBusy();
        ////echo "map_cnt:".count($this->map_table)." free_cnt:".count($this->free_table)." task_worker_num:{$this->serv->setting['task_worker_num']}\n";
        if ( !isset($busy_arr[$fd]) ) {    //  如果不在正使用 修改map 和 free
            if (count($busy_arr) == $this->serv->setting['task_worker_num']) { // 已经没有空闲链接了
                return -1;
            }
            ////echo "current session not have connection, first \n";
            //$task['busy'] = $worker_id = array_shift($task['free']);
            
            $worker_id = $this->_popFree();
        //var_dump($busy_arr);
            $this->_addBusy($fd, $worker_id);
        }
       /*
         else {
            //echo " current session fd:{$fd} have connection \n";
        }
        */

        //$task = json_decode($this->free_table->get("task_id")['task_id'], true);
        $worker_id = $this->_getBusy($fd);
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
        //echo "----------{$worker_id}---------\n";
        // 代表没有可用连接 进入等待队列
        if ($worker_id == -1 ) {    // task_worker_id是从0开始的 所以不能返回0作为判断
            $this->wait_queue[] = array('fd'=>$fd, 'data' =>$data);
            //echo "fd:{$fd} from_id:{$from_id} is busy \n";
            //$serv->send($fd, '-1' , $from_id);
        } else {    // 获得可用连接
            $this->serv->task($data, $worker_id);
        }
        $this->request_cnt++;
        $free = $this->_getFreeArr();
        //echo "cnt:{$this->request_cnt} fd:{$fd} from_id:{$from_id} data:".json_encode($data)."\n";
        $cur_link = $this->_getBusy($fd);
        $busy = $this->_getBusy();
        //echo "----onReceive cur_link:{$cur_link}  follow is free and busy wait_queue\n";
        //var_dump($free, $busy, $this->wait_queue);
        //$this->process();
    }

    public function process() {
        while (count($this->wait_queue) > 0) {
            $wait_data = array_shift($this->wait_queue);
            do {
                $worker_id = $this->getFreeTaskId($wait_data['fd']);
            } while ($worker_id < 0);
            $this->serv->task($wait_data['data'], $worker_id);
        }
    }

    public function doQuery($serv, $fd, $from_id, $data) {
        $rs = "";
        if (is_array($data)) {
            $func_name = $data['func_name'];
            $param = implode(',', $data['param']);
            if ($func_name == "release") {
                $current_worker_id = $this->_getBusy($fd);
                if ($current_worker_id !== false) {
                    //echo "---- doQuery release worker_id:{$current_worker_id} busy_cnt:".count($this->map_table)." before \n";
                    $free = $this->_getFreeArr();
                    $this->_addFree($current_worker_id);
//var_dump($free);
                    $this->_delBusy($fd);
                    //echo "---- doQuery release worker_id:{$current_worker_id} busy_cnt:".count($this->map_table)." after \n";
                }
            } else {    //执行一般pdo方法
                if ($param != "" ) {
                    //echo "---- doQuery func:{$func_name} busy_cnt:".count($this->map_table)." before \n";
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
        ////echo "Client {$fd}  from {$from_id} close connection\n";
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
   //         //echo "Task create new pdo \n";
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
        //echo "Task Id:{$task_id} On Finish, \n";
    }


    private function _getFreeArr() {
        $task = $this->free_table->get("task_id");
        $free = json_decode($task['task_id'], true);
        return $free;
    }
    
    /**
     * _addFree 
     * 使用完后 释放链接实际上是放回到空闲数组里来
     * 
     * @param mixed $current_worker_id 
     * @access private
     * @return void
     */
    private function _addFree($current_worker_id) {
        $free = $this->_getFreeArr();
        array_push($free, $current_worker_id);
        $this->free_table->set("task_id",array("task_id"=>json_encode($free))); 
    }

    /**
     * _popFree 
     * 从空闲连接里获得一个连接id(用来指定到task进程)
     * @access private
     * @return int
     */
    private function _popFree() {
        $free = $this->_getFreeArr();
        $cur_id = array_shift($free);
        $this->free_table->set("task_id",array("task_id"=>json_encode($free))); 
        return $cur_id;
    }

    private function _getBusy($fd = NULL) {
        $task = $this->map_table->get("busy_id");
        $busy = json_decode($task['busy_id'], true);
        if ($fd == NULL) {
            return $busy;
        } else {
            if (isset($busy[$fd])) {
                return $busy[$fd];
            }
        }
    }



    private function _delBusy($fd) {
        $busy_arr = $this->_getBusy();
        unset($busy_arr[$fd]);
        return $this->map_table->set("busy_id", array('busy_id'=>json_encode($busy_arr)));
    }

    private function _addBusy($fd, $worker_id) {
        $busy_arr = $this->_getBusy();
        $busy_arr[$fd] = $worker_id;
        return $this->map_table->set("busy_id", array('busy_id'=>json_encode($busy_arr)));
    }
}

 ?>
