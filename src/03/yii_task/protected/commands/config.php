<?php
// 队列里每种类型执行的进程个数
return array(
    'Email' => 2,   // 邮件处理进程数量
    'Common' => 3,  // 公共进程数量
    'Retry' => 3,  // 公共进程数量
);
