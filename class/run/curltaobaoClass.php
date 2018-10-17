<?php
/**
 * Created by PhpStorm.
 * User: kangly
 * Date: 2018/9/14
 * Time: 15:00
 */
require_once '../base.php';
use QL\QueryList;

//抓取阿里拍卖,有附件和图片
class curltaobaoClass extends baseClass
{
    function run()
    {
        $this->curl_taobao();
    }

    protected function curl_taobao()
    {
        $this->start_task(23);

        $this_day = date('Y-m-d');
        $this_time = strtotime($this_day);
        $file_path = SPIDER_FILE_SAVE_URL.'/Upload/'.date('Ymd');

        $url_data = [
            'https://sf.taobao.com/notice_list.htm',
            'https://zc-paimai.taobao.com/zc/noticeList.htm'
        ];

        $pub_data = $this->dbm->get('collect_data', ['pub_time'], [
            'AND' => [
                'pid' => 23
            ],
            'ORDER' => [
                'id' => 'DESC'
            ]]);
        $pub_day = $pub_data['pub_time'];

        foreach($url_data as $v)
        {
            $page_url = null;
            $is_off = true;

            while(true)
            {
                sleep(6);

                if(!$this->load_task_state(23)){
                    exit;
                }

                $curl_url = $page_url?$page_url:$v;

                $details = QueryList::run('Request',[
                    'target' => $curl_url,
                    'method' => 'GET',
                    'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.181 Safari/537.36',
                    'cookiePath' => '../cookie.txt',
                    'timeout' =>'30'
                ]);

                $source = $details->html;
                if(!$source){
                    $this->stop_task(23);
                    exit;
                }

                $list_data = QueryList::Query($source, array(
                    'url' => array('h2>a','href','',function($content){
                        return 'https:'.$content;
                    }),
                    'time' => array('.date:eq(0)','text')
                ),'.list-item','UTF-8','GBK',true)->data;

                if(empty($list_data)){
                    $this->stop_task(23);
                    exit;
                }

                //下一页的地址
                $page_data = QueryList::Query($source, array(
                    'url' => array('.next','href','',function($content){
                        return 'https:'.$content;
                    })
                ),'','UTF-8','GBK',true)->data;

                $page_url = $page_data[0]['url'];

                foreach($list_data as $m)
                {
                    sleep(6);

                    if(!$this->load_task_state(23)){
                        exit;
                    }

                    if($pub_day)
                    {
                        //处理周末或者节假日问题
                        if(strtotime($m['time'])<strtotime($pub_day)){
                            $is_off = false;
                            break;
                        }
                    }
                    else if(strtotime($m['time'])!=$this_time)
                    {
                        $is_off = false;
                        break;
                    }

                    $view_json = QueryList::Query($m['url'], array(
                        'json' => array('#sf-item-list-data','text')
                    ),'','UTF-8','GBK',true)->data;

                    //所含标的物可能为空,https://sf.taobao.com/notice_detail/1254057.htm?spm=a213w.7398557.noticeList.6.7e401aae9Fkvim
                    /*if(empty($view_json)){
                        $database->update('task_info', ['state'=>0], ['pid'=>23]);
                        exit;
                    }*/

                    $view_data = json_decode($view_json[0]['json'],true);
                    foreach($view_data['data'] as $n)
                    {
                        if(!$this->load_task_state(23)){
                            exit;
                        }

                        //真正的抓取url,前面都是为了获取该url
                        $url = 'https:'.$n['itemUrl'];
                        $is_change = $this->dbm->select('collect_data', ['id'], ['url'=>$url,'pid'=>23]);
                        if(empty($is_change))
                        {
                            sleep(6);

                            $details = QueryList::run('Request',[
                                'target' => $url,
                                'method' => 'GET',
                                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.181 Safari/537.36',
                                'cookiePath' => '../cookie.txt',
                                'timeout' =>'30'
                            ]);

                            $source = $details->html;
                            if(!$source){
                                $this->stop_task(23);
                                exit;
                            }

                            /**
                             * 处理基本信息
                             * 1.title:标题
                             * 2.content1:竞买公告url,包含竞买公告信息和竞买公告下的相关附件(如果有)
                             * 3.content2:标的物介绍url
                             * 4.content3:标的物介绍下的附件地址(json)(如果有)
                             * 5.附件下载地址
                             * https://sf.taobao.com/download_attach.do?attach_id=YIAV3KTVJD54A
                             * https://zc-paimai.taobao.com/download_attach.do?attach_id=YIAV3KTVJD54A
                             */
                            $view_array = QueryList::Query($source, array(
                                'title' => array('.pm-main h1','text','',function($content){
                                    return _str_replace($content);
                                }),
                                'address' => array('#itemAddress','text'),
                                'attr_url' => array('#J_DownLoadSecond','dowload-url','',function($content){
                                    return 'https:'.$content;
                                }),
                                'content1' => array('#J_NoticeDetail','data-from','',function($content){
                                    return 'https:'.$content;
                                }),
                                'content2' => array('#J_desc','data-from','',function($content){
                                    return 'https:'.$content;
                                }),
                                'content3' => array('#J_DownLoadFirst','data-from','',function($content){
                                    return 'https:'.$content;
                                })
                            ),'','UTF-8','GBK',true)->data;

                            if(empty($view_array)){
                                $this->stop_task(23);
                                exit;
                            }

                            $jingmaigonggao = '';
                            $biaodewujieshao = '';
                            $file_data = [];

                            //抓取详细信息
                            $attr_url = $view_array[0]['attr_url'];
                            $content1_url = $view_array[0]['content1'];
                            $content2_url = $view_array[0]['content2'];
                            $content3_url = $view_array[0]['content3'];
                            $address = $view_array[0]['address'];
                            $div_img_data = [];

                            //处理竞买公告和附件
                            if($content1_url)
                            {
                                sleep(3);

                                $content1_details = QueryList::run('Request',[
                                    'target' => $content1_url,
                                    'method' => 'GET',
                                    'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.181 Safari/537.36',
                                    'cookiePath' => '../cookie.txt',
                                    'timeout' =>'30'
                                ]);

                                $content1_source = $content1_details->html;
                                $content1_source = mb_convert_encoding($content1_source, "UTF-8", "GBK");
                                $content1_source = str_replace(['null(',');'],'',$content1_source);
                                if($content1_source)
                                {
                                    $content1_data = json_decode($content1_source,true);
                                    $jingmaigonggao = '<div style="font-family: Microsoft YaHei;font-size: 32px;color: #ad0700;text-align: center;">竞买公告</div>'.$content1_data['content'];
                                    if($content1_data['attaches']){
                                        $attr_data = $content1_data['attaches'];
                                        foreach($attr_data as $f){
                                            $file_content = $attr_url.'?attach_id='.$f['id'];
                                            if(!file_exists($file_path)){
                                                mkdir($file_path,0777,true);
                                            }
                                            $file_name = $f['title'];
                                            $local_file = $file_path.'/taobao_'.$f['id'].'_'.basename($file_name);
                                            if(!is_file($local_file)){
                                                file_put_contents($local_file,file_get_contents($file_content));
                                            }
                                            $ext = get_ext_name($file_name);
                                            $file_data[] = [
                                                'url' => str_replace(SPIDER_FILE_SAVE_URL,'',$local_file),
                                                'name' => $file_name,
                                                'ext' => $ext
                                            ];
                                        }
                                    }
                                }
                            }

                            //处理标的物介绍
                            if($content2_url)
                            {
                                sleep(3);

                                $content2_details = QueryList::run('Request',[
                                    'target' => $content2_url,
                                    'method' => 'GET',
                                    'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.181 Safari/537.36',
                                    'cookiePath' => '../cookie.txt',
                                    'timeout' =>'30'
                                ]);

                                $content2_source = $content2_details->html;
                                $content2_source = mb_convert_encoding($content2_source, "UTF-8", "GBK");
                                $content2_source = str_replace(['var desc=\'','\';'],'',$content2_source);

                                //标的物介绍中可能包含图片
                                if($content2_source)
                                {
                                    $biaodewujieshao = '<div style="font-family: Microsoft YaHei;font-size: 32px;color: #ad0700;text-align: center;">标的物介绍</div>'.preg_replace('#<div class="sf-pic-slide clearfix">.*</div>#is','',$content2_source);

                                    $div_img_data = QueryList::Query($content2_source, array(
                                        'img' => array('.sf-pic-slide>.slide-bigpic img','src','',function($content) use($file_path){
                                            if($content){
                                                $content = 'https:'.$content;
                                                if (!file_exists($file_path)){
                                                    mkdir($file_path,0777,true);
                                                }
                                                $local_image = $file_path.'/taobao_'.basename($content);
                                                if(!is_file($local_image)){
                                                    file_put_contents($local_image,file_get_contents($content));
                                                }
                                                return str_replace(SPIDER_FILE_SAVE_URL,'',$local_image);
                                            }else{
                                                return false;
                                            }
                                        })
                                    ),'','UTF-8','UTF-8')->data;
                                }
                            }

                            if($content3_url)
                            {
                                sleep(3);

                                $content3_details = QueryList::run('Request',[
                                    'target' => $content3_url,
                                    'method' => 'GET',
                                    'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.181 Safari/537.36',
                                    'cookiePath' => '../cookie.txt',
                                    'timeout' =>'30'
                                ]);

                                $content3_source = $content3_details->html;
                                $content3_source = mb_convert_encoding($content3_source, "UTF-8", "GBK");
                                $content3_source = str_replace(['null(',');'],'',$content3_source);
                                if($content3_source){
                                    $attr_data = json_decode($content3_source,true);
                                    foreach($attr_data as $f){
                                        $file_content = $attr_url.'?attach_id='.$f['id'];
                                        if(!file_exists($file_path)){
                                            mkdir($file_path,0777,true);
                                        }
                                        $file_name = $f['title'];
                                        $local_file = $file_path.'/taobao_'.$f['id'].'_'.basename($file_name);
                                        if(!is_file($local_file)){
                                            file_put_contents($local_file,file_get_contents($file_content));
                                        }
                                        $ext = get_ext_name($file_name);
                                        $file_data[] = [
                                            'url' => str_replace(SPIDER_FILE_SAVE_URL,'',$local_file),
                                            'name' => $file_name,
                                            'ext' => $ext
                                        ];
                                    }
                                }
                            }

                            //处理图片
                            $img_array = QueryList::Query($source, array(
                                'img' => array('.sf-pic-slide>.slide-bigpic img','data-ks-lazyload','',function($content) use($file_path){
                                    if($content){
                                        $content = 'https:'.$content;
                                        if (!file_exists($file_path)){
                                            mkdir($file_path,0777,true);
                                        }
                                        $local_image = $file_path.'/taobao_'.basename($content);
                                        if(!is_file($local_image)){
                                            file_put_contents($local_image,file_get_contents($content));
                                        }
                                        return str_replace(SPIDER_FILE_SAVE_URL,'',$local_image);
                                    }else{
                                        return false;
                                    }
                                })
                            ),'','UTF-8','GBK',true)->data;

                            $content = $jingmaigonggao.$biaodewujieshao;
                            $img_data = array_merge($div_img_data,$img_array);

                            $add_data = [
                                'pid' => 23,
                                'url' => $url,
                                'pub_time' => $m['time'],
                                'title' => $view_array[0]['title'],
                                'img_data' => json_encode($img_data,JSON_UNESCAPED_UNICODE),
                                'file_data' => json_encode($file_data,JSON_UNESCAPED_UNICODE),
                                'content' => htmlspecialchars_decode($content),
                                'module_id' => 27,//拍卖
                                'create_time' => _time()
                            ];

                            $address_data = explode(' ',$address);
                            if(!empty($address_data)){
                                $province = str_replace('省','',$address_data[0]);
                                $city = str_replace('市','',$address_data[1]);
                                $area_province = [];
                                $area_city = [];
                                if($province){
                                    $ap_sql = sprintf("select areaid from destoon_area where parentid=0 and areaname like '%%%s%%'",$province);
                                    $area_province = $this->dbm->query($ap_sql)->fetch();
                                }
                                if($city){
                                    $ac_sql = sprintf("select areaid from destoon_area where parentid>0 and areaname like '%%%s%%'",$city);
                                    $area_city = $this->dbm->query($ac_sql)->fetch();
                                }
                                $add_data['province_id'] = $area_province['areaid']>0?$area_province['areaid']:0;
                                $add_data['city'] = $city;
                                $add_data['area_id'] = $area_city['areaid']>0?$area_city['areaid']:0;
                            }

                            $this->dbm->insert('collect_data',$add_data);
                        }
                    }
                }

                if($is_off===false){
                    break;
                }
            }
        }

        $this->stop_task(23);
    }
}