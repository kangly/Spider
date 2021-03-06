<?php
/**
 * Created by PhpStorm.
 * User: kangly
 * Date: 2018/7/25
 * Time: 13:03
 */
require_once '../base.php';
use QL\QueryList;

//抓取宝坻在线,按时间倒序排序,内容较少,只抓取一页
class curlbaodiClass extends baseClass
{
    function run()
    {
        $this->curl_baodi();
    }

    protected function curl_baodi()
    {
        $this->start_task(10);

        $url_data = [
            'http://www.baodi.ccoo.cn/post/ershou/bangong/list-0-0-0-0-0-0-1-0-0-0.html',
            'http://www.baodi.ccoo.cn/post/ershou/qita/list-0-0-0-0-0-0-1-0-0-0.html'
        ];

        $this_date = date('Y-m-d');
        $this_day = strtotime($this_date);
        $img_path = SPIDER_FILE_SAVE_URL.'/Upload/'.date('Ymd');

        foreach($url_data as $v)
        {
            if(!$this->load_task_state(10)){
                exit;
            }

            sleep(6);

            $list_data = QueryList::Query($v, array(
                'url' => array('.list-xx ul li.bt a','href','',function($content){
                    return 'http://www.baodi.ccoo.cn'.$content;
                }),
                'time' => array('.list-xx ul li.rq','text')
            ))->data;

            if(empty($list_data)){
                $this->stop_task(10);
                exit;
            }

            foreach($list_data as $cv)
            {
                if(!$this->load_task_state(10)){
                    exit;
                }

                if($cv['time'] && strtotime($cv['time'])!=$this_day){
                    break;
                }

                $is_change = $this->dbm->select('collect_data',['id'],['url'=>$cv['url'],'pid'=>10]);
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
                        $this->stop_task(10);
                        exit;
                    }

                    $cdata = QueryList::Query($source,array(
                        'field' => array('dl','html','-.teldd',function($content){
                            $content = strip_tags($content,'<dd>');
                            $content = str_replace(['--','<dd>'],'',$content);
                            $content = _str_replace($content);
                            return explode('</dd>',$content);
                        })
                    ),'','UTF-8','GBK',true)->data;

                    $cdata = $cdata[0]['field'];
                    $contact = '';
                    foreach($cdata as $dv){
                        if($dv){
                            if(strpos($dv,'联系人：') !== false){
                                $contact = str_replace('联系人：','',$dv);
                                break;
                            }
                        }
                    }

                    $base_data = QueryList::Query($source,array(
                        'tel' => array('.gettel','id'),
                        'title' => array('h1','text'),
                        'content' => array('.show-content p:first','text')
                    ),'','UTF-8','GBK',true)->data;

                    $tel = $base_data[0]['tel'];
                    $phone = '';
                    if($tel)
                    {
                        sleep(6);

                        $phone_url = 'http://res.pccoo.cn/post/images/inc/tel.asp?tel='.$tel;
                        $phone_data = QueryList::Query($phone_url, array(
                            'phone' => array('.tdc2:eq(0)','text','-a',function($content){
                                return _str_replace($content);
                            })
                        ),'','UTF-8','UTF-8')->data;

                        $phone = $phone_data[0]['phone'];
                    }

                    $img_data = QueryList::Query($source,array(
                        'img' => array('.show-content img:lt(2)','src','',function($content) use($img_path){
                            if($content){
                                if (!file_exists($img_path)){
                                    mkdir($img_path,0777,true);
                                }
                                $local_image = $img_path.'/baodi_'.basename($content);
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
                    $add_data['pid'] = 10;
                    $add_data['title'] = $base_data[0]['title'];
                    $add_data['contact'] = $contact;
                    $add_data['phone'] = $phone;
                    $add_data['content'] = $base_data[0]['content'];
                    $add_data['img_data'] = json_encode($img_data,JSON_UNESCAPED_UNICODE);
                    $add_data['province_id'] = 3;//天津
                    $add_data['city'] = '天津';
                    $add_data['area_id'] = 0;
                    $add_data['pub_date'] = $cv['time']?$cv['time']:$this_date;
                    $add_data['create_time'] = _time();

                    $this->dbm->insert('collect_data',$add_data);
                }
            }
        }

        $this->stop_task(10);
    }
}