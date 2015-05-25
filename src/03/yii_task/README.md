# yii_swoole_task
在YII框架中结合了swoole 的task 做了异步处理。
本例中 主要用到  
1、protected/commands/ServerCommand.php 用来做server。  
2、protected/event/下的文件 这里是在异步中的具体实现。

客户端调用参照 TestController 
```php
<?php
class TestController extends Controller{
    public function actionTT(){
        $message['uid'] = 2;
        $message['email'] = '83212019@qq.com';
        $message['title'] = '接口报警邮件';
        $message['contents'] = "'EmailEvent'接口请求过程出错！ 错误信息如下：err_no:'00000' err_msg:'测试队列' 请求参数为:'[]'";
        $message['type'] = 2;

        $data['param'] = $message;
        $data['class'] = 'Email';
        $client = new EventClient();
        $data = $client->send($data);
    }
}
?>
```

有个task表是用来记录异步任务的。如果失败重试3次。sql在protected/data/sql.sql里。
如有意见欢迎拍砖  83212019@qq.com
