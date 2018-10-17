<?php
/**
 * Created by PhpStorm.
 * User: kangly
 * Date: 2018/7/26
 * Time: 13:50
 */
require_once '../base.php';
use QL\QueryList;

//抓取临沂分类供求网
class curllywwwClass extends baseClass
{
    function run()
    {
        $this->curl_lywww();
    }

    protected function curl_lywww()
    {
        $this->start_task(14);

        $month_day = date('m-d');
        $this_day = date('Y-m-d');
        $img_path = SPIDER_FILE_SAVE_URL.'/Upload/'.date('Ymd');

        $url_data = [
            'http://gq.lywww.com/list_2500_0_1_1.html',
            'http://gq.lywww.com/list_2500_0_2_1.html',
            'http://gq.lywww.com/list_2500_0_3_1.html',
            'http://gq.lywww.com/list_2500_0_4_1.html',
            'http://gq.lywww.com/list_2500_0_5_1.html'
        ];

        foreach($url_data as $v)
        {
            if(!$this->load_task_state(14)){
                exit;
            }

            sleep(6);

            $list_data = QueryList::Query($v, array(
                'title' => array('.title','text'),
                'url' => array('.title','href','',function($content){
                    return 'http://gq.lywww.com'.$content;
                }),
                'info' => array('.gq_pro_cen','text','-div',function($content){
                    return str_replace(['联系人:','电话：'],'',$content);
                }),
                'time' => array('.gq_pro_time','text')
            ))->data;

            foreach($list_data as $cv)
            {
                if(!$this->load_task_state(14)){
                    exit;
                }

                if($cv['time']!=$month_day){
                    break;
                }

                $view_url = $cv['url'];

                sleep(6);

                $page_id = pathinfo($view_url,PATHINFO_FILENAME);
                $date_url = 'http://gq.lywww.com/plus/getdate_js.php?aid='.$page_id;

                $date_details = QueryList::run('Request',[
                    'target' => $date_url,
                    'method' => 'GET',
                    'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.181 Safari/537.36',
                    'cookiePath' => '../cookie.txt',
                    'timeout' => '30'
                ]);

                $date_source = $date_details->html;
                $date_source = '20'.str_replace(["document.getElementById('uptime').innerHTML='","'"],'',$date_source);
                if($date_source!=$this_day){
                    break;
                }

                $info_data = explode(' ',$cv['info']);
                $contact = $info_data[0];
                $phone = $info_data[1];

                $is_change = $this->dbm->select('collect_data',['id'],['url'=>$view_url,'pid'=>14]);
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
                        $this->stop_task(14);
                        exit;
                    }

                    $base_data = QueryList::Query($source,array(
                        'content' => array('.gq_gg_zhengwen','text','-img',function($content){
                            return _str_replace($content);
                        })
                    ))->data;

                    $img_data = QueryList::Query($source,array(
                        'img' => array('.gq_gg_zhengwen img:lt(2)','src','',function($content) use($img_path){
                            if($content){
                                $content = 'http://gq.lywww.com'.$content;
                                if (!file_exists($img_path)){
                                    mkdir($img_path,0777,true);
                                }
                                $local_image = $img_path.'/lywww_'.basename($content);
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
                    $add_data['pid'] = 14;
                    $add_data['title'] = $cv['title'];
                    $add_data['contact'] = $contact;
                    $add_data['phone'] = $phone;
                    $add_data['content'] = $base_data[0]['content'];
                    $add_data['img_data'] = json_encode($img_data,JSON_UNESCAPED_UNICODE);
                    $add_data['province_id'] = 16;//山东
                    $add_data['city'] = '临沂';
                    $add_data['area_id'] = 178;//临沂
                    $add_data['pub_date'] = $cv['time'];
                    $add_data['contact_address'] = '';
                    $add_data['create_time'] = _time();

                    $this->dbm->insert('collect_data',$add_data);
                }
            }
        }

        $this->stop_task(14);
    }
}