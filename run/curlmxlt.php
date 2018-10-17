<?php
/**
 * Created by PhpStorm.
 * User: kangly
 * Date: 2018/7/19
 * Time: 15:29
 */
chdir('/www/wwwroot/spider/run');
require '../function.php';
$obj = new curlmxltClass();
$obj->run();