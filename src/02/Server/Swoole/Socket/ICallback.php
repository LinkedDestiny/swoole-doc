<?php
/**
 * User: shenzhe
 * Date: 13-6-17
 * socket callback接口
 */
namespace Swoole\Socket;
interface ICallback
{
	/**
	 * 当socket服务启动时，回调此方法
	 */
    public function onStart();

    /**
	 * 当有client连接上socket服务时，回调此方法
	 */
    public function onConnect();

    /**
	 * 当有数据到达时，回调此方法
	 */
    public function onReceive();

    /**
	 * 当有client断开时，回调此方法
	 */
    public function onClose();
}