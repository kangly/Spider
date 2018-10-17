<?php
/**
 * Created by PhpStorm.
 * User: kangly
 * Date: 2018/7/19
 * Time: 18:01
 */
require_once '../base.php';
use QL\QueryList;

//抓取暨阳社区,暨阳本地论坛,属于江苏省无锡市江阴市(县)
//不抓取需要登录才能查看的信息,注册账号时要求手机号和ip地址必须是在无锡
class curljysqClass extends baseClass
{
    function run()
    {
        $this->curl_jysq();
    }

    protected function curl_jysq()
    {
        $this->start_task(7);

        $base_url = 'http://bbs.jysq.net/forum.php?mod=forumdisplay&fid=202&orderby=dateline&orderby=dateline&filter=author&page=';
        $this_day = date('Y-n-d');
        $img_path = SPIDER_FILE_SAVE_URL.'/Upload/'.date('Ymd');
        $page = 1;

        while(true)
        {
            if(!$this->load_task_state(7)){
                exit;
            }

            sleep(6);

            $url = $base_url.$page;

            $list_data = QueryList::Query($url, array(
                'url' => array('tr th a:eq(1)','href'),
                'title' => array('tr th a:eq(1)','text'),
                'time' => array('tr span.xi1','text','',function($content){
                    return str_replace(' ','',substr($content,0,10));
                }),
                'contact' => array('tr .by:eq(0) a','text')
            ), '#moderate table tbody:not(.emptb)', 'UTF-8', 'GBK', true)->data;

            //采集列表源解析失败,退出 可能情况:1页面布局改变,2采集被封
            if(empty($list_data)){
                $this->stop_task(7);
                exit;
            }

            foreach($list_data as $cv)
            {
                if(!$this->load_task_state(7)){
                    exit;
                }

                //信息是按时间倒序排列的,遇到非当天的直接退出
                if($cv['time']!=$this_day){
                    $this->stop_task(7);
                    exit;
                }

                //去掉需要登录才可查看的
                if($cv['url']=='#'){
                    continue;
                }

                $is_change = $this->dbm->select('collect_data', ['id'], ['url'=>$cv['url'],'pid'=>7]);
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
                        $this->stop_task(7);
                        exit;
                    }

                    $base_data = QueryList::Query($source,array(
                        'content' => array('.t_fsz:eq(0) table tr td','text','-ignore_js_op -br -i -div -video')
                    ),'','UTF-8','GBK',true)->data;

                    $img_data = QueryList::Query($source,array(
                        'img' => array('.t_fsz:eq(0) img:lt(2)','file','',function($content) use($img_path){
                            if($content){
                                if (!file_exists($img_path)){
                                    mkdir($img_path,0777,true);
                                }
                                $local_image = $img_path.'/yjsq_'.basename($content);
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
                    $add_data['pid'] = 7;
                    $add_data['title'] = $cv['title'];
                    $add_data['contact'] = $cv['contact'];
                    $add_data['phone'] = '';
                    $add_data['content'] = $base_data[0]['content'];
                    $add_data['img_data'] = json_encode($img_data,JSON_UNESCAPED_UNICODE);
                    $add_data['province_id'] = 11;//江苏
                    $add_data['city'] = '无锡';
                    $add_data['area_id'] = 106;//无锡
                    $add_data['pub_date'] = $cv['time'];
                    $add_data['contact_address'] = '';
                    $add_data['create_time'] = _time();

                    $this->dbm->insert('collect_data',$add_data);
                }
            }

            $page++;
        }

        $this->stop_task(7);
    }
}