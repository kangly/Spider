<?php
/**
 * Created by PhpStorm.
 * User: kangly
 * Date: 2018/7/16
 * Time: 11:21
 */
//为crontab设置的路径,服务器上需要执行
chdir('/www/wwwroot/spider/run');
require '../function.php';
$obj = new curlfengfengClass();
$obj->run();