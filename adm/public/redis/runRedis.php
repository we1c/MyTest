<?php

//在LINUX终端运行: php b.php 观察结果，你会发现代码是异步执行

/*注释：
-q 代表屏蔽php信息
> /dev/null 代表消除shell下的输出
& 代表在后台执行脚本
以上注释中提到的3个关键点都做到就可以做到终端没有输出，也不影响后续代码的执行。
*/

$cmd = " php -q ./doRedis.php > /dev/null &";
exec($cmd);