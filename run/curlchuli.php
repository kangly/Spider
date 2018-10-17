<?php
/**
 * Created by PhpStorm.
 * User: kangly
 * Date: 2018/7/26
 * Time: 11:26
 */
//为crontab设置的路径,服务器上需要执行
chdir('/www/wwwroot/spider/run');
require '../function.php';
$obj = new curlchuliClass();
$obj->run();