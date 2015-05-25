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

        var_dump($data);   die;
	}
}
?>
