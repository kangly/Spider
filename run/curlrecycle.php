<?php
/**
 * Created by PhpStorm.
 * User: kangly
 * Date: 2018/10/15
 * Time: 14:26
 */
//为crontab设置的路径,服务器上需要执行
chdir('/www/wwwroot/spider/run');
require '../function.php';
$obj = new curlrecycleClass();
$obj->run();