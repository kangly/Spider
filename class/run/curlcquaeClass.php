<?php
/**
 * Created by PhpStorm.
 * User: kangly
 * Date: 2018/10/16
 * Time: 15:38
 */
require_once '../base.php';
use QL\QueryList;

//抓取重庆市加工贸易废料交易平台
class curlcquaeClass extends baseClass
{
    function run()
    {
        $this->curl_cquae();
    }

    protected function curl_cquae()
    {
        $this->start_task(29);

        $this_day = date('Y-m-d');
        $this_date = strtotime($this_day);
        $img_path = SPIDER_FILE_SAVE_URL.'/Upload/'.date('Ymd');

        $__PageViewDetailId = '';
        $__VIEWSTATE = '';
        $__EVENTVALIDATION = '';
        $__EVENTARGUMENT = '';
        $ctl00_hcSearch_hfText = '';
        $ctl00_hcSearch_hfValue = '';
        $ctl00_ContentPlaceHolder1_Hidden_SelectViewControlID = '';
        $ctl00_ContentPlaceHolder1_qc_State_dropdown_hfComboValue = '';
        $ctl00_hfPageNameABCD = '';
        $ctl00_hfButtonNameABCD = '';
        $__PageViewDetailPerId = '';
        $__VIEWSTATEGENERATOR = '';
        $ctl00_ContentPlaceHolder1_dpWasteType_dropdown_hfComboValue = '';
        $url = 'http://www.recycle.cquae.com/Trade_Announcement/AnnouncementList.aspx?mid=1107131&fid=0';

        $pub_data = $this->dbm->get('collect_data', ['pub_time'], [
            'AND' => [
                'pid' => 29
            ],
            'ORDER' => [
                'id' => 'DESC'
            ]]);
        $pub_day = $pub_data['pub_time'];
        $pub_date = strtotime($pub_day);

        for($i=1;$i>0;$i++)
        {
            if(!$this->load_task_state(29)){
                exit;
            }

            sleep(6);

            if($i>1)
            {
                $details = QueryList::run('Request', [
                    'target' => $url,
                    'method' => 'POST',
                    'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.181 Safari/537.36',
                    'Host' => 'www.recycle.cquae.com',
                    'Origin' => 'http://www.recycle.cquae.com',
                    'X-Requested-With' => 'XMLHttpRequest',
                    'Cache-Control' => 'no-cache',
                    'X-MicrosoftAjax' => 'Delta=true',
                    'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
                    'Referer' => 'http://www.recycle.cquae.com/Trade_Announcement/AnnouncementList.aspx?mid=1107131&fid=0',
                    'params' => [
                        'ctl00$__PageViewDetailId'=>$__PageViewDetailId,
                        'ctl00$ScriptManager1'=>'ctl00$UpdatePanel1|ctl00$ContentPlaceHolder1$viewcontrolJ5$viewcontrolJ5$pager$ctl05',
                        '__VIEWSTATE'=>$__VIEWSTATE,
                        '__EVENTVALIDATION'=>$__EVENTVALIDATION,
                        '__EVENTTARGET'=>'ctl00$ContentPlaceHolder1$viewcontrolJ5$viewcontrolJ5$pager$ctl05',
                        '__EVENTARGUMENT'=>$__EVENTARGUMENT,
                        'ctl00$hcSearch$hfText'=>$ctl00_hcSearch_hfText,
                        'ctl00$hcSearch$hfValue'=>$ctl00_hcSearch_hfValue,
                        'ctl00$ContentPlaceHolder1$Hidden_SelectViewControlID'=>$ctl00_ContentPlaceHolder1_Hidden_SelectViewControlID,
                        'ctl00$ContentPlaceHolder1$dpTown$dropdown$hfComboValue'=>$ctl00_ContentPlaceHolder1_qc_State_dropdown_hfComboValue,
                        'ctl00$hfPageNameABCD'=>$ctl00_hfPageNameABCD,
                        'ctl00$hfButtonNameABCD'=>$ctl00_hfButtonNameABCD,
                        'ctl00$__PageViewDetailPerId'=>$__PageViewDetailPerId,
                        '__VIEWSTATEGENERATOR'=>$__VIEWSTATEGENERATOR,
                        'ctl00_ContentPlaceHolder1_dpWasteType_dropdown_hfComboValue'=>$ctl00_ContentPlaceHolder1_dpWasteType_dropdown_hfComboValue
                    ],
                    'cookiePath' => '../cookie.txt',
                    'timeout' => '30'
                ]);
            }
            else
            {
                $details = QueryList::run('Request', [
                    'target' => $url,
                    'method' => 'GET',
                    'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.181 Safari/537.36',
                    'cookiePath' => '../cookie.txt',
                    'timeout' => '30'
                ]);
            }

            $source = $details->html;
            if(!$source){
                $this->stop_task(29);
                exit;
            }

            $id_data = QueryList::Query($source, array(
                'id' => array('#__PageViewDetailId','value'),
                'field1' => array('#__VIEWSTATE','value'),
                'field2' => array('#__EVENTVALIDATION','value'),
                'field3' => array('#__EVENTARGUMENT','value'),
                'field4' => array('#ctl00_hcSearch_hfText','value'),
                'field5' => array('#ctl00_hcSearch_hfValue','value'),
                'field6' => array('#ctl00_ContentPlaceHolder1_Hidden_SelectViewControlID','value'),
                'field7' => array('#ctl00_ContentPlaceHolder1_qc_State_dropdown_hfComboValue','value'),
                'field8' => array('#ctl00_hfPageNameABCD','value'),
                'field9' => array('#ctl00_hfButtonNameABCD','value'),
                'field10' => array('#__PageViewDetailPerId','value'),
                'field11' => array('#__VIEWSTATEGENERATOR','value'),
                'field12' => array('#ctl00_ContentPlaceHolder1_dpWasteType_dropdown_hfComboValue','value'),
            ))->data;

            if(empty($id_data)){
                $this->stop_task(29);
                exit;
            }

            $__PageViewDetailId = $id_data[0]['id'];
            $__VIEWSTATE = $id_data[0]['field1'];
            $__EVENTVALIDATION = $id_data[0]['field2'];
            $__EVENTARGUMENT = $id_data[0]['field3'];
            $ctl00_hcSearch_hfText = $id_data[0]['field4'];
            $ctl00_hcSearch_hfValue = $id_data[0]['field5'];
            $ctl00_ContentPlaceHolder1_Hidden_SelectViewControlID = $id_data[0]['field6'];
            $ctl00_ContentPlaceHolder1_qc_State_dropdown_hfComboValue = $id_data[0]['field7'];
            $ctl00_hfPageNameABCD = $id_data[0]['field8'];
            $ctl00_hfButtonNameABCD = $id_data[0]['field9'];
            $__PageViewDetailPerId = $id_data[0]['field10'];
            $__VIEWSTATEGENERATOR = $id_data[0]['field11'];
            $ctl00_ContentPlaceHolder1_dpWasteType_dropdown_hfComboValue = $id_data[0]['field12'];

            $list_data = QueryList::Query($source, array(
                'title' => array('td:eq(1) a','text'),
                'id' => array('td:eq(1) a','datavalue'),
                'time' => array('.wordspace_all:last span','text','',function($content){
                    $content = explode('  ',$content);
                    return $content[0];
                })
            ),'.grid_content_table tr:gt(0)','UTF-8','UTF-8',true)->data;

            $over_num = 0;
            foreach($list_data as $v)
            {
                if(!$this->load_task_state(29)){
                    exit;
                }

                if($pub_day)
                {
                    //处理周末或者节假日问题
                    if(strtotime($v['time'])<$pub_date){
                        $over_num++;
                        if($over_num>=5){
                            $this->stop_task(29);
                            exit;
                        }else{
                            continue;
                        }
                    }
                }
                else if(strtotime($v['time'])<$this_date)
                {
                    $over_num++;
                    if($over_num>=5){
                        $this->stop_task(29);
                        exit;
                    }else{
                        continue;
                    }
                }

                $view_url = 'http://www.recycle.cquae.com/Trade_Announcement/AnonymousAnnounceDetail.aspx?id='.$v['id'];
                $is_change = $this->dbm->select('collect_data', ['id'], ['url'=>$view_url,'pid'=>29]);
                if(empty($is_change))
                {
                    sleep(6);

                    $content_url = 'http://www.recycle.cquae.com/Service/DisplayAnnouncementContent.ashx?contents='.$v['id'];

                    $content_data = QueryList::Query($content_url, array(
                        'content' => array('','html')
                    ),'','UTF-8','UTF-8',true)->data;

                    if(empty($content_data)){
                        $this->stop_task(29);
                        exit;
                    }

                    $content = $content_data[0]['content'];

                    $img_array = QueryList::Query($content, array(
                        'url' => array('a','href','',function($content){
                            return 'http://www.recycle.cquae.com'.str_replace('../..','',$content);
                        }),
                        'name' => array('a','text')
                    ),'','UTF-8','UTF-8',true)->data;

                    $img_data = [];
                    foreach($img_array as $fk=>$f){
                        if(strpos($f['url'],'type=download') !== false){
                            if(!file_exists($img_path)){
                                mkdir($img_path,0777,true);
                            }
                            $ext = get_ext_name($f['name']);
                            $local_file = $img_path.'/cquae_'.$fk.time().'.'.$ext;
                            if(!is_file($local_file)){
                                file_put_contents($local_file,file_get_contents($f['url']));
                            }
                            $img_data[]['img'] = str_replace(SPIDER_FILE_SAVE_URL,'',$local_file);
                        }
                    }

                    sleep(6);

                    $file_array = QueryList::Query($view_url, array(
                        'url' => array('#ctl00_ContentPlaceHolder1_txtContract_Sample a','href','',function($content){
                            return 'http://www.recycle.cquae.com'.$content;
                        }),
                        'name' => array('#ctl00_ContentPlaceHolder1_txtContract_Sample a','text')
                    ),'','UTF-8','UTF-8',true)->data;

                    $file_data = [];
                    foreach($file_array as $fk=>$f){
                        if(strpos($f['url'],'type=download') !== false){
                            if(!file_exists($img_path)){
                                mkdir($img_path,0777,true);
                            }
                            $ext = get_ext_name($f['name']);
                            $local_file = $img_path.'/cquae_'.$fk.time().'.'.$ext;
                            if(!is_file($local_file)){
                                file_put_contents($local_file,file_get_contents($f['url']));
                            }
                            $file_data[] = [
                                'url' => str_replace(SPIDER_FILE_SAVE_URL,'',$local_file),
                                'name' => $f['name'],
                                'ext' => $ext
                            ];
                        }
                    }

                    $content = preg_replace('/<a .*?href="(.*?)".*?>*<\/a>/is','',$content);

                    $add_data = [
                        'pid' => 29,
                        'url' => $view_url,
                        'pub_time' => $this_day,
                        'title' => $v['title'],
                        'img_data' => json_encode($img_data,JSON_UNESCAPED_UNICODE),
                        'file_data' => json_encode($file_data,JSON_UNESCAPED_UNICODE),
                        'content' => htmlspecialchars_decode($content),
                        'province_id' => 4,
                        'city' => '重庆',
                        'area_id' => 0,
                        'module_id' => 27,//拍卖
                        'create_time' => _time()
                    ];

                    $this->dbm->insert('collect_data',$add_data);
                }
            }
        }

        $this->stop_task(29);
    }
}