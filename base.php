<?php
/**
 * Created by PhpStorm.
 * User: kangly
 * Date: 2018/4/21
 * Time: 17:04
 */
error_reporting(E_ALL ^ E_NOTICE);//不输出notice提示
date_default_timezone_set('Asia/Shanghai');
ini_set('memory_limit','2048M');
define('SPIDER_FILE_SAVE_URL','../../SpiderInfo');//定义附件保存路径

require_once '../vendor/autoload.php';
use Medoo\Medoo;

class baseClass
{
    protected $dbm;

    function __construct(){
        $this->dbm = new medoo([
            'database_type' => 'mysql',
            'database_name' => 'spider',
            'server' => '127.0.0.1',
            'username' => 'root',
            'password' => '922419',

            // 可选参数
            'charset' => 'utf8',
            'port' => 3306,
            'prefix' => 'destoon_',

            'option' => [
                PDO::ATTR_CASE => PDO::CASE_NATURAL,    //列名按照原始的方式
                PDO::ATTR_PERSISTENT => true    //默认这个不是长连接，如果需要数据库长连接，需要最后加一个参数,变成这样：array(PDO::ATTR_PERSISTENT => true)
            ]
        ]);
    }

    /**
     * 获取对应任务执行状态
     * @param $pid
     * @return int
     */
    protected function load_task_state($pid)
    {
        $data = $this->dbm->select('task_info',['state'],['pid'=>$pid]);
        return $data[0]['state']?1:0;
    }

    /**
     * 开始任务
     * @param $pid
     */
    protected function start_task($pid)
    {
        $this->dbm->update('task_info',['state'=>1],['pid'=>$pid]);
    }

    /**
     * 结束任务
     * @param $pid
     */
    protected function stop_task($pid)
    {
        $this->dbm->update('task_info',['state'=>0],['pid'=>$pid]);
    }
}