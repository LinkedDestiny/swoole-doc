<?php
/**
 * Created by PhpStorm.
 * User: lancelot
 * Date: 16-6-29
 * Time: 下午8:35
 */

class BaseProcess
{
    private $process;                   // process对象

    public function __construct()
    {
        $this->process = new swoole_process(array($this, 'run') , false , true);
        $this->process->start();
    }

    public function run($worker)
    {
        $worker->name("swoole_process");   // 设置进程名
        for($i = 0; $i < 10; $i ++)
        {
            echo "process: {$i}\n";
        }
    }
}

new BaseProcess();
swoole_process::signal(SIGCHLD, function($sig) {
  while($ret =  swoole_process::wait(false)) {
      echo "PID={$ret['pid']}\n";
  }
});