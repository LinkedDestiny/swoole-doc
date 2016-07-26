<?php

namespace ctrl;

use Swoole\Controller\IController;

class BaseController implements IController
{

	protected $server;
    protected $params = array();

	/**
	 *  设置服务
	 */
    public function setServer($server) {
        $this->server = $server;
        $this->params = $server->getParams();
    }

    /**
	 * 业务逻辑开始前执行
	 */
    public function _before() {
        return true;
    }

    /**
	 * 业务逻辑结束后执行
	 */
    public function _after() {
        return true;
    }

    public function getParams()
    {
        return $this->params;
    }
}