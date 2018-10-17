<?php
/**
 * Created by PhpStorm.
 * User: kangly
 * Date: 2018/7/29
 * Time: 21:04
 */
require_once '../base.php';
use QL\QueryList;

//抓取慈溪网
class curlzxipClass extends baseClass
{
    function run()
    {
        $this->curl_zxip();
    }

    protected function curl_zxip()
    {
        $this->start_task(21);

        $url_data = [
            'http://fenlei.zxip.com/Home/List/1_110_0_0_1_0',
            'http://fenlei.zxip.com/Home/List/1_19_0_0_1_0',
            'http://fenlei.zxip.com/Home/List/1_116_0_0_1_0',
        ];

        $this_day = date('Y-m-d');
        //$img_path = SPIDER_FILE_SAVE_URL.'/Upload/'.date('Ymd');

        foreach($url_data as $v)
        {
            if(!$this->load_task_state(21)){
                exit;
            }

            sleep(6);

            $list_data = QueryList::Query($v, array(
                'url' => array('.mlist ul li a', 'href','',function($content){
                    return 'http://fenlei.zxip.com'.$content;
                }),
                'title' => array('.mlist ul li a', 'text')
            ))->data;

            if(empty($list_data)){
                $this->stop_task(21);
                exit;
            }

            foreach($list_data as $cv)
            {
                if(!$this->load_task_state(21)){
                    exit;
                }

                $view_url = $cv['url'];
                $is_change = $this->dbm->select('collect_data',['id'],['url'=>$view_url,'pid'=>21]);
                if(empty($is_change))
                {
                    sleep(6);

                    $base_data = QueryList::Query($view_url,array(
                        'time' => array('.addtime p','text','-span',function($content){
                            $content = _str_replace($content);
                            $content = str_replace('更新时间：','',$content);
                            return substr($content,0,10);
                        }),
                        'contact' => array('.brief table tr:eq(2) td:eq(1)','text'),
                        'phone' => array('.telred','text'),
                        'content' => array('.detail','text','-.detail_top',function($content){
                            return _str_replace($content);
                        }),
                    ))->data;

                    if($base_data[0]['time'] != $this_day){
                        break;
                    }

                    //没发现有图片的信息,先不抓取
                    $img_data = [];

                    $add_data = [
                        'url' => $view_url
                    ];
                    $add_data['pid'] = 21;
                    $add_data['title'] = $cv['title'];
                    $add_data['contact'] = $base_data[0]['contact'];
                    $add_data['phone'] = $base_data[0]['phone'];
                    $add_data['content'] = $base_data[0]['content'];
                    $add_data['img_data'] = json_encode($img_data,JSON_UNESCAPED_UNICODE);
                    $add_data['province_id'] = 12;//浙江
                    $add_data['city'] = '宁波';
                    $add_data['area_id'] = 119;//宁波
                    $add_data['pub_date'] = $base_data[0]['time'];
                    $add_data['contact_address'] = '';
                    $add_data['create_time'] = _time();

                    $this->dbm->insert('collect_data',$add_data);
                }
            }
        }

        $this->stop_task(21);
    }
}