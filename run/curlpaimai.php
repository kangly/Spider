<?php
/**
 * Created by PhpStorm.
 * User: kangly
 * Date: 2018/9/10
 * Time: 15:35
 */
//为crontab设置的路径,服务器上需要执行
chdir('/www/wwwroot/spider/run');
require '../function.php';
$obj = new curlpaimaiClass();
$obj->run();