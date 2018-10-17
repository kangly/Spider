<?php
/**
 * Created by PhpStorm.
 * User: kangly
 * Date: 2018/7/26
 * Time: 09:30
 */
require_once '../base.php';
use QL\QueryList;

//抓取永年二手网,按时间倒序排序,内容过少,只抓取一页
class curlynianClass extends baseClass
{
    function run()
    {
        $this->curl_ynian();
    }

    protected function curl_ynian()
    {
        $this->start_task(12);

        $url_data = [
            'http://www.ynian.com/forum.php?mod=forumdisplay&fid=227&filter=sortid&sortid=12&searchsort=1&esjy_class=1&page=1',
            'http://www.ynian.com/forum.php?mod=forumdisplay&fid=227&filter=sortid&sortid=12&searchsort=1&esjy_class=14&page=1',
            'http://www.ynian.com/forum.php?mod=forumdisplay&fid=227&filter=sortid&sortid=12&searchsort=1&esjy_class=15&page=1',
            'http://www.ynian.com/forum.php?mod=forumdisplay&fid=227&filter=sortid&sortid=12&searchsort=1&esjy_class=18&page=1'
        ];

        $this_day = date('Y-n-j');
        $img_path = SPIDER_FILE_SAVE_URL.'/Upload/'.date('Ymd');

        foreach($url_data as $v)
        {
            if(!$this->load_task_state(12)){
                exit;
            }

            sleep(6);

            $list_data = QueryList::Query($v, array(
                'url' => array('.ltitle a','href','',function($content){
                    return 'http://www.ynian.com/'.$content;
                }),
                'title' => array('.ltitle a','text','-span',function($content){
                    return str_replace("\n",'',$content);
                }),
                'time1' => array('.ltime span','title'),
                'time2' => array('.ltime','text')
            ),'#moderate ul','UTF-8','GBK',true)->data;

            if(empty($list_data)){
                $this->stop_task(12);
                exit;
            }

            foreach($list_data as $cv)
            {
                if(!$this->load_task_state(12)){
                    exit;
                }

                //去掉置顶的
                if($cv['title']==''){
                    continue;
                }

                $cv['time'] = $cv['time1']?$cv['time1']:$cv['time2'];
                if($cv['time']!=$this_day){
                    break;
                }

                $view_url = $cv['url'];
                $is_change = $this->dbm->select('collect_data',['id'],['url'=>$view_url,'pid'=>12]);
                if(empty($is_change))
                {
                    sleep(6);

                    $details = QueryList::run('Request',[
                        'target' => $view_url,
                        'method' => 'GET',
                        'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.181 Safari/537.36',
                        'cookiePath' => '../cookie.txt',
                        'timeout' => '30'
                    ]);

                    $source = $details->html;
                    if(!$source){
                        $this->stop_task(12);
                        exit;
                    }

                    $base_data = QueryList::Query($source,array(
                        'content' => array('.t_f:eq(0)','text','-ignore_js_op -img -div -br'),
                        'contact' => array('.favatar:eq(0) .authi a','text')
                    ),'','UTF-8','GBK',true)->data;

                    $img_data = QueryList::Query($source,array(
                        'img' => array('.t_f:eq(0) img:lt(2)','file','',function($content) use($img_path){
                            if($content){
                                if (!file_exists($img_path)){
                                    mkdir($img_path,0777,true);
                                }
                                $local_image = $img_path.'/ynian_'.basename($content);
                                if(!is_file($local_image)){
                                    file_put_contents($local_image,file_get_contents($content));
                                }
                                return str_replace(SPIDER_FILE_SAVE_URL,'',$local_image);
                            }else{
                                return false;
                            }
                        })
                    ))->data;

                    $add_data = [
                        'url' => $view_url
                    ];
                    $add_data['pid'] = 12;
                    $add_data['title'] = $cv['title'];
                    $add_data['contact'] = $base_data[0]['contact'];
                    $add_data['phone'] = '';
                    $add_data['content'] = $base_data[0]['content'];
                    $add_data['img_data'] = json_encode($img_data,JSON_UNESCAPED_UNICODE);
                    $add_data['province_id'] = 5;//河北
                    $add_data['city'] = '邯郸';
                    $add_data['area_id'] = 38;//邯郸
                    $add_data['pub_date'] = $cv['time'];
                    $add_data['contact_address'] = '';
                    $add_data['create_time'] = _time();

                    $this->dbm->insert('collect_data',$add_data);
                }
            }
        }

        $this->stop_task(12);
    }
}