<?php
/**
 * Created by PhpStorm.
 * User: kangly
 * Date: 2018/7/16
 * Time: 11:21
 */
require_once '../base.php';
use QL\QueryList;

//采集峰峰信息港,内容很少,默认只采集一页
//此站点是河北省邯郸市峰峰城区的网站,默认省市为河北省邯郸市
class curlfengfengClass extends baseClass
{
    function run()
    {
        $this->curl_fengfeng();
    }

    protected function curl_fengfeng()
    {
        $this->start_task(3);

        $url_data = [
            'http://www.fengfeng.cc/xinxi/chushou/',
            'http://www.fengfeng.cc/xinxi/qiugou/'
        ];

        $this_day = strtotime(date('Y-m-d'));
        $img_path = SPIDER_FILE_SAVE_URL.'/Upload/'.date('Ymd');

        foreach($url_data as $v)
        {
            if(!$this->load_task_state(3)){
                exit;
            }

            sleep(6);

            $list_data = QueryList::Query($v,array(
                'url' => array('.list_box dl dt a','href'),
                'time' => array('.list_box dl dt span','text'),
            ),'','UTF-8','GBK',true)->data;

            if(empty($list_data)){
                $this->stop_task(3);
                exit;
            }

            foreach($list_data as $cv)
            {
                if(!$this->load_task_state(3)){
                    exit;
                }

                if(strtotime($cv['time']) != $this_day){
                    break;
                }

                sleep(6);

                $is_change = $this->dbm->select('collect_data', ['id'], ['url'=>$cv['url'],'pid'=>3]);
                if(empty($is_change))
                {
                    $details = QueryList::run('Request',[
                        'target' => $cv['url'],
                        'host' => 'www.fengfeng.cc',
                        'method' => 'GET',
                        'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.181 Safari/537.36',
                        'cookiePath' => '../cookie.txt',
                        'timeout' =>'30'
                    ]);

                    $source = $details->html;
                    if(!$source){
                        $this->stop_task(3);
                        exit;
                    }

                    $base_data = QueryList::Query($source,array(
                        'title' => array('.text_title h1','text'),
                        'content' => array('.con_box:eq(0)','html','-img',function($content){
                            return str_replace('<p></p>','',$content);
                        }),
                    ),'','UTF-8','GBK',true)->data;

                    if(empty($base_data)){
                        $this->stop_task(3);
                        exit;
                    }

                    $img_data = QueryList::Query($source,array(
                        'img' => array('.con_box:eq(0) img:lt(2)','src','',function($content) use($img_path){
                            if($content && $content !='http://www.fengfeng.cc/e/data/images/notimg.gif'){
                                $image = substr($content,0);
                                if (!file_exists($img_path)){
                                    mkdir($img_path,0777,true);
                                }
                                $local_image = $img_path.'/ff_'.basename($image);
                                if(!is_file($local_image)){
                                    file_put_contents($local_image,file_get_contents($content));
                                }
                                return str_replace(SPIDER_FILE_SAVE_URL,'',$local_image);
                            }else{
                                return false;
                            }
                        })
                    ),'','UTF-8','GBK',true)->data;

                    if(empty($img_data[0]['img'])){
                        $img_data = [];
                    }

                    $contact_data = QueryList::Query($source,array(
                        'field' => array('.lianxifangshi dl dt','text','',function($content){
                            return _str_replace($content);
                        })
                    ))->data;

                    $phone = '';
                    $contact = '';
                    $address = '';
                    foreach($contact_data as $av)
                    {
                        if($av['field'])
                        {
                            if(strpos($av['field'],'联系电话:') !== false){
                                $phone = str_replace('联系电话:','',$av['field']);
                            }

                            if(strpos($av['field'],'联系人:') !== false){
                                $contact = str_replace('联系人:','',$av['field']);
                            }

                            if(strpos($av['field'],'联系地址：') !== false){
                                $address = str_replace('联系地址：','',$av['field']);
                            }
                        }
                    }

                    $add_data = [
                        'url' => $cv['url']
                    ];
                    $add_data['pid'] = 3;
                    $add_data['title'] = $base_data[0]['title'];
                    $add_data['contact'] = $contact;
                    $add_data['phone'] = $phone;
                    $add_data['content'] = $base_data[0]['content'];
                    $add_data['img_data'] = json_encode($img_data,JSON_UNESCAPED_UNICODE);
                    $add_data['province_id'] = 5;//河北
                    $add_data['city'] = '邯郸';
                    $add_data['area_id'] = 38;//邯郸
                    $add_data['pub_date'] = $cv['time'];
                    $add_data['contact_address'] = $address;
                    $add_data['create_time'] = _time();

                    $this->dbm->insert('collect_data',$add_data);
                }
            }
        }

        $this->stop_task(3);
    }
}