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

        swoole_timer_tick(1000, function() {
            $this->process->push("Hello");
        });
    }

    public function task_run($worker)
    {
        while(true)
        {
            $data = $worker->pop();
            var_dump($data);
            sleep(5);
        }
    }
}

new BaseProcess();