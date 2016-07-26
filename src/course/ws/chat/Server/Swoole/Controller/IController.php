<?php
/**
 * User: shenzhe
 * Date: 13-6-17
 *  controller 接口
 */
namespace Swoole\Controller;
interface IController
{
	/**
	 *  设置服务
	 */
    function setServer($server);

    /**
	 * 业务逻辑开始前执行
	 */
    function _before();

    /**
	 * 业务逻辑结束后执行
	 */
    function _after();
}