<?php
/**
 * Created by PhpStorm.
 * User: kangly
 * Date: 2018/10/11
 * Time: 17:02
 */
require_once '../base.php';
use QL\QueryList;

//抓取京东拍卖,有图片和附件
class curljdClass extends baseClass
{
    function run()
    {
        $this->curl_jd();
    }

    protected function curl_jd()
    {
        $this->start_task(25);

        $this_day = date('Y-m-d');
        $img_path = SPIDER_FILE_SAVE_URL.'/Upload/'.date('Ymd');

        $params = [];
        $headers = [
            'Content-Type: text/json;charset=UTF-8',
            'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.81 Safari/537.36'
        ];

        $url_data = [
            'http://paimai.jd.com/json/noticeJson?publishSource=6&page=',
            'http://paimai.jd.com/json/noticeJson?publishSource=7&page=',
            'http://paimai.jd.com/json/noticeJson?publishSource=9&page='
        ];

        $pub_data = $this->dbm->get('collect_data', ['pub_date'], [
            'AND' => [
                'pid' => 25
            ],
            'ORDER' => [
                'id' => 'DESC'
            ]]);
        $pub_day = $pub_data['pub_date'];
        $pub_date = strtotime($pub_day);

        foreach($url_data as $v)
        {
            $is_off = true;

            for($i=1;$i>0;$i++)
            {
                if(!$this->load_task_state(25)){
                    exit;
                }

                sleep(6);

                $url = $v.$i;
                $json_data = http_get($url,$params,$headers);
                $json_data = str_replace(['null(','])'],['',']'],$json_data);
                $index_data = json_decode($json_data,true);

                if(empty($index_data)){
                    $this->stop_task(25);
                    exit;
                }

                foreach($index_data as $i)
                {
                    if(!$this->load_task_state(25)){
                        exit;
                    }

                    $publishTime = str_replace(['年','月','日'],['-','-',''],$i['publishTime']);
                    $time = date('Y-m-d',strtotime($publishTime));

                    if($pub_day)
                    {
                        //处理周末或者节假日问题
                        if(strtotime($time)<$pub_date){
                            $is_off = false;
                            break;
                        }
                    }
                    else if($time!=$this_day)
                    {
                        $is_off = false;
                        break;
                    }

                    sleep(6);

                    $list_data = QueryList::Query('http://paimai.jd.com/notice/'.$i['id'], array(
                        'url' => array('.p-name a','href','',function($content){
                            return 'http:'.$content;
                        })
                    ))->data;

                    if(empty($list_data)){
                        continue;
                    }

                    foreach($list_data as $l)
                    {
                        if(!$this->load_task_state(25)){
                            exit;
                        }

                        $view_url = $l['url'];
                        $is_change = $this->dbm->select('collect_data', ['id'], ['url'=>$view_url,'pid'=>25]);
                        if(empty($is_change))
                        {
                            sleep(6);

                            $view_id = str_replace('http://paimai.jd.com/','',$view_url);
                            $view_json_url = 'http://mpaimai.jd.com/json/mobile/getProductbasicInfo.html?paimaiId='.$view_id;
                            $view_json_data = http_get($view_json_url,$params,$headers);
                            $view_data = json_decode($view_json_data,true);

                            if(empty($view_data)){
                                $this->stop_task(25);
                                exit;
                            }

                            $area_province = [];
                            $province = $view_data['productAddress']['province'];
                            if($province){
                                $ap_sql = sprintf("select areaid from destoon_area where parentid=0 and areaname like '%%%s%%'",$province);
                                $area_province = $this->dbm->query($ap_sql)->fetch();
                            }

                            $area_city = [];
                            if($province=='北京' || $province=='上海' || $province=='重庆' || $province=='天津'){
                                $city = $province;
                            }else{
                                $city = str_replace('市','',$view_data['productAddress']['city']);
                            }
                            if($city){
                                $ac_sql = sprintf("select areaid from destoon_area where parentid>0 and areaname like '%%%s%%'",$city);
                                $area_city = $this->dbm->query($ac_sql)->fetch();
                            }

                            $add_data = [
                                'pid' => 25,
                                'url' => $view_url,
                                'pub_date' => $time,
                                'title' => $view_data['title'],
                                'province_id' => $area_province['areaid']>0?$area_province['areaid']:0,
                                'city' => $city,
                                'area_id' => $area_city['areaid']>0?$area_city['areaid']:0,
                                'module_id' => 27,//拍卖
                                'create_time' => _time()
                            ];

                            sleep(6);
                            $file_url = 'http://paimai.jd.com/json/paimaiProduct/queryProductFiles?productId='.$view_id;
                            $file_json_data = http_get($file_url,$params,$headers);
                            $attr_data = json_decode($file_json_data,true);
                            if($attr_data){
                                $file_data = [];
                                foreach($attr_data as $f){
                                    $file_content = $f['attachmentAddress'];
                                    if(!file_exists($img_path)){
                                        mkdir($img_path,0777,true);
                                    }
                                    $local_file = $img_path.'/jd_'.pathinfo($file_content,PATHINFO_BASENAME);
                                    if(!is_file($local_file)){
                                        file_put_contents($local_file,file_get_contents($file_content));
                                    }
                                    $file_data[] = [
                                        'url' => str_replace(SPIDER_FILE_SAVE_URL,'',$local_file),
                                        'name' => $f['attachmentName'],
                                        'ext' => $f['attachmentFormat']
                                    ];
                                }
                                $add_data['file_data'] = json_encode($file_data,JSON_UNESCAPED_UNICODE);
                            }

                            $content = '';

                            if($view_data['albumId'])
                            {
                                sleep(6);
                                $paimaigonggao_url = 'http://paimai.jd.com/json/current/queryAlbumAnnouncement?albumId='.$view_data['albumId'];
                                $paimaigonggao_json_data = http_get($paimaigonggao_url,$params,$headers);
                                $paimaigonggao_data = json_decode($paimaigonggao_json_data,true);
                                $content .= $paimaigonggao_data['content'];
                            }

                            if($view_data['skuId'])
                            {
                                sleep(6);

                                $biaodewujieshao_url = 'http://paimai.jd.com/json/paimaiProduct/productDesciption?productId='.$view_data['skuId'];
                                $content_details = QueryList::run('Request',[
                                    'target' => $biaodewujieshao_url,
                                    'method' => 'GET',
                                    'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.181 Safari/537.36',
                                    'cookiePath' => '../cookie.txt',
                                    'timeout' =>'30'
                                ]);

                                $content_source = $content_details->html;
                                if($content_source)
                                {
                                    $content_source = trim($content_source,'"');
                                    $img_data = QueryList::Query($content_source, array(
                                        'img' => array('img','src','',function($content) use($img_path){
                                            if($content){
                                                $content = 'https:'.$content;
                                                if (!file_exists($img_path)){
                                                    mkdir($img_path,0777,true);
                                                }
                                                $local_image = $img_path.'/jd_'.basename($content);
                                                if(!is_file($local_image)){
                                                    file_put_contents($local_image,file_get_contents($content));
                                                }
                                                return str_replace(SPIDER_FILE_SAVE_URL,'',$local_image);
                                            }else{
                                                return false;
                                            }
                                        })
                                    ))->data;

                                    $add_data['img_data'] = json_encode($img_data,JSON_UNESCAPED_UNICODE);

                                    //过滤img标签
                                    $content .= str_replace('\n','',preg_replace('/<\s*img\s+[^>]*?src\s*=\s*(\'|\")(.*?)\\1[^>]*?\/?\s*>/i','',$content_source));
                                }
                            }

                            if($content){
                                $add_data['content'] = htmlspecialchars_decode($content);
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

        $this->stop_task(25);
    }
}