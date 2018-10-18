<?php
/**
 * Created by PhpStorm.
 * User: kangly
 * Date: 2018/10/12
 * Time: 17:00
 */
require_once '../base.php';
use QL\QueryList;

//抓取e交易,有图片和附件
class curlejyClass extends baseClass
{
    function run()
    {
        $this->curl_ejy();
    }

    protected function curl_ejy()
    {
        $this->start_task(26);

        $this_day = date('Y-m-d');
        $this_date = strtotime($this_day);
        $img_path = SPIDER_FILE_SAVE_URL.'/Upload/'.date('Ymd');

        $url_data = [
            'http://www.ejy365.com/jygg_more?categoryname=%E4%BA%8C%E6%89%8B%E8%AE%BE%E5%A4%87&s2=%25&s1=%25&s0=%25&orderType=orderType3_down&conditionopen=1&page=',
            'http://www.ejy365.com/jygg_more?categoryname=%E4%BA%8C%E6%89%8B%E8%BD%A6&s2=%25&s1=%25&s0=%25&orderType=orderType3_down&conditionopen=1&page=',
            'http://www.ejy365.com/jygg_more?categoryname=%E8%88%B9%E8%88%B6&s2=%25&s1=%25&s0=%25&orderType=orderType3_down&conditionopen=1&page=',
            'http://www.ejy365.com/jygg_more?categoryname=%E9%97%B2%E7%BD%AE%E8%B5%84%E4%BA%A7&s2=%25&s1=%25&s0=%25&orderType=orderType3_down&conditionopen=1&page='
        ];

        $pub_data = $this->dbm->get('collect_data', ['pub_date'], [
            'AND' => [
                'pid' => 26
            ],
            'ORDER' => [
                'id' => 'DESC'
            ]]);
        $pub_day = $pub_data['pub_date'];
        $pub_date = strtotime($pub_day);

        foreach($url_data as $v)
        {
            for($i=1;$i<3;$i++)
            {
                if(!$this->load_task_state(26)){
                    exit;
                }

                sleep(6);

                $url = $v.$i;
                $list_data = QueryList::Query($url, array(
                    'url' => array('.title a','onclick'),
                    'title' => array('.title a','text','-b'),
                    'city' => array('.title a b','text','',function($content){
                        return str_replace(['市','[',']'],'',$content);
                    }),
                    'time' => array('.item:eq(1)','text','',function($content){
                        $content = str_replace('报名截止时间：','',$content);
                        return strtotime(date('Y-m-d',strtotime($content)));
                    }),
                ),'.product-list:eq(0) ul li')->data;

                if(empty($list_data)){
                    $this->stop_task(26);
                    exit;
                }

                foreach($list_data as $l)
                {
                    if(!$this->load_task_state(26)){
                        exit;
                    }

                    if($pub_day)
                    {
                        //处理周末或者节假日问题
                        if($l['time']<$pub_date){
                            continue;
                        }
                    }
                    else if($l['time']<$this_date)
                    {
                        continue;
                    }

                    $url_data = explode(',',$l['url']);
                    $url_id = str_replace("'",'',$url_data[1]);
                    $view_url = 'http://www.ejy365.com/infodetail?infoid='.$url_id;

                    $is_change = $this->dbm->select('collect_data', ['id'], ['url'=>$view_url,'pid'=>26]);
                    if(empty($is_change))
                    {
                        sleep(6);

                        $details = QueryList::run('Request',[
                            'target' => $view_url,
                            'method' => 'GET',
                            'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.181 Safari/537.36',
                            'cookiePath' => '../cookie.txt',
                            'timeout' =>'30'
                        ]);

                        $source = $details->html;
                        if(!$source){
                            $this->stop_task(26);
                            exit;
                        }

                        $view_data = QueryList::Query($source, array(
                            'bdgk' => array('#d-bdgk','html'),
                            'tbgz' => array('#d-tbgz','html'),
                            'jytj' => array('#d-jytj','html'),
                            'lxwm' => array('#d-lxwm','html')
                        ))->data;

                        $content = $view_data[0]['bdgk'].$view_data[0]['tbgz'].$view_data[0]['jytj'].$view_data[0]['lxwm'];

                        $img_data = QueryList::Query($source, array(
                            'img' => array('#cSlideUl ul img','data-original','',function($content) use($img_path){
                                if($content){
                                    $content = 'http://www.ejy365.com'.$content;
                                    if (!file_exists($img_path)){
                                        mkdir($img_path,0777,true);
                                    }
                                    $local_image = $img_path.'/ejy_'.basename($content);
                                    if(!is_file($local_image)){
                                        file_put_contents($local_image,file_get_contents($content));
                                    }
                                    return str_replace(SPIDER_FILE_SAVE_URL,'',$local_image);
                                }else{
                                    return false;
                                }
                            })
                        ))->data;

                        $file_array = QueryList::Query($source, array(
                            'url' => array('#d-jyzn a','href'),
                            'name' => array('#d-jyzn a','text')
                        ))->data;

                        $file_data = [];
                        foreach($file_array as $f){
                            if(strpos($f['url'],'ejyzx/uploadfile') !== false){
                                if(!file_exists($img_path)){
                                    mkdir($img_path,0777,true);
                                }
                                $pathInfo = pathinfo($f['url']);
                                $local_file = $img_path.'/ejy_'.$pathInfo['basename'];
                                if(!is_file($local_file)){
                                    file_put_contents($local_file,file_get_contents($f['url']));
                                }
                                $file_data[] = [
                                    'url' => str_replace(SPIDER_FILE_SAVE_URL,'',$local_file),
                                    'name' => $f['name'],
                                    'ext' => $pathInfo['extension']
                                ];
                            }
                        }

                        $area_city = [];
                        $area_province = '';
                        if($l['city']){
                            $ac_sql = sprintf("select areaid,arrparentid from destoon_area where parentid>0 and areaname like '%%%s%%'",$l['city']);
                            $area_city = $this->dbm->query($ac_sql)->fetch();
                            $area_province = str_replace(['0',','],'',$area_city['arrparentid']);
                        }

                        $add_data = [
                            'pid' => 26,
                            'url' => $view_url,
                            'pub_date' => $this_day,
                            'title' => $l['title'],
                            'img_data' => json_encode($img_data,JSON_UNESCAPED_UNICODE),
                            'file_data' => json_encode($file_data,JSON_UNESCAPED_UNICODE),
                            'content' => htmlspecialchars_decode($content),
                            'city' => $l['city'],
                            'area_id' => $area_city['areaid']>0?$area_city['areaid']:0,
                            'province_id' => $area_province?$area_province:0,
                            'module_id' => 27,//拍卖
                            'create_time' => _time()
                        ];

                        $this->dbm->insert('collect_data',$add_data);
                    }
                }
            }
        }

        $this->stop_task(26);
    }
}