<?php
/**
 * Created by PhpStorm.
 * User: kangly
 * Date: 2018/7/19
 * Time: 13:02
 */
chdir('/www/wwwroot/spider/run');
require '../function.php';
$obj = new curlometalClass();
$obj->run();