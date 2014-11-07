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

    function __construct(array $config) {
        $this->port = isset($config['port']) ? $config['port'] : 9500; // server监听的端口
        $this->pool_size = isset($config['pool_size']) ? $config['pool_size'] : 20;    
        $this->db_host = isset($config['db_host']) ? $config['db_host'] : "127.0.0.1";
        $this->db_user= isset($config['db_user']) ? $config['db_user'] : "root";
        $this->db_pwd = isset($config['db_pwd']) ? $config['db_pwd'] : "";
        $this->db_name = isset($config['db_name']) ? $config['db_name'] : "test";
        $this->db_port = isset($config['db_port']) ? $config['db_port'] : 3306;
    }

    function run() {
        $this->serv = new swoole_server("127.0.0.1", $this->port);
        $this->serv->set( array(
            'worker_num'=>1,
            'task_worker_num' => 2
        ));
        $this->serv->on('WorkerStart', array($this, 'onStart'));
        $this->serv->on('Receive', array($this, 'onReceive'));

        // Task 回调的2个必须函数
        $this->serv->on('Task', array($this, 'onTask'));
        $this->serv->on('Finish', array($this, 'onFinish'));

        $this->serv->start();
    }

    function onStart($serv, $worker_id) {
        $version = swoole_version();
        echo "onWorkerStart {$version}\n";
        //  初始化连接池
        for ($i = 0; $i < $this->pool_size; $i++) {
            $db = new PDO("mysql:host={$this->db_host};port={$this->db_port};dbname={$this->db_name}",$this->db_user, $this->db_pwd,
                array(
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8';",
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_PERSISTENT => true
                )
            );
            $this->free_pool[] = $db;
        }
    }

    public function onConnect( $serv, $fd, $from_id ) {
        echo "Client {$fd} connect\n";
    }

    public function onReceive( swoole_server $serv, $fd, $from_id, $data ) {
        //  如果没有空闲连接,都用着呢
        if (count($this->free_pool) == 0 ) { 
            if (count($this->wait_queue) < $this->wait_queue_max) { // 判断是否可以进入等待队列
                /*
                $this->wait_queue[] = array(
                    'fd' => $fd,
                    'sql' => $data,
                    );
                 */
            } else {
                $this->serv->send($fd, "request too many, Please try again later.");
            }
        } else {
            $data = array('fd' => $fd,'send_data' => $data);
            echo "{$fd}  {$from_id} new connection , receive data:".json_encode($data['send_data'])."\n";
            $this->serv->task(json_encode($data));
        }
    }

    public function doQuery($fd, $data) {
        echo "doQuery \n";
        $rs = "";

        if (is_array($data)) {
            $cur_key = $fd."_".$data['cur_key'];
            $func_name = $data['func_name'];
            $param = implode(',', $data['param']);

            //  如果当前连接还在使用,则直接使用。
            if (isset($this->busy_pool[$cur_key])) {
                $db = $this->busy_pool[$cur_key];
            } else { // 如果没有使用则从空闲中pop一个连接出来.
                $db = array_pop($this->free_pool);
                $this->busy_pool[$cur_key] = $db;
            }
            var_dump($db);
            if ($func_name == "release") {
                $db = $this->busy_pool[$cur_key];    // 重新放回到free
                $this->free_pool[] = $db;
                unset($this->busy_pool[$cur_key]);
                echo $rs = "doQuery: release \n";
                $this->serv->send($fd, $rs);
            } else {    //执行一般pdo方法
                echo "fname:{$func_name}   param: {$param} \n";
                $st = $db->$func_name($param);
                $rs = $st->fetchAll();

                if ($rs == "") {
                    echo $rs = "doQuery: data:: isempty \n";
                    $this->serv->send($fd, $rs);
                } else {
                    echo "doQuery: data:: ".json_encode($rs)."\n";
                    $this->serv->send($fd, json_encode($rs));
                }
            }
        }
        
    }

    public function onClose( $serv, $fd, $from_id ) {
        echo "Client {$fd} close connection\n";
    }

    public function onTask($serv,$task_id,$from_id, $data) {
        
        $data = json_decode( $data , true );
        $send_data = json_decode( $data['send_data'], true);
        echo "Server On Task,".json_encode($data)." \n";
        $this->doQuery($data['fd'], $send_data);
        $serv->send($data['fd'], " On Task");

        /*
        $sql = json_decode( $data , true );
        
        $statement = $this->pdo->prepare($sql['sql']);
        $statement->execute($sql['param']);     
        $serv->send( $sql['fd'],"Insert");
         */
        return true;
    }

    public function onFinish($serv,$task_id, $data) {
        echo "Task Id:{$task_id} On Finish,".json_encode($data)." \n";
    }

}

 ?>
