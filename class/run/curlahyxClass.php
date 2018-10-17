<?php
/**
 * Created by PhpStorm.
 * User: kangly
 * Date: 2018/7/29
 * Time: 16:39
 */
require_once '../base.php';
use QL\QueryList;

//抓取岳西在线,信息少,只抓取一页
class curlahyxClass extends baseClass
{
    function run()
    {
        $this->curl_ahyx();
    }

    protected function curl_ahyx()
    {
        $this->start_task(18);

        $url = 'http://bbs.ahyx.net/forum.php?mod=forumdisplay&fid=168&filter=author&orderby=dateline';
        $this_day = strtotime(date('Y-m-d'));
        $img_path = SPIDER_FILE_SAVE_URL.'/Upload/'.date('Ymd');

        sleep(6);

        $list_data = QueryList::Query($url, array(
            'url' => array('tr th a.xst','href'),
            'title' => array('tr th a.xst','text'),
            'contact' => array('tr .by:eq(0) cite a','text'),
            'time' => array('tr .by:eq(0) em span','text','',function($content){
                $date_data = explode(' ',$content);
                return $date_data[0];
            })
        ),'table#threadlisttableid tbody[id^="normalthread_"]','UTF-8','GBK',true)->data;

        if(empty($list_data)){
            $this->stop_task(18);
            exit;
        }

        foreach($list_data as $cv)
        {
            if(!$this->load_task_state(18)){
                exit;
            }

            if(strtotime($cv['time']) != $this_day){
                break;
            }

            $is_change = $this->dbm->select('collect_data', ['id'], ['url' => $cv['url'], 'pid' => 18]);
            if(empty($is_change))
            {
                sleep(6);

                $details = QueryList::run('Request', [
                    'target' => $cv['url'],
                    'method' => 'GET',
                    'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.181 Safari/537.36',
                    'cookiePath' => '../cookie.txt',
                    'timeout' => '30'
                ]);

                $source = $details->html;
                if(!$source){
                    $this->stop_task(18);
                    exit;
                }

                $base_data = QueryList::Query($source,array(
                    'content' => array('.t_f:eq(0)','text','-ignore_js_op -i -br -img -div -a -span')
                ),'','UTF-8','GBK',true)->data;

                $img_source = QueryList::Query($source,array(
                    'img' => array('.zoom:lt(2)','file')
                ),'','UTF-8','GBK',true)->data;

                $img_data = [];
                foreach($img_source as $ik=>$iv){
                    if($iv['img']){
                        if(!file_exists($img_path)){
                            mkdir($img_path,0777,true);
                        }
                        $local_image = $img_path.'/ahyx_'.time().$ik.'.jpg';
                        if(!is_file($local_image)){
                            $content = 'http://bbs.ahyx.net/'.$iv['img'];
                            file_put_contents($local_image,file_get_contents($content));
                        }
                        $img_data[]['img'] = str_replace(SPIDER_FILE_SAVE_URL,'',$local_image);
                    }
                }

                $add_data = [
                    'url' => $cv['url'],
                    'pid' => 18,
                    'title' => $cv['title'],
                    'contact' => $cv['contact'],
                    'content' => $base_data[0]['content'],
                    'img_data' => json_encode($img_data,JSON_UNESCAPED_UNICODE),
                    'province_id' => 13,//安徽
                    'city' => '安庆',
                    'area_id' => 136,//安庆
                    'pub_date' => $cv['time'],
                    'create_time' => _time()
                ];

                $this->dbm->insert('collect_data',$add_data);
            }
        }

        $this->stop_task(18);
    }
}