<?php
abstract class EventBase
{
    public $event_rs = array('err_no' => 0, 'err_msg' => '');
    public $param;
    public $class;
    public $retry_cnt = 0;

    public abstract function exec();

    public function before($data = array()) {
        if (is_array($data) && isset($data['class'])) {
            $this->class = $data['class'];
        }

        if (is_array($data) && isset($data['param'])) {
            $this->param = $data['param'];
        }
        $this->retry_cnt = isset($data['retry_cnt']) ? $data['retry_cnt'] : 0;
        $this->retry_cnt++;

        echo "before \n";

    }

    public function after() {
        echo "after\n";
        $insert = 0;  // 判断是否插入表
        $class_name = get_class($this);
        $type = str_replace('Event', '' ,$class_name);

        $task = new Task();
        if ($this->event_rs['err_no'] > 0) {
            //  代表错误
            $task->is_success = 0;
            $this->event_rs['class'] = $this->class;
            $this->event_rs['param'] = $this->param;

            if ($this->retry_cnt >= 3) {
                $insert = 1;
            }
        } else {
            $task->is_success = 1;
            $insert = 1;
        }


        // 成功则入库 第一次失败则不入库 第三次失败才一起入库

        $task->create_time = time();
        $task->retry_cnt = $this->retry_cnt;
        $task->event = $type;
        $task->param = json_encode($this->param);

        if ($insert > 0) {
            $task->save();
        }

        $this->event_rs['retry_cnt'] = $this->retry_cnt;
    }

    public function run($data) {
        $this->before($data);
        $rs = $this->exec();
        $this->after();
        return $this->event_rs;
    }

}
?>
