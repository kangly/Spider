<?php
/**
 * Created by PhpStorm.
 * User: kangly
 * Date: 2018/5/8
 * Time: 09:31
 */
require_once '../base.php';
use QL\QueryList;

//抓取赶集网办公设备当天信息
class curlganjiClass extends baseClass
{
    function run()
    {
        $this->curl_ganji();
    }

    public function curl_ganji()
    {
        $this->start_task(2);

        $this_year = date('Y');
        $this_day = date('Y-m-d');
        $img_path = SPIDER_FILE_SAVE_URL.'/Upload/'.date('Ymd');

        //获取赶集网所有办公设备城市列表
        $url = '../ext/ganji_urls.json';
        $json_data = file_get_contents($url);
        $data = json_decode($json_data,true);

        //开始循环采集每个城市下的信息
        foreach($data as $v)
        {
            sleep(10);//暂停10秒

            $url = $v;//办公设备城市链接
            $city_state = true;//是否继续抓取该城市下信息
            $page = 1;//该城市下抓取页数

            while(true)
            {
                if(!$this->load_task_state(2)){
                    exit;
                }

                //个人下的按照时间排序
                if($page==1){
                    $city_url = $url.'bangong/t1/';
                }else{
                    $city_url = $url.'bangong/o'.$page.'t1/';
                }

                $url_data = parse_url($city_url);
                $base_url = $url_data['host'];

                $details = QueryList::run('Request',[
                    'target' => $city_url,
                    'host' => $base_url,
                    'method' => 'GET',
                    'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.181 Safari/537.36',
                    'cookiePath' => '../cookie.txt',
                    'timeout' => '30'
                ]);

                $source = $details->html;
                //获取采集列表源失败,退出
                if(!$source)
                {
                    //赶集网会遇到提示页面不存在，其实是存在的问题
                    //遇到上述问题，重复操作一次
                    sleep(3);

                    $details = QueryList::run('Request',[
                        'target' => $city_url,
                        'host' => $base_url,
                        'method' => 'GET',
                        'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.181 Safari/537.36',
                        'cookiePath' => '../cookie.txt',
                        'timeout' => '30'
                    ]);

                    $source = $details->html;
                    if(!$source){
                        //$this->stop_task(2);
                        //exit;
                        continue;
                    }
                }

                //获取城市
                $pc_data = QueryList::Query($source,array(
                    'pc' => array('meta[name="location"]','content')
                ))->data;

                //采集列表源解析失败,退出 可能情况:1页面布局改变,2采集被封
                if(empty($pc_data)){
                    $this->stop_task(2);
                    exit;
                }

                //处理省市
                $pc = explode(';',$pc_data[0]['pc']);
                $province = str_replace('province=','',$pc[0]);
                $city = str_replace('city=','',$pc[1]);

                //获取列表中所有详情链接
                $data = QueryList::Query($source,array(
                    'url' => array('.ft-tit','href'),
                    'class1' => array('.js-item .ico-hot','class'),
                    'class2' => array('.js-item .ico-stick-yellow','class')
                ),'.layoutlist .list-bigpic')->data;

                //采集详情源解析失败,退出 可能情况:1页面布局改变,2采集被封
                if(empty($data)){
                    $this->stop_task(2);
                    exit;
                }

                foreach($data as $ck=>$cv)
                {
                    $view_url = $cv['url'];

                    //去掉精、顶和非本城市下的链接
                    if(strpos($view_url,$base_url)===false || $cv['class1'] || $cv['class2']){
                        continue;
                    }

                    if(!$this->load_task_state(2)){
                        exit;
                    }

                    $is_change = $this->dbm->select('collect_data', ['id'], ['url'=>$view_url,'pid'=>2]);
                    if(empty($is_change))
                    {
                        //暂停10秒
                        sleep(10);

                        $cv_details = QueryList::run('Request',[
                            'target' => $view_url,
                            'host' => $base_url,
                            'method' => 'GET',
                            'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.181 Safari/537.36',
                            'cookiePath' => '../cookie.txt',
                            'timeout' =>'30'
                        ]);

                        $cv_source = $cv_details->html;
                        if(!$cv_source)
                        {
                            sleep(3);

                            $cv_details = QueryList::run('Request',[
                                'target' => $view_url,
                                'host' => $base_url,
                                'method' => 'GET',
                                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.181 Safari/537.36',
                                'cookiePath' => '../cookie.txt',
                                'timeout' =>'30'
                            ]);
 
                            $cv_source = $cv_details->html;
                            if(!$cv_source){
                                //$this->stop_task(2);
                                //exit;
                                continue;
                            }
                        }

                        $data = QueryList::Query($cv_source,array(
                            'title' => array('.title-name','text'),
                            'time' => array('.pr-5','text','',function($content){
                                $content = _str_replace($content);
                                $content = str_replace('发布','',$content);
                                return substr($content,0,5);
                            }),
                            'phone' => array('.phoneNum-style','text','',function($content){
                                return str_replace(' ','',$content);
                            }),
                            'content_text' => array('.second-sum-cont','html','-div',function($content){
                                $content = _str_replace($content);
                                return preg_replace('#<!--[^\!\[]*?(?<!\/\/)-->#','',$content);
                            })
                        ))->data;

                        if(empty($data)){
                            $this->stop_task(2);
                            exit;
                        }

                        //赶集网不会显示发布时间的年份,只判断月-日
                        if($this_year.'-'.$data[0]['time'] != $this_day){
                            $city_state = false;
                            break;
                        }

                        //电脑端部分电话为图片,如果为空,通过手机端页面获取电话
                        $phone = $data[0]['phone'];
                        if(!$phone)
                        {
                            //暂停10秒
                            sleep(10);

                            $url_id = pathinfo($view_url,PATHINFO_FILENAME);
                            $city_base = str_replace('.ganji.com','',$base_url);
                            $phone_url = 'https://3g.ganji.com/'.$city_base.'_bangong/'.$url_id;

                            $phone_details = QueryList::run('Request',[
                                'target' => $phone_url,
                                'host' => '3g.ganji.com',
                                'method' => 'GET',
                                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.87 Safari/537.36',
                                'cookiePath' => '../cookie.txt',
                                'timeout' =>'30'
                            ]);

                            $phone_source = $phone_details->html;
                            if(!$phone_source)
                            {
                                sleep(3);

                                $phone_details = QueryList::run('Request',[
                                    'target' => $phone_url,
                                    'host' => '3g.ganji.com',
                                    'method' => 'GET',
                                    'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.87 Safari/537.36',
                                    'cookiePath' => '../cookie.txt',
                                    'timeout' =>'30'
                                ]);

                                $phone_source = $phone_details->html;
                                if(!$phone_source){
                                    //$this->stop_task(2);
                                    //exit;
                                    continue;
                                }
                            }

                            $phone_data = QueryList::Query($phone_source,array(
                                'phone' => array('.f15.fc-red','text')
                            ))->data;

                            $phone = $phone_data[0]['phone'];
                        }
						
                        //联系人
                        $contact = '';
                        $cdata = QueryList::Query($cv_source,array(
                            'field' => array('.det-infor li','text','-script -span -div -a -style',function($content){
                                return _str_replace($content);
                            })
                        ))->data;

						$ganji_type = '';
                        foreach($cdata as $v)
                        {
                            if($v['field']){
                                if(strpos($v['field'],'联系人：') !== false){
                                    $contact = str_replace('联系人：','',$v['field']);
                                }

                                if(strpos($v['field'],'类型：') !== false){
                                    $ganji_type = str_replace('类型：','',$v['field']);
                                }
                            }
                        }

                        //不抓取的类型
                        $no_allow_type = [
                            '农用机械','货架/展架','展柜','柜台','酒柜','打印机','复印机','激光一体机','投影仪','传真机','扫描仪','打印机耗材',
                            '电话机','交换机','无线座机/小灵通','400电话','其他通讯器材','其他美容美发','美容器材','美发器材','脱毛器/除毛刀',
                            '台球桌','台球杆','台球','乒乓球桌','跑步机','踏步机','收腹机/健腹器','健身车','仰卧板','哑铃','杠铃','拉力器/握力器',
                            '举重床','场馆设施','其他健身器材'
                        ];

                        if($ganji_type && in_array($ganji_type,$no_allow_type)){
                            //$city_state = false;
                            //break;
                            continue;
                        }

                        //图片仅抓取前三张
                        $img_data = QueryList::Query($cv_source,array(
                            'img' => array('.cont-box.pics img:lt(2)','src','',function($content) use($img_path){
                                if($content){
                                    if (!file_exists($img_path)){
                                        mkdir($img_path,0777,true);
                                    }
                                    $local_image = $img_path.'/gj_'.basename($content);
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
                        if($province){
                            $ap_sql = sprintf("select areaid from destoon_area where parentid=0 and areaname like '%%%s%%'",$province);
                            $area_province = $this->dbm->query($ap_sql)->fetch();
                        }

                        $area_city = [];
                        if($city){
                            $ac_sql = sprintf("select areaid from destoon_area where parentid>0 and areaname like '%%%s%%'",$city);
                            $area_city = $this->dbm->query($ac_sql)->fetch();
                        }

                        $add_data = [
                            'url' => $view_url
                        ];
                        $add_data['pid'] = 2;
                        $add_data['title'] = $data[0]['title'];
                        $add_data['contact'] = $contact;
                        $add_data['phone'] = $phone;
                        $add_data['content'] = $data[0]['content_text'];
                        $add_data['img_data'] = json_encode($img_data,JSON_UNESCAPED_UNICODE);
                        $add_data['province_id'] = $area_province['areaid']>0?$area_province['areaid']:0;
                        $add_data['city'] = $city;
                        $add_data['area_id'] = $area_city['areaid']>0?$area_city['areaid']:0;
                        $add_data['pub_date'] = $this_day;
                        $add_data['create_time'] = _time();

                        $this->dbm->insert('collect_data',$add_data);
                    }
                }

                if($city_state===false){
                    break;
                }

                $page++;
            }
        }

        $this->stop_task(2);
    }
}