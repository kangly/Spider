<?php

/**
 * Created by PhpStorm.
 * User: kangly
 * Date: 2018/10/15
 * Time: 13:04
 */
require_once '../base.php';
use QL\QueryList;

//抓取北京产权交易(http://www.jinmajia.com/cbex_zyt/xzqsy/),有图片和附件
class curljinmajiaClass extends baseClass
{
    function run()
    {
        $this->curl_jinmajia();
    }

    protected function curl_jinmajia()
    {
        $this->start_task(27);

        $this_day = date('Y-m-d');
        $img_path = SPIDER_FILE_SAVE_URL.'/Upload/'.date('Ymd');

        sleep(6);

        $list_data = QueryList::Query('http://info.jinmajia.com/list/list_txt.jsp?seids=134026748410620865', array(
            'id' => array('td:eq(0) font','id','',function($content){
                return str_replace('iname_','',$content);
            }),
            'title' => array('td:eq(0) font','text')
        ),'tr:class(cg)')->data;

        unset($list_data[0]);

        if(empty($list_data)){
            $this->stop_task(27);
            exit;
        }

        foreach($list_data as $v)
        {
            if(!$this->load_task_state(27)){
                exit;
            }

            $view_url = 'http://baojia.jinmajia.com/home.jsp?itemid='.$v['id'];
            $is_change = $this->dbm->select('collect_data', ['id'], ['url'=>$view_url,'pid'=>27]);
            if(empty($is_change))
            {
                sleep(6);

                $data_list = QueryList::Query('http://baojia.jinmajia.com/ab_item_view.jsp?baojia=false&itemid='.$v['id'], array(
                    'url' => array('iframe','src')
                ))->data;

                if(empty($data_list)){
                    $this->stop_task(27);
                    exit;
                }

                sleep(6);

                $view_data = QueryList::Query($data_list[0]['url'], array(
                    'id' => array('#projectid','value'),
                    'content' => array('body','html','-script -style -div')
                ))->data;

                if(empty($view_data)){
                    $this->stop_task(27);
                    exit;
                }

                $add_data = [
                    'pid' => 27,
                    'url' => $view_url,
                    'pub_time' => $this_day,
                    'title' => $v['title'],
                    'content' => htmlspecialchars_decode($view_data[0]['content']),
                    'module_id' => 27,//拍卖
                    'create_time' => _time()
                ];

                $post_url = 'http://xzzc3.cbex.com.cn/eads/attachment/ws/attachment!jsonAttachList.action';

                sleep(6);
                $img_json_data = http_post($post_url,['objId'=>$view_data[0]['id'],'objType'=>'fujiantupian']);
                $img_array = json_decode($img_json_data,true);
                if($img_array){
                    $img_data = [];
                    foreach($img_array['rows'] as $f){
                        $content = $f['attaUrl'];
                        if (!file_exists($img_path)){
                            mkdir($img_path,0777,true);
                        }
                        $local_image = $img_path.'/jinmajia_'.$f['attaId'].'.'.$f['attaType'];
                        if(!is_file($local_image)){
                            file_put_contents($local_image,file_get_contents($content));
                        }
                        $img_data[]['img'] = str_replace(SPIDER_FILE_SAVE_URL,'',$local_image);
                    }
                    $add_data['img_data'] = json_encode($img_data,JSON_UNESCAPED_UNICODE);
                }

                sleep(6);
                $file_json_data = http_post($post_url,['objId'=>$view_data[0]['id'],'objType'=>'fujianwy']);
                $file_array = json_decode($file_json_data,true);
                if($file_array){
                    $file_data = [];
                    foreach($file_array['rows'] as $f){
                        $file_content = $f['attaUrl'];
                        if(!file_exists($img_path)){
                            mkdir($img_path,0777,true);
                        }
                        $local_file = $img_path.'/jinmajia_'.$f['attaId'].'.'.$f['attaType'];
                        if(!is_file($local_file)){
                            file_put_contents($local_file,file_get_contents($file_content));
                        }
                        $file_data[] = [
                            'url' => str_replace(SPIDER_FILE_SAVE_URL,'',$local_file),
                            'name' => $f['attaName'].'.'.$f['attaType'],
                            'ext' => $f['attaType']
                        ];
                    }
                    $add_data['file_data'] = json_encode($file_data,JSON_UNESCAPED_UNICODE);
                }

                $this->dbm->insert('collect_data',$add_data);
            }
        }

        $this->stop_task(27);
    }
}