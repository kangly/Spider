<?php
/**
 * Created by PhpStorm.
 * User: kangly
 * Date: 2018/7/26
 * Time: 16:06
 */
chdir('/www/wwwroot/spider/run');
require '../function.php';
$obj = new curlpzzcClass();
$obj->run();