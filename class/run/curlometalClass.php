<?php
/**
 * Created by PhpStorm.
 * User: kangly
 * Date: 2018/7/19
 * Time: 13:02
 */
require_once '../base.php';
use QL\QueryList;

//抓取全球金属网信息,只看供应、求购(其实就这两项),只要普通会员的信息
class curlometalClass extends baseClass
{
    function run()
    {
        $this->curl_ometal();
    }

    protected function curl_ometal()
    {
        $this->start_task(5);

        $base_url = 'http://biz.ometal.com/List.html?AreaID=&ClassID=8&OrderType=1&InfoType=&Page=';
        $date_time = date('Y-m-d');
        $this_day = date('Y-n-d').' 00:00:00';
        $img_path = SPIDER_FILE_SAVE_URL.'/Upload/'.date('Ymd');
        $page = 1;

        while(true)
        {
            if(!$this->load_task_state(5)){
                exit;
            }

            sleep(10);

            $url = $base_url.$page;

            $list_data = QueryList::Query($url, array(
                'url' => array('.title a:eq(0)','href'),
                'city' => array('.title a:eq(1)','text'),
                'phone' => array('.tel em','text'),
                'contact' => array('.tel','html','-em -a -img',function($content){
                    $content = _str_replace($content);
                    return $content;
                }),
                'type' => array('.pticon','title','',function($content){
                    if($content=='全球金属网商机普通会员'){
                        return 1;
                    }else{
                        return 0;
                    }
                })
            ), '#SInfolist table', 'UTF-8', 'GBK', true)->data;

            //采集列表源解析失败,退出 可能情况:1页面布局改变,2采集被封
            if(empty($list_data)){
                $this->stop_task(5);
                exit;
            }

            foreach($list_data as $lk=>$lv)
            {
                if(!$this->load_task_state(5)){
                    exit;
                }

                //只抓取普通会员的信息
                if(!$lv['type']){
                    continue;
                }

                $view_url = $lv['url'];
                $is_change = $this->dbm->select('collect_data', ['id'], ['url'=>$view_url,'pid'=>5]);
                if(empty($is_change))
                {
                    sleep(10);

                    $base_data = QueryList::Query($view_url,array(
                        'title' => array('h1','text'),
                        'content' => array('.Content','text'),
                        'time' => array('.artInfo span','text','-div -a')
                    ),'','UTF-8','GBK',true)->data;

                    //采集列表源解析失败,退出 可能情况:1页面布局改变,2采集被封
                    if(empty($base_data)){
                        $this->stop_task(5);
                        exit;
                    }

                    //默认按时间倒序排列,遇到时间非当天的直接退出
                    $this_time = str_replace('发布时间:','',$base_data[0]['time']);
                    if(!$this_time || $this_time<$this_day){
                        $this->stop_task(5);
                        exit;
                    }

                    $img_data = QueryList::Query($view_url,array(
                        'img' => array('.SmallPic img:lt(2)','src','',function($content) use($img_path){
                            if($content){
                                if (!file_exists($img_path)){
                                    mkdir($img_path,0777,true);
                                }
                                $local_image = $img_path.'/ometal_'.basename($content);
                                if(!is_file($local_image)){
                                    file_put_contents($local_image,file_get_contents($content));
                                }
                                return str_replace(SPIDER_FILE_SAVE_URL,'',$local_image);
                            }else{
                                return false;
                            }
                        })
                    ),'','UTF-8','GBK',true)->data;

                    $contact = '';
                    if($lv['contact']){
                        $contact = str_replace(['电话：','联系人：'],'',$lv['contact']);
                    }

                    $city = $lv['city'];
                    $area_province = [];
                    $area_city = [];
                    if($city)
                    {
                        $ac_sql = sprintf("select areaid from destoon_area where parentid>0 and areaname like '%%%s%%'",$city);
                        $area_city = $this->dbm->query($ac_sql)->fetch();

                        if(empty($area_city))
                        {
                            $ap_sql = sprintf("select areaid from destoon_area where parentid=0 and areaname like '%%%s%%'",$city);
                            $area_province = $this->dbm->query($ap_sql)->fetch();
                        }
                        else
                        {
                            if($area_city['areaid']>0){
                                $ap_sql = sprintf("select parentid as areaid from destoon_area where areaid=%d",$area_city['areaid']);
                                $area_province = $this->dbm->query($ap_sql)->fetch();
                            }
                        }
                    }

                    $add_data = [
                        'url' => $view_url
                    ];
                    $add_data['pid'] = 5;
                    $add_data['title'] = $base_data[0]['title'];
                    $add_data['contact'] = $contact;
                    $add_data['phone'] = $lv['phone'];
                    $add_data['content'] = $base_data[0]['content'];
                    $add_data['img_data'] = json_encode($img_data,JSON_UNESCAPED_UNICODE);
                    $add_data['province_id'] = $area_province['areaid']>0?$area_province['areaid']:0;;
                    $add_data['city'] = $city;
                    $add_data['area_id'] = $area_city['areaid']>0?$area_city['areaid']:0;
                    $add_data['pub_date'] = $date_time;
                    $add_data['contact_address'] = $city;
                    $add_data['create_time'] = _time();

                    $this->dbm->insert('collect_data',$add_data);
                }
            }

            $page++;
        }

        $this->stop_task(5);
    }
}