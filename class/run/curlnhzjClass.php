<?php
/**
 * Created by PhpStorm.
 * User: kangly
 * Date: 2018/7/20
 * Time: 21:51
 */
require_once '../base.php';
use QL\QueryList;

//抓取宁海在线,按时间倒序排序,内容过少,只抓取一页
class curlnhzjClass extends baseClass
{
    function run()
    {
        $this->curl_nhzj();
    }

    protected function curl_nhzj()
    {
        $this->start_task(8);

        $url_data = [
            'http://bbs.nhzj.com/forum.php?mod=forumdisplay&fid=8&orderby=dateline&typeid=16&orderby=dateline&typeid=16&filter=author&page=1',
            'http://bbs.nhzj.com/forum.php?mod=forumdisplay&fid=8&orderby=dateline&typeid=17&orderby=dateline&typeid=17&filter=author&page=1',
            'http://bbs.nhzj.com/forum.php?mod=forumdisplay&fid=8&orderby=dateline&typeid=18&orderby=dateline&typeid=18&filter=author&page=1'
        ];

        $this_day = date('Y-n-j');
        $img_path = SPIDER_FILE_SAVE_URL.'/Upload/'.date('Ymd');

        foreach($url_data as $v)
        {
            if(!$this->load_task_state(8)){
                exit;
            }

            sleep(6);

            $list_data = QueryList::Query($v, array(
                'url' => array('.s.xst','href'),
                'title' => array('.s.xst','text'),
                'time1' => array('.by:eq(0) em span:last','title'),
                'time2' => array('.by:eq(0) em span:last','text'),
                'contact' => array('.by:eq(0) cite a','text'),
            ),'table#threadlisttableid tbody[id^="normalthread_"]','UTF-8','GBK',true)->data;

            //采集列表源解析失败,退出 可能情况:1页面布局改变,2采集被封
            if(empty($list_data)){
                $this->stop_task(8);
                exit;
            }

            foreach($list_data as $cv)
            {
                if(!$this->load_task_state(8)){
                    exit;
                }

                $cv['time'] = $cv['time1']?$cv['time1']:$cv['time2'];

                //此网站数据是按时间倒序排序的
                //所以该列表遇到时间非当天的退出此循环,进行下一循环
                if($cv['time'] != $this_day){
                    break;
                }

                $is_change = $this->dbm->select('collect_data', ['id'], ['url'=>$cv['url'],'pid'=>8]);
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
                        $this->stop_task(8);
                        exit;
                    }

                    $base_data = QueryList::Query($source,array(
                        'content' => array('.t_fsz:eq(0) table tr td','text','-ignore_js_op -br -i -div -video')
                    ),'','UTF-8','GBK',true)->data;

                    $img_data = QueryList::Query($source,array(
                        'img' => array('.t_fsz:eq(0) ignore_js_op:lt(2) img','file','',function($content) use($img_path){
                            if($content){
                                if (!file_exists($img_path)){
                                    mkdir($img_path,0777,true);
                                }
                                $local_image = $img_path.'/nhzj_'.basename($content);
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
                    $add_data['pid'] = 8;
                    $add_data['title'] = $cv['title'];
                    $add_data['contact'] = $cv['contact'];
                    $add_data['phone'] = '';
                    $add_data['content'] = $base_data[0]['content'];
                    $add_data['img_data'] = json_encode($img_data,JSON_UNESCAPED_UNICODE);
                    $add_data['province_id'] = 12;//浙江
                    $add_data['city'] = '宁波';
                    $add_data['area_id'] = 119;//宁波
                    $add_data['pub_date'] = $cv['time'];
                    $add_data['contact_address'] = '';
                    $add_data['create_time'] = _time();

                    $this->dbm->insert('collect_data',$add_data);
                }
            }
        }

        $this->stop_task(8);
    }
}