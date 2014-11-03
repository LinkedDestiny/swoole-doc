<?php

class Crontab
{
	private $server;
    private $config;

    private $event_list;

    const MINUTE = 60 * 1000;
    const HOUR = 60 * Crontab::MINUTE;
    const DAY = 24 * Crontab::HOUR;

	public function __construct() {
		$this->serv = new swoole_server("0.0.0.0", 9501);
        $this->serv->set(array(
            'worker_num' => 8,
            'daemonize' => false,
            'max_request' => 10000,
            'dispatch_mode' => 2,
            'debug_mode'=> 1 ,
        ));

        $this->serv->on('WorkerStart', array($this, 'onWorkerStart'));
        $this->serv->on('Receive', array($this, 'onReceive'));
                // bind callback
        $this->serv->on('Timer', array($this, 'onTimer'));
        $this->serv->start();
	}

	public function onWorkerStart( $serv , $worker_id) {
        // 加载配置文件
        require "Config.php";
        $this->config = $config;

        // 只有当worker_id为0时才添加定时器,避免重复添加
        if( $worker_id == 0 ) {
            $serv->addtimer(Crontab::MINUTE);
        }
    }

    public function onReceive( swoole_server $serv, $fd, $from_id, $data ) {
        echo "Get Message From Client {$fd}:{$data}\n";
    }

    public function onTimer($serv, $interval) {
    	switch( $interval ) {
    		case Crontab::MINUTE: {	// 
    			
    			break;
    		}
            case Crontab::HOUR: { // 
                
                break;
            }
            case Crontab::DAY: { // 
                
                break;
            }
    	}
    }

    private function parseConfig() {
        foreach ($this->config as $event => $time) {
            $minute = $this->parseMinute( $time['minute'] );
            $hour = $this->parseMinute( $time['hour'] );
            $day = $this->parseMinute( $time['day'] );

            if( $minute < 0 ) {
                echo "{$event} set wrong minute\n";
                continue;
            }
            if( $hour < 0 ) {
                echo "{$event} set wrong hour\n";
                continue;
            }
            if( $day < 0 ) {
                echo "{$event} set wrong day\n";
                continue;
            }

            $this->event_list[ $event ] = array(
                'minute' => $minute > 0 ? $minute : -1,
                'hour' => $hour > 0 ? $hour : -1,
                'day' => $day > 0 ? $day : -1
            )
        }
    }

    private function parseMinute( $time ) {
        $result = split("/", $time);
        if( strlen($result) == 1 ) {
            if( $result[0] == '*' )
                return 1;
            else
                return intval( $result[0];
        } else {
            $i = intval( $result[1] );
            if( 60 % $i != 0 ) {
                return -1;
            }
            return $i;
        }
    }

    private function parseHour( $time ) {
        $result = split("/", $time);
        if( strlen($result) == 1 ) {
            return 1;
        } else {
            $i = intval( $result[1] );
            if( 24 % $i != 0 ) {
                return -1;
            }
            return $i;
        }
    }

    private function parseDay( $time ) {
        $result = split("/", $time);
        if( strlen($result) == 1 ) {
            return 1;
        } else {
            return intval( $result[1] );
        }
    }
}

new TimerServer();