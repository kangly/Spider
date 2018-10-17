<?php
/**
 * Created by PhpStorm.
 * User: kangly
 * Date: 2018/9/14
 * Time: 15:11
 */
//为crontab设置的路径,服务器上需要执行
chdir('/www/wwwroot/spider/run');
require '../function.php';
$obj = new curltaobaoClass();
$obj->run();