<?php

class BaseProcess
{

    private $process;

    private $process_list = [];
    private $process_use = [];
    private $min_worker_num = 3;
    private $max_worker_num = 6;

    private $current_num;

    public function __construct()
    {
        $this->process = new swoole_process(array($this, 'run') , false , 2);
        $this->process->start();
		
		swoole_process::wait();
    }

    public function run()
    {
    	$this->current_num = $this->min_worker_num;

        for($i=0;$i<$this->current_num ; $i++){
            $process = new swoole_process(array($this, 'task_run') , false , 2);
            $pid = $process->start();
            $this->process_list[$pid] = $process;
            $this->process_use[$pid] = 0;
        }
        foreach ($this->process_list as $process) {
        	swoole_event_add($process->pipe, function ($pipe) use($process) {
	            $data = $process->read();
	            var_dump($data);
	            $this->process_use[$data] = 0;      
	        });
        }
		

        swoole_timer_tick(1000, function($timer_id) {
            static $index = 0;
            $index = $index + 1;
            $flag = true;
            foreach ($this->process_use as $pid => $used) {
            	if($used == 0)
            	{
            		$flag = false;
            		$this->process_use[$pid] = 1;
					$this->process_list[$pid]->write($index . "Hello");
            		break;
            	}
            }
            if( $flag && $this->current_num < $this->max_worker_num )
            {
            	$process = new swoole_process(array($this, 'task_run') , false , 2);
	            $pid = $process->start();
	            $this->process_list[$pid] = $process;
	            $this->process_use[$pid] = 1;
				$this->process_list[$pid]->write($index . "Hello");
				$this->current_num ++;
            }
            var_dump($index);
            if( $index == 10 )
            {
            	foreach ($this->process_list as $process) {
            		$process->write("exit");
            	}
                swoole_timer_clear($timer_id);
                $this->process->exit();
            }
        });
    }

    public function task_run($worker)
    {
    	swoole_event_add($worker->pipe, function ($pipe) use ($worker){
            $data = $worker->read();
            var_dump($worker->pid . ": " . $data);
            if($data == 'exit')
            {
                $worker->exit();
                exit;
            }
            sleep(5);
            
            $worker->write("" . $worker->pid);
        });
    }
}

new BaseProcess();
