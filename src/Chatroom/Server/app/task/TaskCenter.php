<?php

namespace task;

use Swoole\Core\Factory,
    Swoole\Core\Config;

class TaskCenter
{
	private $ctrl;
	private $task;
	private $data;

	public function parse( $_data ) {
		$params = \json_decode( $_data , true );

		if( isset( $params['ctrl'] ) ) {
			$this->ctrl = $params['ctrl'];
		}
		if( isset( $params['task'] ) ) {
			$this->task = $params['task'];
		}
		if( isset( $params['data'] ) ) {
			$this->data = $params['data'];
		}
	}

	public function route( $server ) {
		$action = 'task\\Adapter\\' . $this->ctrl;
        $class = Factory::getInstance($action);
        if (!($class instanceof Task ) ) {
            throw new \Exception("ctrl error");
        }
        $class->setParams( $this->data );
        $class->setServer( $server );
        $result = $exception = null;
        try {
            $task = $this->task;
            if (\method_exists($class, $task)) {
                $result = $class->$task();
            } else {
                throw new \Exception("no method {$task}");
            }
        } catch (\Exception $e) {
            $exception = $e;
        }
        if ($exception !== null) {
            throw $exception;
        }
        if (null === $result) {
            return;
        }
        return $result;
	}
}