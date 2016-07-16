<?php

namespace ctrl;

use Swoole\Core\Config as ZConfig,
    db\Factory as DBFactory;

class Chat extends BaseController
{
    private $redis;
    protected $server;

    public function __construct($server)
    {
        $this->server = $server;
        $this->redis = DBFactory::getInstance('Chat');
    }

    public function online($params) {
    	$fd = $params['fd'];
    	$name = $params['name'];

        echo $name . PHP_EOL;
    	$data = json_encode( array(
    		'op' => 'online',
    		'fd' => $fd,
    		'name' => $name
    	));
        $this->redis->online( $fd );
        $this->redis->setUserInfo( $fd , array( 'name' => $name ) );

        $fd_list = $this->redis->getFdList();
        unset($fd_list[array_search( $fd ,$fd_list)]);
    	$this->sendMessage( $fd_list , $data );

        $this->getOnlineList($params);
    }

    public function offline($params) {
    	$fd = $params['fd'];

    	$data = json_encode( array(
    		'op' => 'offline',
    		'fd' => $fd,
    	));
        $this->redis->offline( $fd );
    	$this->sendMessage( $this->redis->getFdList( $this->redis->getChannel( $fd ) ), $data );
    }

    public function changeChannel($params) {
    	$fd = $params['fd'];
    	$from = $params['from'];
    	$to = $params['to'];

    	$data = json_encode( array(
    		'op' => 'online',
    		'fd' => $fd,
    		'name' => $this->redis->getUserInfo( $fd , "name")
    	));

        $fd_list = $this->redis->getFdList($to);
        unset($fd_list[array_search( $fd ,$fd_list)]);
    	$this->sendMessage( $fd_list, $data );

    	if( !$this->redis->enterChannel( $fd , $to ) ) {
    		return;
    	}
    }

    public function send($params) {
    	$fd = $params['fd'];
    	$sendto = $params['sendto'];
    	$msg = $params['msg'];
    	$data = json_encode( array(
    		'op' => 'recv',
    		'from' => $fd,
    		'msg' => $msg
    	));
        $fd_list = $this->redis->getFdList($sendto);
        unset($fd_list[array_search( $fd ,$fd_list)]);
        $this->sendMessage( $fd_list, $data );
    }

    public function getOnlineList($params) {
        $fd = $params['fd'];
        $fd_list = $this->redis->getFdList();
        unset($fd_list[array_search( $fd ,$fd_list)]);
        $list = array();
        foreach ($fd_list as $f) {
            $list[$f] = $this->redis->getUserInfo( $f , "name");
        }
        $data = json_encode( array(
            'op' => 'onlineList',
            'list' => $list
        ));
        $this->sendMessage( array( $fd ), $data );
    }

    private function sendMessage( $fd_list , $msg ) {
 		$server = $this->server;
        $data = array(
            'ctrl' => 'Chat',
            'task' => 'sendMessage',
            'data' => array(
                'list' => $fd_list,
                'data' => $msg
            )
           
        );
        $server->task( \json_encode( $data ) );
    }
}