<?php
/**
 * Created by PhpStorm.
 * User: kangly
 * Date: 2018/7/24
 * Time: 17:41
 */
require_once '../base.php';
use QL\QueryList;

//抓取巩义搜,按时间倒序排序,内容过少,只抓取一页
class curlgysouClass extends baseClass
{
    function run()
    {
        $this->curl_gysou();
    }

    protected function curl_gysou()
    {
        $this->start_task(9);

        $url_data = [
            'https://www.gysou.com/category-catid-9-page-1.html',
            'https://www.gysou.com/category-catid-12-page-1.html',
            'https://www.gysou.com/category-catid-22-page-1.html',
            'https://www.gysou.com/category-catid-23-page-1.html'
        ];

        $this_day = date('Y-m-d');
        $this_year = date('Y');
        $img_path = SPIDER_FILE_SAVE_URL.'/Upload/'.date('Ymd');

        foreach($url_data as $v)
        {
            if(!$this->load_task_state(9)){
                exit;
            }

            sleep(6);

            $list_data = QueryList::Query($v, array(
                'url' => array('.contant_pt_li_list3-a a', 'href','',function($content){
                    return 'https://www.gysou.com'.$content;
                }),
                'title' => array('.contant_pt_li_list3-a a', 'text'),
                'time' => array('.contant_pt_li_list3-bw .gayf6', 'text')
            ), '.contant-con_pt_li', 'UTF-8', 'GBK', true)->data;

            if(empty($list_data)){
                $this->stop_task(9);
                exit;
            }

            foreach($list_data as $cv)
            {
                if(!$this->load_task_state(9)){
                    exit;
                }

                $cv['time'] = $this_year.'-'.str_replace(['月','日'],['-',''],$cv['time']);
                if($cv['time'] != $this_day){
                    break;
                }

                $is_change = $this->dbm->select('collect_data',['id'],['url'=>$cv['url'],'pid'=>9]);
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
                        $this->stop_task(9);
                        exit;
                    }

                    $base_data = QueryList::Query($source,array(
                        'contact' => array('.showconl_lx ul li:eq(0)','text','-strong -font',function($content){
                            return _str_replace($content);
                        }),
                        'phone' => array('.showconl_lx ul li:eq(1) em','text'),
                        'address' => array('.showconl_lx ul li:eq(4)','text','-strong',function($content){
                            return _str_replace($content);
                        }),
                        'content' => array('.maincon','html'),
                    ),'','UTF-8','GBK',true)->data;

                    $img_data = QueryList::Query($source,array(
                        'img' => array('.view_bd img:lt(2)','src','-div',function($content) use($img_path){
                            if($content){
                                if (!file_exists($img_path)){
                                    mkdir($img_path,0777,true);
                                }
                                $local_image = $img_path.'/gysou_'.basename($content);
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
                    $add_data['pid'] = 9;
                    $add_data['title'] = $cv['title'];
                    $add_data['contact'] = $base_data[0]['contact'];
                    $add_data['phone'] = $base_data[0]['phone'];
                    $add_data['content'] = $base_data[0]['content'];
                    $add_data['img_data'] = json_encode($img_data,JSON_UNESCAPED_UNICODE);
                    $add_data['province_id'] = 17;//河南
                    $add_data['city'] = '郑州';
                    $add_data['area_id'] = 183;//郑州
                    $add_data['pub_date'] = $cv['time'];
                    $add_data['contact_address'] = $base_data[0]['address'];
                    $add_data['create_time'] = _time();

                    $this->dbm->insert('collect_data',$add_data);
                }
            }
        }

        $this->stop_task(9);
    }
}