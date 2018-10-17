<?php
/**
 * Created by PhpStorm.
 * User: kangly
 * Date: 2018/7/19
 * Time: 15:28
 */
require_once '../base.php';
use QL\QueryList;

//抓取梦溪论坛
class curlmxltClass extends baseClass
{
    function run()
    {
        $this->curl_mxlt();
    }

    protected function curl_mxlt()
    {
        $this->start_task(6);

        $url_data = [
            'http://bbs.my0511.com/f840b-y871p',
            'http://bbs.my0511.com/f840b-y769p'
        ];

        $this_day = date('Y-n-j');
        $img_path = SPIDER_FILE_SAVE_URL.'/Upload/'.date('Ymd');

        foreach($url_data as $v)
        {
            if(!$this->load_task_state(6)){
                exit;
            }

            sleep(10);

            $list_data = QueryList::Query($v, array(
                'url' => array('.list_dl:gt(0) .a_topic','href','',function($content){
                    return 'http://bbs.my0511.com'.$content;
                }),
                'title' => array('.list_dl:gt(0) .a_topic','text'),
                'contact' => array('.list_dl:gt(0) .linkblack:eq(0)','text'),
                'time' => array('.list_dl:gt(0) .tdate','text'),
            ),'','UTF-8','GBK',true)->data;

            //采集列表源解析失败,退出 可能情况:1页面布局改变,2采集被封
            if(empty($list_data)){
                $this->stop_task(6);
                exit;
            }

            foreach($list_data as $cv)
            {
                if(!$this->load_task_state(6)){
                    exit;
                }

                //此网站数据是按时间倒序排序的
                //所以该列表遇到时间非当天的退出此循环,进行下一循环
                //先跳过本次循环,可能有置顶等信息
                if($cv['time'] != $this_day){
                    continue;
                }

                sleep(10);//暂停10秒

                $is_change = $this->dbm->select('collect_data', ['id'], ['url'=>$cv['url'],'pid'=>6]);
                if(empty($is_change))
                {
                    $details = QueryList::run('Request',[
                        'target' => $cv['url'],
                        'method' => 'GET',
                        'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.181 Safari/537.36',
                        'cookiePath' => '../cookie.txt',
                        'timeout' =>'30'
                    ]);

                    $source = $details->html;
                    if(!$source){
                        $this->stop_task(6);
                        exit;
                    }

                    $base_data = QueryList::Query($source,array(
                        'content' => array('.postcon:eq(0)','text','-a -br -span -#dashang_btn -img -i',function($content){
                            return _str_replace($content);
                        })
                    ),'','UTF-8','GBK',true)->data;

                    //采集列表源解析失败,退出 可能情况:1页面布局改变,2采集被封
                    if(empty($base_data)){
                        $this->stop_task(6);
                        exit;
                    }

                    $img_data = QueryList::Query($source,array(
                        'img' => array('.postcon:eq(0) img:lt(2)','data-src','',function($content) use($img_path){
                            if($content){
                                $content = 'http:'.$content;
                                if (!file_exists($img_path)){
                                    mkdir($img_path,0777,true);
                                }
                                $local_image = $img_path.'/mxlt_'.basename($content);
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
                    $add_data['pid'] = 6;
                    $add_data['title'] = $cv['title'];
                    $add_data['contact'] = $cv['contact'];
                    $add_data['phone'] = '';
                    $add_data['content'] = $base_data[0]['content'];
                    $add_data['img_data'] = json_encode($img_data,JSON_UNESCAPED_UNICODE);
                    $add_data['province_id'] = 0;
                    $add_data['city'] = '';
                    $add_data['area_id'] = 0;
                    $add_data['pub_date'] = $cv['time'];
                    $add_data['contact_address'] = '';
                    $add_data['create_time'] = _time();

                    $this->dbm->insert('collect_data',$add_data);
                }
            }
        }

        $this->stop_task(6);
    }
}