<?php
/**
 * Created by PhpStorm.
 * User: kangly
 * Date: 2018/7/29
 * Time: 20:38
 */
require_once '../base.php';
use QL\QueryList;

//抓取热处理论坛
class curlrclbbsClass extends baseClass
{
    function run()
    {
        $this->curl_rclbbs();
    }

    protected function curl_rclbbs()
    {
        $this->start_task(20);

        $url = 'http://www.rclbbs.com/forum.php?mod=forumdisplay&fid=50&orderby=dateline&orderby=dateline&filter=author&page=1';
        $this_day = date('Y-n-j');
        $img_path = SPIDER_FILE_SAVE_URL.'/Upload/'.date('Ymd');

        sleep(6);

        $list_data = QueryList::Query($url, array(
            'url' => array('tr th a.xst','href','',function($content){
                return 'http://www.rclbbs.com/'.$content;
            }),
            'title' => array('tr th a.xst','text'),
            'contact' => array('tr .by:eq(0) cite a','text'),
            'time1' => array('tr .by:eq(0) em span:last','title'),
            'time2' => array('tr .by:eq(0) em span:last','text')
        ),'table#threadlisttableid tbody[id^="normalthread_"]','UTF-8','GBK',true)->data;

        if(empty($list_data)){
            $this->stop_task(20);
            exit;
        }

        foreach($list_data as $cv)
        {
            if(!$this->load_task_state(20)){
                exit;
            }

            $cv['time'] = $cv['time1']?$cv['time1']:$cv['time2'];
            if($cv['time'] != $this_day){
                break;
            }

            $is_change = $this->dbm->select('collect_data', ['id'], ['url' => $cv['url'], 'pid' => 20]);
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
                    $this->stop_task(20);
                    exit;
                }

                $base_data = QueryList::Query($source,array(
                    'content' => array('.t_f:eq(0)','text','-i -br -img -div -a -span')
                ),'','UTF-8','GBK',true)->data;

                $img_data = QueryList::Query($source,array(
                    'img' => array('.t_fsz:eq(0) .savephotop img:lt(2)','file','',function($content) use($img_path){
                        if($content){
                            $content = 'http://www.rclbbs.com/'.$content;
                            if (!file_exists($img_path)){
                                mkdir($img_path,0777,true);
                            }
                            $local_image = $img_path.'/rclbbs_'.basename($content);
                            if(!is_file($local_image)){
                                file_put_contents($local_image,file_get_contents($content));
                            }
                            return str_replace(SPIDER_FILE_SAVE_URL,'',$local_image);
                        }else{
                            return false;
                        }
                    })
                ),'','UTF-8','GBK',true)->data;

                $add_data = [
                    'url' => $cv['url']
                ];
                $add_data['pid'] = 20;
                $add_data['title'] = $cv['title'];
                $add_data['contact'] = $cv['contact'];
                $add_data['phone'] = '';
                $add_data['content'] = $base_data[0]['content'];
                $add_data['img_data'] = json_encode($img_data,JSON_UNESCAPED_UNICODE);
                $add_data['province_id'] = 0;
                $add_data['city'] = '';
                $add_data['area_id'] = 0;
                $add_data['pub_date'] = $cv['time'];
                $add_data['contact_address'] = '';
                $add_data['create_time'] = _time();

                $this->dbm->insert('collect_data',$add_data);
            }
        }

        $this->stop_task(20);
    }
}