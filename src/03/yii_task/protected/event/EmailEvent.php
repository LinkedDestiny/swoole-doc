<?php
//include(Yii::app()->basePath.'/event/EventBase.php');
class EmailEvent extends EventBase
{
    public function exec() {

        $param = $this->param;
        $flag['message'] = 'success';
//        $flag['message'] = 'fail';
        if ($flag['message'] == 'success') {
            // 成功
            $param['status'] = 1;
        } else {
            // 失败 入库,重试机制还没想好.
            $param['status'] = 0;
            $this->event_rs['err_no'] = 100;
            $this->event_rs['err_msg'] = '发送邮件失败';
        }
        $param['create_time'] = time();
        $param['mobile'] = '';
        $message = new Messages();
        $message->attributes = $param;
        $rs = $message->insert();

        return $this->event_rs;
    }
}
