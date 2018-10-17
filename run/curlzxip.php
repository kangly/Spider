<?php
/**
 * Created by PhpStorm.
 * User: kangly
 * Date: 2018/7/29
 * Time: 21:04
 */
//为crontab设置的路径,服务器上需要执行
chdir('/www/wwwroot/spider/run');
require '../function.php';
$obj = new curlzxipClass();
$obj->run();