<?php
/**
 * Created by PhpStorm.
 * User: kangly
 * Date: 2018/10/16
 * Time: 16:37
 */
require_once '../base.php';

//抓取中国拍卖行业协会
class curlsfClass extends baseClass
{
    function run()
    {
        $this->curl_sf();
    }

    protected function curl_sf()
    {
        $this->start_task(30);

        $this_day = date('Y-m-d');
        $file_path = SPIDER_FILE_SAVE_URL.'/Upload/'.date('Ymd');
        $params = [];
        $headers = [
            'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
            'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.81 Safari/537.36'
        ];

        $pub_data = $this->dbm->get('collect_data', ['pub_time'], [
            'AND' => [
                'pid' => 30
            ],
            'ORDER' => [
                'id' => 'DESC'
            ]]);
        $pub_day = $pub_data['pub_time'];

        for($i=0;$i>=0;$i++)
        {
            if(!$this->load_task_state(30)){
                exit;
            }

            sleep(6);

            $url = 'https://sf.caa123.org.cn/caa-web-ws/ws/0.1/notices?sortname=&sortorder=&start='.$i.'&count=10';
            $res = http_get($url,$params,$headers);
            $res = json_decode($res,true);
            $html = $res['items'];

            if(empty($html)){
                $this->stop_task(30);
                exit;
            }

            foreach($html as $v)
            {
                if(!$this->load_task_state(30)){
                    exit;
                }

                //处理周末或者节假日问题
                $publishDate = date('Y-m-d',$v['publishTime']/1000);
                if($pub_day)
                {
                    if(strtotime($publishDate)<strtotime($pub_day)){
                        $this->stop_task(30);
                        exit;
                    }
                }
                else if($publishDate!=$this_day)
                {
                    $this->stop_task(30);
                    exit;
                }

                sleep(6);
                $list_url = 'https://sf.caa123.org.cn/caa-web-ws/ws/0.1/notice/'.$v['id'];
                $res = http_get($list_url,$params,$headers);
                $res = json_decode($res,true);
                $list_data = $res['lotList'];

                if(empty($list_data)){
                    $this->stop_task(30);
                    exit;
                }

                foreach($list_data as $l)
                {
                    if(!$this->load_task_state(30)){
                        exit;
                    }

                    $real_url = 'https://sf.caa123.org.cn/lot/'.$l['id'].'.html';
                    $is_change = $this->dbm->select('collect_data', ['id'], ['url'=>$real_url,'pid'=>30]);
                    if(empty($is_change))
                    {
                        sleep(6);
                        $view_url = 'https://sf.caa123.org.cn/caa-web-ws/ws/0.1/goods/lot/'.$l['id'];
                        $res = http_get($view_url,$params,$headers);
                        $view_data = json_decode($res,true);

                        if(empty($view_data)){
                            $this->stop_task(30);
                            exit;
                        }

                        //处理附件
                        $attr_data = $view_data['enclosures'];
                        $file_data = [];
                        if($attr_data){
                            foreach($attr_data as $f){
                                $file_content = 'https://sf.caa123.org.cn'.$f['filePath'];
                                if(!file_exists($file_path)){
                                    mkdir($file_path,0777,true);
                                }
                                $local_file = $file_path.'/sf_'.basename($file_content);
                                if(!is_file($local_file)){
                                    file_put_contents($local_file,file_get_contents($file_content));
                                }
                                $ext = get_ext_name($file_content);
                                $file_data[] = [
                                    'url' => str_replace(SPIDER_FILE_SAVE_URL,'',$local_file),
                                    'name' => $f['filename'],
                                    'ext' => $ext
                                ];
                            }
                        }

                        //处理图片
                        $img_array = $view_data['pictures'];
                        $img_data = [];
                        if($img_array){
                            foreach($img_array as $f){
                                $file_content = 'https://sf.caa123.org.cn'.$f['filePath'];
                                if(!file_exists($file_path)){
                                    mkdir($file_path,0777,true);
                                }
                                $local_file = $file_path.'/sf_'.basename($file_content);
                                if(!is_file($local_file)){
                                    file_put_contents($local_file,file_get_contents($file_content));
                                }
                                $img_data[]['img'] = str_replace(SPIDER_FILE_SAVE_URL,'',$local_file);
                            }
                        }

                        $add_data = [
                            'pid' => 30,
                            'url' => $real_url,
                            'pub_time' => $publishDate,
                            'title' => $view_data['name'],
                            'img_data' => json_encode($img_data,JSON_UNESCAPED_UNICODE),
                            'file_data' => json_encode($file_data,JSON_UNESCAPED_UNICODE),
                            'content' => htmlspecialchars_decode($view_data['description']),
                            'module_id' => 27,//拍卖
                            'create_time' => _time()
                        ];

                        $this->dbm->insert('collect_data',$add_data);
                    }
                }
            }
        }

        $this->stop_task(30);
    }
}