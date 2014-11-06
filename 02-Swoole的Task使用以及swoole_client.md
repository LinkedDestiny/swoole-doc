###第二章 Swoole的task使用以及swoole_client
环境说明：
系统：Ubuntu14.04 （安装教程包括CentOS6.5）<br>
PHP版本：PHP-5.5.10<br>
swoole版本：1.7.6-stable<br>

上一章已经简单介绍了如何写一个简单的Echo服务器，并了解了onReceive等几个核心回调函数的使用方法。这一章，我将介绍如何使用Swoole的异步任务Task。

####**1.Task简介**
Swoole的业务逻辑部分是同步阻塞运行的，如果遇到一些耗时较大的操作，例如访问数据库、广播消息等，就会影响服务器的响应速度。因此Swoole提供了Task功能，将这些耗时操作放到另外的进程去处理，当前进程继续执行后面的逻辑。

####**2.开启Task功能**
开启Task功能只需要在swoole_server的配置项中添加[task_worker_num](https://github.com/LinkedDestiny/swoole-doc/blob/master/doc/01.%E9%85%8D%E7%BD%AE%E9%80%89%E9%A1%B9.md#6task_worker_num)一项即可，如下：
```php
$serv->set(array(
    'task_worker_num' => 8
));
```
即可开启task功能。此外，必须给swoole_server绑定两个回调函数：[onTask](https://github.com/LinkedDestiny/swoole-doc/blob/master/doc/02.%E4%BA%8B%E4%BB%B6%E5%9B%9E%E8%B0%83%E5%87%BD%E6%95%B0.md#6ontask)和[onFinish](https://github.com/LinkedDestiny/swoole-doc/blob/master/doc/02.%E4%BA%8B%E4%BB%B6%E5%9B%9E%E8%B0%83%E5%87%BD%E6%95%B0.md#7onfinish)。这两个回调函数分别用于执行Task任务和处理Task任务的返回结果。

####**3.使用Task**
首先是发起一个Task，代码如下：
```php
    public function onReceive( swoole_server $serv, $fd, $from_id, $data ) {
        echo "Get Message From Client {$fd}:{$data}\n";
        // send a task to task worker.
        $param = array(
            'fd' => $fd
        );
        // start a task
        $serv->task( json_encode( $param ) );
        
        echo "Continue Handle Worker\n";
    }
```
可以看到，发起一个任务时，只需通过swoole_server对象调用task函数即可发起一个任务。swoole内部会将这个请求投递给task_worker，而当前Worker进程会继续执行。

当一个任务发起后，task_worker进程会响应[onTask](https://github.com/LinkedDestiny/swoole-doc/blob/master/doc/02.%E4%BA%8B%E4%BB%B6%E5%9B%9E%E8%B0%83%E5%87%BD%E6%95%B0.md#6ontask)回调函数，如下：
```php
    public function onTask($serv,$task_id,$from_id, $data) {
        echo "This Task {$task_id} from Worker {$from_id}\n";
        echo "Data: {$data}\n";
        for($i = 0 ; $i < 10 ; $i ++ ) {
            sleep(1);
            echo "Task {$task_id} Handle {$i} times...\n";
        }
        $fd = json_decode( $data , true )['fd'];
        $serv->send( $fd , "Data in Task {$task_id}");
        return "Task {$task_id}'s result";
    }
```
这里我用sleep函数和循环模拟了一个长耗时任务。在onTask回调中，我们通过task_id和from_id(也就是worker_id)来区分不同进程投递的不同task。当一个task执行结束后，通过return一个字符串将执行结果返回给Worker进程。Worker进程将通过[onFinish](https://github.com/LinkedDestiny/swoole-doc/blob/master/doc/02.%E4%BA%8B%E4%BB%B6%E5%9B%9E%E8%B0%83%E5%87%BD%E6%95%B0.md#7onfinish)回调函数接收这个处理结果。

下面来看onFinish回调：
```php
    public function onFinish($serv,$task_id, $data) {
        echo "Task {$task_id} finish\n";
        echo "Result: {$data}\n";
    }
```
在[onFinish](https://github.com/LinkedDestiny/swoole-doc/blob/master/doc/02.%E4%BA%8B%E4%BB%B6%E5%9B%9E%E8%B0%83%E5%87%BD%E6%95%B0.md#7onfinish)回调中，会接收到Task任务的处理结果$data。在这里处理这个返回结果即可。
（**Tip:** 可以通过在传递的data中存放fd、buff等数据，来延续投递Task之前的工作）

[点此查看完整示例](https://github.com/LinkedDestiny/swoole-doc/blob/master/src/02/swoole_task_server.php)

####**4.swoole_client**
之所以在这里讲解如何使用swoole_client是因为，在写服务端代码的时候，不可避免的需要用到客户端来进行测试。swoole提供了swoole_client用于编写测试客户端，下面我将讲解如何使用这个工具。

swoole_client有两种工作模式：同步阻塞模式和异步回调模式。其中，同步阻塞模式在上一章中已经给出示例，其使用和一般的socket基本无异。因此，我将重点讲解swoole_client的异步回调模式。

创建一个异步client的代码如下：
```php
$client = new swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);
```
其中，**SWOOLE_SOCK_ASYNC**选项即表明创建一个异步client。

既然是异步，那当然需要回调函数。swoole_client一共有四个回调函数，如下：
```php
$client->on("connect", function($cli) {
    $cli->send("hello world\n");
});
$client->on("receive", function($cli, $data){
    echo "Received: ".$data."\n";
});
$client->on("error", function($cli){
    echo "Connect failed\n";
});
$client->on("close", function($cli){
    echo "Connection close\n";
});
```
这几个回调函数的作用基本和swoole_server类似，只有参数不同，因此不再赘述。
[点此查看完整示例](https://github.com/LinkedDestiny/swoole-doc/blob/master/src/02/swoole_async_client.php)


###**进阶：简易聊天室**
我用swoole扩展写了一个简单的聊天室Demo（[点此查看](https://github.com/LinkedDestiny/swoole-doc/tree/master/src/Chatroom)）
这个Demo虽然用到了一些其他的架构，但是核心功能仍然是依托swoole扩展实现的。

- 通过onReceive回调接收数据，根据预先规定的字段找到对应的处理函数。 
-  通过onTask处理发送数据、广播这样的耗时内容。

[Server.php](https://github.com/LinkedDestiny/swoole-doc/blob/master/src/Chatroom/Server/app/socket/Server.php)是全部的Swoole回调函数实现的类。


下章预告：Timer计时器、心跳检测及Task进阶实例：mysql连接池。




