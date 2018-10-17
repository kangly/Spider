<?php
/**
 * Created by PhpStorm.
 * User: kangly
 * Date: 2018/7/19
 * Time: 09:51
 */
require_once '../base.php';
use QL\QueryList;

//采集胜芳大杂烩,内容很少,默认只采集一页
//此站点是河北省廊坊市霸州市(县级)的网站,默认省市为河北省廊坊市
class curlsfdzhClass extends baseClass
{
    function run()
    {
        $this->curl_sfdzh();
    }

    protected function curl_sfdzh()
    {
        $this->start_task(4);

        $url_data = [
            'http://www.sfdzh.com/forum.php?mod=forumdisplay&fid=43&filter=sortid&sortid=4&searchsort=1&wupin=9&page=1',
            'http://www.sfdzh.com/forum.php?mod=forumdisplay&fid=43&sortid=4&filter=sortid&searchsort=1&wupin=13&page=1',
            'http://www.sfdzh.com/forum.php?mod=forumdisplay&fid=43&sortid=4&filter=sortid&searchsort=1&wupin=22&page=1'
        ];

        $this_day = date('Y-n-j');
        $img_path = SPIDER_FILE_SAVE_URL.'/Upload/'.date('Ymd');

        sleep(10);

        $login = QueryList::run('Login',[
            'target' => 'http://www.sfdzh.com/member.php?mod=logging&action=login&loginsubmit=yes&lssubmit=yes',
            'method' => 'post',
            'params' => ['username'=>'xianrenqiu','password'=>'a123456','lostpwsubmit'=>true],
            'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.181 Safari/537.36',
            'cookiePath' => '../cookie.txt'
        ]);

        foreach($url_data as $v)
        {
            if(!$this->load_task_state(4)){
                exit;
            }

            sleep(10);

            $list_data = QueryList::Query($v, array(
                'url' => array('tr th a:last','href'),
                'time1' => array('tr .by:eq(0) em span:last','title'),
                'time2' => array('tr .by:eq(0) em span:last','text')
            ), '#threadlisttableid tbody:not(.emptb)', 'UTF-8', 'GBK', true)->data;

            //采集列表源解析失败,退出 可能情况:1页面布局改变,2采集被封
            if(empty($list_data)){
                $this->stop_task(4);
                exit;
            }

            foreach($list_data as $cv)
            {
                if(!$this->load_task_state(4)){
                    exit;
                }

                $cv['time'] = $cv['time1']?$cv['time1']:$cv['time2'];

                //此网站数据是按时间倒序排序的
                //所以该列表遇到时间非当天的退出此循环,进行下一循环
                if($cv['time'] != $this_day){
                    break;
                }

                sleep(10);//暂停10秒

                $is_change = $this->dbm->select('collect_data', ['id'], ['url'=>$cv['url'],'pid'=>4]);
                if(empty($is_change))
                {
                    $res = $login->get($cv['url']);
                    $source = $res->html;

                    //采集列表源,退出
                    if(!$source){
                        $this->stop_task(4);
                        exit;
                    }

                    $base_data = QueryList::Query($source,array(
                        'title' => array('#thread_subject','text'),
                        'content' => array('.t_f:eq(0)','text','-ignore_js_op -br'),
                    ),'','UTF-8','GBK',true)->data;

                    //采集列表源解析失败,退出 可能情况:1页面布局改变,2采集被封
                    if(empty($base_data)){
                        $this->stop_task(4);
                        exit;
                    }

                    //联系信息
                    $contact_data = QueryList::Query($source,array(
                        'field' => array('.typeoption tr','text','',function($content){
                            return _str_replace($content);
                        })
                    ))->data;

                    $phone = '';
                    $contact = '';
                    $address = '';
                    foreach($contact_data as $av)
                    {
                        if($av['field'])
                        {
                            if(strpos($av['field'],'所在区域:') !== false){
                                $address = str_replace('所在区域:','',$av['field']);
                            }

                            if(strpos($av['field'],'联系人:') !== false){
                                $contact = str_replace('联系人:','',$av['field']);
                            }

                            if(strpos($av['field'],'联系电话:') !== false){
                                $phone = str_replace('联系电话:','',$av['field']);
                            }
                        }
                    }

                    //获取图片,如果存在图片仅抓取前三张
                    $img_data = QueryList::Query($source,array(
                        'img' => array('.t_f:eq(0) img:lt(2)','file','',function($content) use($img_path){
                            if($content){
                                $content = 'http://www.sfdzh.com/'.$content;
                                $image = substr($content,0);
                                if (!file_exists($img_path)){
                                    mkdir($img_path,0777,true);
                                }
                                $local_image = $img_path.'/sfdzh_'.basename($image);
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
                    $add_data['pid'] = 4;
                    $add_data['title'] = $base_data[0]['title'];
                    $add_data['contact'] = $contact;
                    $add_data['phone'] = $phone;
                    $add_data['content'] = $base_data[0]['content'];
                    $add_data['img_data'] = json_encode($img_data,JSON_UNESCAPED_UNICODE);
                    $add_data['province_id'] = 5;//河北
                    $add_data['city'] = '廊坊';
                    $add_data['area_id'] = 44;//廊坊
                    $add_data['pub_date'] = $cv['time'];
                    $add_data['contact_address'] = $address;
                    $add_data['create_time'] = _time();

                    $this->dbm->insert('collect_data',$add_data);
                }
            }
        }

        $this->stop_task(4);
    }
}