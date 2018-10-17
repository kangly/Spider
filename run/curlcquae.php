<?php
/**
 * Created by PhpStorm.
 * User: kangly
 * Date: 2018/10/16
 * Time: 15:38
 */
//为crontab设置的路径,服务器上需要执行
chdir('/www/wwwroot/spider/run');
require '../function.php';
$obj = new curlcquaeClass();
$obj->run();