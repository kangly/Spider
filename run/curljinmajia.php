<?php
/**
 * Created by PhpStorm.
 * User: kangly
 * Date: 2018/10/15
 * Time: 13:04
 */
//为crontab设置的路径,服务器上需要执行
chdir('/www/wwwroot/spider/run');
require '../function.php';
$obj = new curljinmajiaClass();
$obj->run();