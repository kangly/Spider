<?php
/**
 * Created by PhpStorm.
 * User: kangly
 * Date: 2018/7/26
 * Time: 11:26
 */
require_once '../base.php';
use QL\QueryList;

//抓取处理网,每个列表只抓取一页
class curlchuliClass extends baseClass
{
    function run()
    {
        $this->curl_chuli();
    }

    protected function curl_chuli()
    {
        $this->start_task(13);

        $this_day = date('Y-m-d');
        $img_path = SPIDER_FILE_SAVE_URL.'/Upload/'.date('Ymd');

        //获取处理网所有待抓取列表页地址
        $url = '../ext/chuli.json';
        $json_data = file_get_contents($url);
        $data = json_decode($json_data,true);

        foreach($data as $v)
        {
            if(!$this->load_task_state(13)){
                exit;
            }

            sleep(6);

            $list_data = QueryList::Query($v['url'], array(
                'title' => array('h5 span a','text'),
                'url' => array('h5 span a','href')
            ))->data;

            if(empty($list_data)){
                $this->stop_task(13);
                exit;
            }

            foreach($list_data as $cv)
            {
                if(!$this->load_task_state(13)){
                    exit;
                }

                $is_change = $this->dbm->select('collect_data',['id'],['url'=>$cv['url'],'pid'=>13]);
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
                        $this->stop_task(13);
                        exit;
                    }

                    $base_data = QueryList::Query($source,array(
                        'time' => array('.d-ltop-t span:eq(1) label','text','',function($content){
                            return substr($content,0,10);
                        }),
                        'province' => array('.addressp span:eq(0) a:eq(0)','text'),
                        'city' => array('.addressp span:eq(0) a:eq(1)','text'),
                        'contact' => array('.contactman span','text'),
                        'phone_img' => array('.phonetab img','src'),
                        'content' => array('.containbox3 p:eq(0)','text','-h3 -div')
                    ))->data;

                    if($base_data[0]['time']!=$this_day){
                        break;
                    }

                    $img_data = QueryList::Query($source,array(
                        'img' => array('#img_ul2 img:lt(2)','src','',function($content) use($img_path){
                            if($content){
                                if (!file_exists($img_path)){
                                    mkdir($img_path,0777,true);
                                }
                                $local_image = $img_path.'/chuli_'.basename($content);
                                if(!is_file($local_image)){
                                    file_put_contents($local_image,file_get_contents($content));
                                }
                                return str_replace(SPIDER_FILE_SAVE_URL,'',$local_image);
                            }else{
                                return false;
                            }
                        })
                    ))->data;

                    $area_province = [];
                    if($base_data[0]['province']){
                        $ap_sql = sprintf("select areaid from destoon_area where parentid=0 and areaname like '%%%s%%'",$base_data[0]['province']);
                        $area_province = $this->dbm->query($ap_sql)->fetch();
                    }

                    $area_city = [];
                    if($base_data[0]['city']){
                        $ac_sql = sprintf("select areaid from destoon_area where parentid>0 and areaname like '%%%s%%'",$base_data[0]['city']);
                        $area_city = $this->dbm->query($ac_sql)->fetch();
                    }

                    $add_data = [
                        'url' => $cv['url']
                    ];
                    $add_data['pid'] = 13;
                    $add_data['title'] = $cv['title'];
                    $add_data['contact'] = $base_data[0]['contact'];
                    $add_data['phone_img'] = $base_data[0]['phone_img'];
                    $add_data['content'] = $base_data[0]['content'];
                    $add_data['img_data'] = json_encode($img_data,JSON_UNESCAPED_UNICODE);
                    $add_data['province_id'] = $area_province['areaid']>0?$area_province['areaid']:0;
                    $add_data['city'] = $base_data[0]['city']?$base_data[0]['city']:$base_data[0]['province'];
                    $add_data['area_id'] = $area_city['areaid']>0?$area_city['areaid']:0;
                    $add_data['pub_date'] = $base_data[0]['time'];
                    $add_data['create_time'] = _time();

                    $this->dbm->insert('collect_data',$add_data);
                }
            }
        }

        $this->stop_task(13);
    }

    //获取处理网所有待抓取列表页地址
    protected function curl_chuli_url()
    {
        require_once '../vendor/autoload.php';

        $base_url = 'http://www.51chuli.com/category/';

        $base_data = QueryList::Query($base_url,array(
            'url' => array('.gridtable a','href','',function($content){
                return $content.'000100/';
            })
        ))->data;

        file_put_contents('../ext/chuli.json',json_encode($base_data,JSON_UNESCAPED_UNICODE));
    }
}