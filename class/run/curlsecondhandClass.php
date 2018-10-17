<?php
/**
 * Created by PhpStorm.
 * User: kangly
 * Date: 2018/7/30
 * Time: 09:53
 */
require_once '../base.php';
use QL\QueryList;

//抓取绍兴E网
class curlsecondhandClass extends baseClass
{
    function run()
    {
        $this->curl_secondhand();
    }

    protected function curl_secondhand()
    {
        $this->start_task(22);

        $url_data = [
            'https://secondhand.e0575.com/list.php?cIx=6',
            'https://secondhand.e0575.com/list.php?cIx=8',
            'https://secondhand.e0575.com/list.php?cIx=29',
            'https://secondhand.e0575.com/list.php?cIx=13'
        ];

        $this_day = date('Y-m-d');
        $img_path = SPIDER_FILE_SAVE_URL.'/Upload/'.date('Ymd');

        foreach($url_data as $v)
        {
            if(!$this->load_task_state(22)){
                exit;
            }

            sleep(6);

            $list_data = QueryList::Query($v, array(
                'url' => array('.es_list1_l1 li>a', 'href','',function($content){
                    return 'https://secondhand.e0575.com/'.$content;
                }),
                'title' => array('.es_list1_l1 li>a', 'text'),
                'time' => array('.es_list1_l1 .time1', 'text')
            ))->data;

            if(empty($list_data)){
                $this->stop_task(22);
                exit;
            }

            foreach($list_data as $cv)
            {
                if(!$this->load_task_state(22)){
                    exit;
                }

                if(strpos($cv['time'],'今天') === false){
                    break;
                }

                $is_change = $this->dbm->select('collect_data',['id'],['url'=>$cv['url'],'pid'=>22]);
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
                        $this->stop_task(22);
                        exit;
                    }

                    $base_data = QueryList::Query($source,array(
                        'field' => array('.es_show1_d1_x','html','',function($content){
                            return strip_tags($content,'<br>');
                        }),
                        'content' => array('.es_show1_d1_p','text','-br -div -img')
                    ))->data;

                    $field_data = explode('<br>',$base_data[0]['field']);

                    $contact = '';
                    $phone = '';
                    foreach($field_data as $v)
                    {
                        if($v){
                            if(strpos($v,'联系人：') !== false){
                                $contact = str_replace('联系人：','',$v);
                            }

                            if(strpos($v,'联系方式：') !== false){
                                $phone = str_replace('联系方式：','',$v);
                            }
                        }
                    }

                    $img_data = QueryList::Query($source,array(
                        'img' => array('.es_show1_d1_p img:lt(2)','src','-div',function($content) use($img_path){
                            if($content){
                                if (!file_exists($img_path)){
                                    mkdir($img_path,0777,true);
                                }
                                $local_image = $img_path.'/sxew_'.basename($content);
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
                        'url' => $cv['url']
                    ];
                    $add_data['pid'] = 22;
                    $add_data['title'] = $cv['title'];
                    $add_data['contact'] = $contact;
                    $add_data['phone'] = $phone;
                    $add_data['content'] = $base_data[0]['content'];
                    $add_data['img_data'] = json_encode($img_data,JSON_UNESCAPED_UNICODE);
                    $add_data['province_id'] = 12;//浙江
                    $add_data['city'] = '绍兴';
                    $add_data['area_id'] = 123;//绍兴
                    $add_data['pub_date'] = $this_day;
                    $add_data['contact_address'] = '';
                    $add_data['create_time'] = _time();

                    $this->dbm->insert('collect_data',$add_data);
                }
            }
        }

        $this->stop_task(22);
    }
}