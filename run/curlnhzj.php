<?php
/**
 * Created by PhpStorm.
 * User: kangly
 * Date: 2018/7/20
 * Time: 21:53
 */
chdir('/www/wwwroot/spider/run');
require '../function.php';
$obj = new curlnhzjClass();
$obj->run();