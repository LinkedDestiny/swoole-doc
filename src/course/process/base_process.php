<?php
/**
 * Created by PhpStorm.
 * User: lancelot
 * Date: 16-6-29
 * Time: 下午8:35
 */

class BaseProcess
{

    private $process;

    public function __construct()
    {
        $this->process = new swoole_process(array($this, 'run') , false , true);
        $this->process->signal(SIGTERM, function($signo) {
            echo "{$signo} shutdown.\n";
        });
        $this->process->daemon(true,true);
        var_dump($this->process);
        $this->process->start();

        swoole_event_add($this->process->pipe, function ($pipe){
            $data = $this->process->read();
            echo "RECV: " . $data.PHP_EOL;
        });
    }

    public function run($worker)
    {
        swoole_timer_tick(1000, function() {
            $this->process->write("Hello");
        });

        swoole_process::wait();
    }
}

new BaseProcess();