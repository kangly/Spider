<?php
/**
 * Created by PhpStorm.
 * User: kangly
 * Date: 2018/10/16
 * Time: 16:37
 */
chdir('/www/wwwroot/spider/run');
require '../function.php';
$obj = new curlsfClass();
$obj->run();