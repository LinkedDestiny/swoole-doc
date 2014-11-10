DB连接池
==============
Server运行命令：php server.php db1  
Client运行命令：php client.php  
事务测试Client运行命令：php transaction.php  

说明:  
目前连接池只是尝试版本.目前只是实现了一般pdo类的方法。   

建表SQL,例子中是在test库中,具体情况可以更改config.php文件  

CREATE TABLE `test` (
`pid` int(10) DEFAULT NULL,
`name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB;  


