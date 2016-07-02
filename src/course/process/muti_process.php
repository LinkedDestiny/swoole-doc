<?php
/**
 * Created by PhpStorm.
 * User: lancelot
 * Date: 16-6-29
 * Time: 下午10:34
 */

class BaseProcess
{

    private $process;

    private $process_list = [];
    private $worker_num = 3;

    public function __construct()
    {
        $this->process = new swoole_process(array($this, 'run') , false , false);
        $this->process->useQueue();
        $this->process->start();

    }

    public function run()
    {
        for($i=0;$i<$this->worker_num ; $i++){
            $this->process_list[$i] = new swoole_process(array($this, 'task_run') , false , false);
            $this->process_list[$i]->useQueue();
            $this->process_list[$i]->start();
        }

        swoole_timer_tick(1000, function($timer_id) {
            static $index = 0;
            $index = $index + 1;
            $this->process->push($index . "Hello");
            var_dump($index);
            if( $index == 10 )
            {
                $this->process->push("exit");
                $this->process->push("exit");
                $this->process->push("exit");
                swoole_timer_clear($timer_id);
            }
        });
    }

    public function task_run($worker)
    {
        while(true)
        {
            $data = $worker->pop();
            var_dump($data);
            if($data == 'exit')
            {
                $worker->exit();
            }
            sleep(5);
        }
    }
}

new BaseProcess();
swoole_process::signal(SIGCHLD, function($sig) {
  //必须为false，非阻塞模式
  while($ret =  swoole_process::wait(false)) {
      echo "PID={$ret['pid']}\n";
  }
});