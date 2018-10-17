<?php
/**
 * Created by PhpStorm.
 * User: kangly
 * Date: 2018/9/10
 * Time: 15:26
 */
require_once '../base.php';

//页面标签如果遇到被编辑器转义问题,发现再解决
//抓取中拍平台,附件,未发现图片
class curlpaimaiClass extends baseClass
{
    function run()
    {
        $this->curl_paimai();
    }

    protected function curl_paimai()
    {
        $this->start_task(24);

        $this_day = date('Y-m-d');
        $file_path = SPIDER_FILE_SAVE_URL.'/Upload/'.date('Ymd');
        $params = [];
        $headers = [
            'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
            'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.81 Safari/537.36'
        ];

        $pub_data = $this->dbm->get('collect_data', ['pub_time'], [
            'AND' => [
                'pid' => 24
            ],
            'ORDER' => [
                'id' => 'DESC'
            ]]);
        $pub_day = $pub_data['pub_time'];

        for($i=0;$i>=0;$i++)
        {
            if(!$this->load_task_state(24)){
                exit;
            }

            sleep(6);

            $url = 'https://paimai.caa123.org.cn/caa-search-ws/ws/0.1/notices?start='.$i.'&count=10';
            $res = http_get($url,$params,$headers);
            $res = json_decode($res,true);
            $html = $res['items'];
            foreach($html as $v)
            {
                if(!$this->load_task_state(24)){
                    exit;
                }

                //处理周末或者节假日问题
                $publishDate = date('Y-m-d',$v['publishDate']/1000);
                if($pub_day)
                {
                    if(strtotime($publishDate)<strtotime($pub_day)){
                        $this->stop_task(24);
                        exit;
                    }
                }
                else if($publishDate!=$this_day)
                {
                    $this->stop_task(24);
                    exit;
                }

                $real_url = 'https://paimai.caa123.org.cn/pages/notice/item.html?id='.$v['id'];
                $is_change = $this->dbm->select('collect_data', ['id'], ['url'=>$real_url,'pid'=>24]);
                if(empty($is_change))
                {
                    sleep(6);

                    $url = 'https://paimai.caa123.org.cn/caa-search-ws/ws/0.1/notice/'.$v['id'];
                    $res = http_get($url,$params,$headers);
                    $res = json_decode($res,true);
					
                    //处理内容,编辑器会自动去掉label标签,先写成span标签,ui li标签替换为div,发现其他问题再解决
                    $content = '<div class="other-info">
                                <div>
                                    <span style="float:left;margin: 0;padding: 0;">拍卖地点：</span>
                                    <p>'.$res['address'].'</p>
                                </div>
                                <div>
                                    <span style="float:left;margin: 0;padding: 0;">拍卖时间：</span>
                                    <p>'.date('Y-m-d H:i:s',$res['auctionTime']/1000).'</p>
                                </div>
                                <div>
                                    <span style="float:left;margin: 0;padding: 0;">预展地点：</span>
                                    <p>'.$res['previewAddress'].'</p>
                                </div>
                                <div>
                                    <span style="float:left;margin: 0;padding: 0;">预展时间：</span>
                                    <p>'.$res['previewTime'].'</p>
                                </div>
                            </div>
                            <br>'
                        .$res['content'];

                    //处理附件
                    $attr_data = $res['annexeList'];
                    $file_data = [];
                    if($attr_data)
                    {
                        foreach($attr_data as $f)
                        {
                            $file_content = 'https://paimai.caa123.org.cn'.$f['filePath'];
                            if(!file_exists($file_path)){
                                mkdir($file_path,0777,true);
                            }
                            $local_file = $file_path.'/paimai_'.basename($file_content);
                            if(!is_file($local_file)){
                                file_put_contents($local_file,file_get_contents($file_content));
                            }
                            $ext = get_ext_name($file_content);
                            $file_data[] = [
                                'url' => str_replace(SPIDER_FILE_SAVE_URL,'',$local_file),
                                'name' => $f['fileName'],
                                'ext' => $ext
                            ];
                        }
                    }

                    $add_data = [
                        'pid' => 24,
                        'url' => $real_url,
                        'pub_time' => $publishDate,
                        'title' => $res['name'],
                        'file_data' => json_encode($file_data,JSON_UNESCAPED_UNICODE),
                        'content' => htmlspecialchars_decode($content),
                        'module_id' => 27,//拍卖
                        'create_time' => _time()
                    ];

                    $this->dbm->insert('collect_data',$add_data);
                }
            }
        }

        $this->stop_task(24);
    }
}