<?php
/**
 * Created by PhpStorm.
 * User: kangly
 * Date: 2018/7/26
 * Time: 16:06
 */
require_once '../base.php';
use QL\QueryList;

//抓取邳州论坛
class curlpzzcClass extends baseClass
{
    function run()
    {
        $this->curl_pzzc();
    }

    protected function curl_pzzc()
    {
        $this->start_task(16);

        $url_data = [
            'http://www.pzzc.net/forum.php?mod=forumdisplay&fid=33&filter=author&orderby=dateline&typeid=106',
            'http://www.pzzc.net/forum.php?mod=forumdisplay&fid=33&filter=author&orderby=dateline&typeid=105'
        ];

        $this_day = date('Y-n-j');
        $img_path = SPIDER_FILE_SAVE_URL.'/Upload/'.date('Ymd');

        foreach($url_data as $v)
        {
            if(!$this->load_task_state(16)){
                exit;
            }

            sleep(6);

            $list_data = QueryList::Query($v, array(
                'url' => array('tr th a.xst','href','',function($content){
                    return 'http://www.pzzc.net/'.$content;
                }),
                'title' => array('tr th a.xst','text'),
                'contact' => array('tr .by:eq(0) cite a','text'),
                'time' => array('tr .by:eq(0) em span','text','',function($content){
                    $date_data = explode(' ',$content);
                    return $date_data[0];
                })
            ), 'table#threadlisttableid tbody[id^="normalthread_"]', 'UTF-8', 'GBK', true)->data;

            if(empty($list_data)){
                $this->stop_task(16);
                exit;
            }

            foreach($list_data as $cv)
            {
                if(!$this->load_task_state(16)){
                    exit;
                }

                if($cv['time'] != $this_day){
                    break;
                }

                $is_change = $this->dbm->select('collect_data', ['id'], ['url'=>$cv['url'],'pid'=>16]);
                if(empty($is_change))
                {
                    sleep(6);

                    $details = QueryList::run('Request',[
                        'target' => $cv['url'],
                        'method' => 'GET',
                        'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.181 Safari/537.36',
                        'cookiePath' => '../cookie.txt',
                        'timeout' => '30'
                    ]);

                    $source = $details->html;
                    if(!$source){
                        $this->stop_task(16);
                        exit;
                    }

                    $base_data = QueryList::Query($source,array(
                        'content' => array('.t_f:eq(0)','text','-br -img -div -a -span')
                    ),'','UTF-8','GBK',true)->data;

                    $img_data = QueryList::Query($source,array(
                        'img' => array('.zoom:lt(2)','file','',function($content) use($img_path){
                            if($content){
                                $is_full = strpos($content,'http://');
                                if($is_full === false){
                                    $content = 'http://www.pzzc.net/'.$content;
                                }
                                $image = $content;
                                $p = strpos($content,'?watermark');
                                if($p !== false){
                                    $image = substr($content,0,$p);
                                }
                                if (!file_exists($img_path)){
                                    mkdir($img_path,0777,true);
                                }
                                $local_image = $img_path.'/pzzc_'.basename($image);
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
                    $add_data['pid'] = 16;
                    $add_data['title'] = $cv['title'];
                    $add_data['contact'] = $cv['contact'];
                    $add_data['phone'] = '';
                    $add_data['content'] = $base_data[0]['content'];
                    $add_data['img_data'] = json_encode($img_data,JSON_UNESCAPED_UNICODE);
                    $add_data['province_id'] = 11;//江苏
                    $add_data['city'] = '徐州';
                    $add_data['area_id'] = 107;//徐州
                    $add_data['pub_date'] = $cv['time'];
                    $add_data['contact_address'] = '';
                    $add_data['create_time'] = _time();

                    $this->dbm->insert('collect_data',$add_data);
                }
            }
        }

        $this->stop_task(16);
    }
}