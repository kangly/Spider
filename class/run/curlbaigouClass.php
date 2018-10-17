<?php
/**
 * Created by PhpStorm.
 * User: kangly
 * Date: 2018/7/25
 * Time: 15:22
 */
require_once '../base.php';
use QL\QueryList;

//抓取白沟河网,按时间倒序排序,内容较少,只抓取一页
class curlbaigouClass extends baseClass
{
    function run()
    {
        $this->curl_baigou();
    }

    protected function curl_baigou()
    {
        $this->start_task(11);

        $url_data = [
            'http://www.baigou.net/list-fid-427-sy_271_ya_96s1-4.html',
            'http://www.baigou.net/list-fid-427-sy_271_ya_96s1-5.html'
        ];

        $this_day = strtotime(date('Y-m-d'));
        $img_path = SPIDER_FILE_SAVE_URL.'/Upload/'.date('Ymd');

        foreach($url_data as $v)
        {
            if(!$this->load_task_state(11)){
                exit;
            }

            sleep(6);

            $list_data = QueryList::Query($v, array(
                'url' => array('.info1 a','href')
            ),'','UTF-8','GBK',true)->data;

            if(empty($list_data)){
                $this->stop_task(11);
                exit;
            }

            foreach($list_data as $cv)
            {
                if(!$this->load_task_state(11)){
                    exit;
                }

                $is_change = $this->dbm->select('collect_data',['id'],['url'=>$cv['url'],'pid'=>11]);
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
                        $this->stop_task(11);
                        exit;
                    }

                    $base_data = QueryList::Query($source,array(
                        'title' => array('h2','text'),
                        'time' => array('.ben-info .cen','text','-a'),
                        'contact' => array('.bx-ben-r-a a','text')
                    ),'','UTF-8','GBK',true)->data;

                    $time_str = $base_data[0]['time'];
                    $time_data = explode('&#160;&#160;&#160;',$time_str);
                    $time = str_replace('发布时间：','',$time_data[0]);
                    $time = substr($time,0,10);
                    if(strtotime($time)!=$this_day){
                        continue;
                    }

                    sleep(6);

                    $phone_url_data = parse_url($cv['url']);
                    $phone_path = $phone_url_data['path'];
                    $phone_url = 'http://www.baigou.net/3gg'.$phone_path;

                    $phone_details = QueryList::run('Request',[
                        'target' => $phone_url,
                        'method' => 'GET',
                        'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.181 Safari/537.36',
                        'cookiePath' => '../cookie.txt',
                        'timeout' => '30'
                    ]);

                    $phone_source = $phone_details->html;
                    $img_data = [];
                    $phone = '';
                    $content = '';
                    if($phone_source){
                        $img_data = QueryList::Query($phone_source,array(
                            'img' => array('.BenContent>img:lt(2)','src','',function($content) use($img_path){
                                if($content){
                                    if (!file_exists($img_path)){
                                        mkdir($img_path,0777,true);
                                    }
                                    $local_image = $img_path.'/baigou_'.basename($content);
                                    if(!is_file($local_image)){
                                        file_put_contents($local_image,file_get_contents($content));
                                    }
                                    return str_replace(SPIDER_FILE_SAVE_URL,'',$local_image);
                                }else{
                                    return false;
                                }
                            })
                        ))->data;

                        $phone_data = QueryList::Query($phone_source, array(
                            'phone' => array('.teldian1 a','href','',function($content){
                                return str_replace('tel:','',$content);
                            }),
                            'content' => array('.lists:last','text','-span')
                        ))->data;

                        $phone = $phone_data[0]['phone'];
                        $content = $phone_data[0]['content'];
                    }

                    $add_data = [
                        'url' => $cv['url']
                    ];
                    $add_data['pid'] = 11;
                    $add_data['title'] = $base_data[0]['title'];
                    $add_data['contact'] = $base_data[0]['contact'];
                    $add_data['phone'] = $phone;
                    $add_data['content'] = $content;
                    $add_data['img_data'] = json_encode($img_data,JSON_UNESCAPED_UNICODE);
                    $add_data['province_id'] = 5;//河北
                    $add_data['city'] = '保定';
                    $add_data['area_id'] = 40;//保定
                    $add_data['pub_date'] = $time;
                    $add_data['create_time'] = _time();

                    $this->dbm->insert('collect_data',$add_data);
                }
            }
        }

        $this->stop_task(11);
    }
}