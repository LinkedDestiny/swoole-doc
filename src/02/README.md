###简易聊天室

Server运行命令：php Main.php chat
Client运行命令：php chatroom.php

说明：
Server基于swoole扩展开发， 使用了redis数据库，因此需要按照phpredis扩展。
整个Server框架是简化版的ZPHP框架，如果看不懂ZPHP的可以先尝试看懂这个简易版的。

目前聊天室的功能还没有完全写完，我会在后续慢慢补充。
已完成的功能：上线下线广播、消息发送、获取在线列表。
待完成的功能：频道切换。 