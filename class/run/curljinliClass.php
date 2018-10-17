<?php
/**
 * Created by PhpStorm.
 * User: kangly
 * Date: 2018/7/26
 * Time: 18:26
 */
require_once '../base.php';
use QL\QueryList;

//抓取金利网
class curljinliClass extends baseClass
{
    function run()
    {
        $this->curl_jinli();
    }

    protected function curl_jinli()
    {
        $this->start_task(17);

        $url_data = [
            'http://www.0758vip.com/forum.php?mod=forumdisplay&fid=46&filter=author&orderby=dateline&typeid=54',
            'http://www.0758vip.com/forum.php?mod=forumdisplay&fid=46&filter=author&orderby=dateline&typeid=82',
            'http://www.0758vip.com/forum.php?mod=forumdisplay&fid=46&filter=author&orderby=dateline&typeid=19',
            'http://www.0758vip.com/forum.php?mod=forumdisplay&fid=46&filter=author&orderby=dateline&typeid=86',
            'http://www.0758vip.com/forum.php?mod=forumdisplay&fid=46&filter=author&orderby=dateline&typeid=77'
        ];

        $this_day = date('Y-n-j');
        $img_path = SPIDER_FILE_SAVE_URL.'/Upload/'.date('Ymd');

        sleep(6);

        $login = QueryList::run('Login',[
            'target' => 'http://www.0758vip.com/member.php?mod=logging&action=login&loginsubmit=yes&infloat=yes&lssubmit=yes&inajax=1',
            'method' => 'post',
            'params' => ['fastloginfield'=>'username','username'=>'shebeishang168','password'=>'a123456.','quickforward'=>'yes','handlekey'=>'ls'],
            'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.181 Safari/537.36',
            'cookiePath' => '../cookie.txt'
        ]);

        foreach($url_data as $v)
        {
            if(!$this->load_task_state(17)){
                exit;
            }

            sleep(6);

            $list_data = QueryList::Query($v, array(
                'title' => array('tr th .xst','text'),
                'url' => array('tr th .xst','href','',function($content){
                    return 'http://www.0758vip.com/'.$content;
                }),
                'contact' => array('tr .by:eq(0) cite a','text'),
                'time1' => array('tr .by:eq(0) em span:last','title'),
                'time2' => array('tr .by:eq(0) em span:last','text')
            ), '#threadlisttableid tbody:not(.emptb)', 'UTF-8', 'GBK', true)->data;

            //采集列表源解析失败,退出 可能情况:1页面布局改变,2采集被封
            if(empty($list_data)){
                $this->stop_task(17);
                exit;
            }

            foreach($list_data as $cv)
            {
                if(!$this->load_task_state(17)){
                    exit;
                }

                $cv['time'] = $cv['time1']?$cv['time1']:$cv['time2'];
                if($cv['time']!=$this_day){
                    break;
                }

                $is_change = $this->dbm->select('collect_data', ['id'], ['url'=>$cv['url'],'pid'=>17]);
                if(empty($is_change))
                {
                    sleep(6);

                    $res = $login->get($cv['url']);
                    $source = $res->html;
                    if(!$source){
                        $this->stop_task(17);
                        exit;
                    }

                    $base_data = QueryList::Query($source,array(
                        'content' => array('.t_f:eq(0)','text','-ignore_js_op -br'),
                    ),'','UTF-8','GBK',true)->data;

                    if(empty($base_data)){
                        $this->stop_task(17);
                        exit;
                    }

                    $img_source = QueryList::Query($source,array(
                        'title' => array('.t_fsz:eq(0) .savephotop img:lt(2)','title'),
                        'img' => array('.t_fsz:eq(0) .savephotop img:lt(2)','file')
                    ),'','UTF-8','GBK',true)->data;

                    $img_data = [];
                    foreach($img_source as $iv){
                        if($iv['title'] && $iv['img']){
                            $content = 'http://www.0758vip.com/'.$iv['img'];
                            if (!file_exists($img_path)){
                                mkdir($img_path,0777,true);
                            }
                            $local_image = $img_path.'/jinli_'.$iv['title'];
                            if(!is_file($local_image)){
                                $img_res = $login->get($content);
                                file_put_contents($local_image,$img_res->html);
                            }
                            $img_data[]['img'] = str_replace(SPIDER_FILE_SAVE_URL,'',$local_image);
                        }
                    }

                    $add_data = [
                        'url' => $cv['url']
                    ];
                    $add_data['pid'] = 17;
                    $add_data['title'] = $cv['title'];
                    $add_data['contact'] = $cv['contact'];
                    $add_data['phone'] = '';
                    $add_data['content'] = $base_data[0]['content'];
                    $add_data['img_data'] = json_encode($img_data,JSON_UNESCAPED_UNICODE);
                    $add_data['province_id'] = 20;//广东
                    $add_data['city'] = '肇庆';
                    $add_data['area_id'] = 240;//肇庆
                    $add_data['pub_date'] = $cv['time'];
                    $add_data['contact_address'] = '';
                    $add_data['create_time'] = _time();

                    $this->dbm->insert('collect_data',$add_data);
                }
            }
        }

        $this->stop_task(17);
    }
}