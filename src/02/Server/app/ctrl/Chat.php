<?php

namespace ctrl;

use Swoole\Core\Config as ZConfig,
    db\Factory as DBFactory;

class Chat extends BaseController
{
    private $reids;

	public function setServer($server) {
        $this->server = $server;
        $this->params = $server->getParams();
        $this->redis = DBFactory::getInstance('Chat');
    }

    public function online() {
    	$fd = $this->params['fd'];
    	$name = $this->params['name'];
        echo $name . PHP_EOL;
    	$data = json_encode( array(
    		'op' => 'online',
    		'fd' => $fd,
    		'name' => $name
    	));
        $this->redis->online( $fd );
        $this->redis->setUserInfo( $fd , array( 'name' => $name ) );
    	$data = pack("Na*", strlen($data) , $data );
        $fd_list = $this->redis->getFdList();
        unset($fd_list[array_search( $fd ,$fd_list)]);
    	$this->sendMessage( $fd_list , $data );

        $this->getOnlineList();
    }

    public function offline() {
    	$fd = $this->params['fd'];

    	$data = json_encode( array(
    		'op' => 'offline',
    		'fd' => $fd,
    	));
    	$data = pack("Na*", strlen($data) , $data );
        $this->redis->offline( $fd );
    	$this->sendMessage( $this->redis->getFdList( $this->redis->getChannel( $fd ) ), $data );
    }

    public function changeChannel() {
    	$fd = $this->params['fd'];
    	$from = $this->params['from'];
    	$to = $this->params['to'];

    	$data = json_encode( array(
    		'op' => 'online',
    		'fd' => $fd,
    		'name' => $this->redis->getUserInfo( $fd , "name")
    	));
    	$data = pack("Na*", strlen($data) , $data );

        $fd_list = $this->redis->getFdList($to);
        unset($fd_list[array_search( $fd ,$fd_list)]);
    	$this->sendMessage( $fd_list, $data );

    	if( !$this->redis->enterChannel( $fd , $to ) ) {
    		return;
    	}
    }

    public function send() {
    	$fd = $this->params['fd'];
    	$sendto = $this->params['sendto'];
    	$msg = $this->params['msg'];
    	$data = json_encode( array(
    		'op' => 'recv',
    		'from' => $fd,
    		'msg' => $msg
    	));
    	$data = pack("Na*", strlen($data) , $data );
        $fd_list = $this->redis->getFdList($sendto);
        unset($fd_list[array_search( $fd ,$fd_list)]);
        $this->sendMessage( $fd_list, $data );
    }

    public function getOnlineList() {
        $fd = $this->params['fd'];
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
        $data = pack("Na*", strlen($data) , $data );
        $this->sendMessage( array( $fd ), $data );
    }

    private function sendMessage( $fd_list , $msg ) {
 		$server = $this->server->getServer();
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