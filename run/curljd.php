<?php
/**
 * Created by PhpStorm.
 * User: kangly
 * Date: 2018/10/11
 * Time: 17:02
 */
//为crontab设置的路径,服务器上需要执行
chdir('/www/wwwroot/spider/run');
require '../function.php';
$obj = new curljdClass();
$obj->run();