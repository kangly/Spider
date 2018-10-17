<?php
/**
 * Created by PhpStorm.
 * User: kangly
 * Date: 2018/5/8
 * Time: 09:31
 */
require_once '../base.php';
use QL\QueryList;

//抓取58同城二手设备当天信息
//58同城会禁止服务器集群的ip访问,阿里云和腾讯云采集不了,本地可以采集
class curlwubaClass extends baseClass
{
    function run()
    {
        $this->curl_wuba();
    }

    public function curl_wuba()
    {
        $this->start_task(1);

        $this_day = date('Y-m-d');
        $img_path = SPIDER_FILE_SAVE_URL.'/Upload/'.date('Ymd');

        //获取58所有二手设备城市列表
        $url = '../ext/58_urls.json';
        $json_data = file_get_contents($url);
        $data = json_decode($json_data,true);

        //开始循环采集每个城市下的信息
        foreach($data as $v)
        {
            sleep(20);

            $url = $v['url'];//二手设备城市链接
            $city_state = true;//是否继续抓取该城市下信息
            $page = 1;//该城市下抓取页数

            while(true)
            {
                if(!$this->load_task_state(1)){
                    exit;
                }

                $city_url = $url.'0/pn'.$page.'/';
                $url_data = parse_url($city_url);
                $base_url = $url_data['host'];

                //按时间排序,抓取个人下的数据
                $city_url = $city_url.'?sort=sortid_desc';

                $details = QueryList::run('Request',[
                    'target' => $city_url,
                    'referrer' => $base_url,
                    'host' => $base_url,
                    'method' => 'GET',
                    'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.181 Safari/537.36',
                  	':authority' => $base_url,
                    ':method' => 'GET',
                    ':path' => '/ershoushebei/0/pn'.$page.'/?sort=sortid_desc',
                    ':scheme' => 'https',
                    'accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
                    'accept-encoding' => 'gzip, deflate, br',
                    'accept-language' => 'zh-CN,zh;q=0.9*/',
                  	'cookiePath' => '../cookie.txt',
                    'timeout' =>'30'
                ]);

                $source = $details->html;
                if(!$source){
                    $this->stop_task(1);
                    exit;
                }

                //获取城市
                $pc_data = QueryList::Query($source,array(
                    'pc' => array('meta[name="location"]','content')
                ))->data;

                //采集列表源解析失败,退出 可能情况:1页面布局改变,2采集被封
                if(empty($pc_data)){
                    $this->stop_task(1);
                    exit;
                }

                //处理省市
                $pc = explode(';',$pc_data[0]['pc']);
                $province = str_replace('province=','',$pc[0]);
                $city = str_replace('city=','',$pc[1]);

                //获取列表中所有详情链接
                $data = QueryList::Query($source,array(
                    'url' => array('td:eq(1) a:eq(0)','href','',function($content){
                        $data = explode('?',$content);
                        return $data[0];
                    }),
                    'class1' => array('td:eq(1) .ico.accurate','class'),
                    'class2' => array('td:eq(1) .ico.ding','class')
                ),'#infolist .tbimg tr')->data;

                //采集详情源解析失败,退出 可能情况:1页面布局改变,2采集被封
                if(empty($data)){
                    $this->stop_task(1);
                    exit;
                }

                foreach($data as $ck=>$cv)
                {
                    $view_url = $cv['url'];

                    //去掉精、顶和非本城市下的链接
                    if(strpos($view_url,$base_url)===false || $cv['class1'] || $cv['class2']){
                        continue;
                    }

                    if(!$this->load_task_state(1)){
                        exit;
                    }

                    $is_change = $this->dbm->select('collect_data', ['id'], ['url'=>$view_url,'pid'=>1]);
                    if(empty($is_change))
                    {
                        sleep(20);
                      
                      	$path_dirname = pathinfo($view_url,PATHINFO_DIRNAME);
                        $url_path = str_replace($path_dirname,'',$view_url);

                        $cv_details = QueryList::run('Request',[
                            'target' => $view_url,
                            'referrer' => $city_url,
                            'host' => $base_url,
                            'method' => 'GET',
                            'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.181 Safari/537.36',
                          	':authority' => $base_url,
                            ':method' => 'GET',
                            ':path' => $url_path,
                            'scheme' => 'https',
                            'accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
                            'accept-encoding' => 'gzip, deflate, br',
                            'accept-language' => 'zh-CN,zh;q=0.9',
                            'cache-control' => 'max-age=0*/',
                          	'cookiePath' => '../cookie.txt',
                            'timeout' =>'30'
                        ]);

                        $cv_source = $cv_details->html;
                        if(!$cv_source){
                            $this->stop_task(1);
                            exit;
                        }

                        $data = QueryList::Query($cv_source,array(
                            'time' => array('.detail-title__info__text:eq(0)','text','',function($content){
                                return str_replace(' 发布','',$content);
                            }),
                            'title' => array('h1.detail-title__name','text','',function($content){
                                return _str_replace($content);
                            }),
                            'content1' => array('div.detail-desc__ershoushebei','html'),
                            'content2' => array('.description_con','html')
                        ))->data;

                        //获取详情页数据源失败
                        if(empty($data)){
                            $this->stop_task(1);
                            exit;
                        }

                        //跳过非当天数据
                        if($data[0]['time'] != $this_day){
                            $city_state = false;
                            break;
                        }

                        //只能抓取到58同城的转拨电话,真实电话目前是抓取不到的
                        $phone = '';

                        /*//暂停10秒
                        sleep(10);

                        //抓取电话,固定格式
                        $phone_id = pathinfo($view_url,PATHINFO_FILENAME);
                        $phone_id = str_replace('x','',$phone_id);
                        $phone_url = 'https://bossapi.58.com/smallapp/common/link?sign=6cb4c891c9a408dea0e7f5a86874174d59ac1880&infoId='.$phone_id.'&source=0&type=0';

                        $phone_details = QueryList::run('Request',[
                            'target' => $phone_url,
                            'method' => 'GET',
                            'referrer' => 'https://servicewechat.com/wxc81edb242dec62d4/47/page-frame.html',
                            'id58' => 'f6ab7d840dd8403ef37c3d8bc3e17316',
                            'Host' => 'bossapi.58.com',
                            'user_agent' => 'Mozilla/5.0 (Linux; Android 8.0; STF-AL10 Build/HUAWEISTF-AL10; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/53.0.2785.143 Crosswalk/24.53.595.0 XWEB/153 MMWEBSDK/21 Mobile Safari/537.36 MicroMessenger/6.6.7.1321(0x26060737) NetType/WIFI Language/zh_CN MicroMessenger/6.6.7.1321(0x26060737) NetType/WIFI Language/zh_CN',
                            'cookiePath' => '../cookie.txt',
                            'timeout' =>'30'
                        ]);

                        $phone_data = json_decode($phone_details->html,true);
                        $phone = intval($phone_data['data']);

                        //电话获取失败,结果可能为:请求命中防刷
                        if(!$phone){
                            exit;
                        }*/

                        $cdata = QueryList::Query($cv_source,array(
                            'field' => array('.infocard__container .infocard__container__item','text','-.infocard__container__item__main__link--im -.im-chat -.infocard__container__item__main__link--wx -.btn_tocompletetel -.free_tel',function($content){
                                return _str_replace($content);
                            })
                        ))->data;

                        $contact = '';
                        foreach($cdata as $v)
                        {
                            if($v['field']){
                                if(strpos($v['field'],'联系人：') !== false){
                                    $contact = str_replace('联系人：','',$v['field']);
                                }
                            }
                        }

                        //图片仅抓取前三张
						$img_data = [];
                        $img_data = QueryList::Query($cv_source,array(
                            'img' => array('.imgplayerlist img:lt(2)','src','',function($content) use($img_path){
                                if($content){
                                    $content = 'http:'.$content;
                                    $image = $content;
                                    $p = strpos($content,'?w');
                                    if($p !== false){
                                        $image = substr($content,0,$p);
                                    }
                                    if (!file_exists($img_path)){
                                        mkdir($img_path,0777,true);
                                    }
                                    $local_image = $img_path.'/58_'.basename($image);
                                    if(!is_file($local_image)){
                                        file_put_contents($local_image,file_get_contents($content));
                                    }
                                    return str_replace(SPIDER_FILE_SAVE_URL,'',$local_image);
                                }else{
                                    return false;
                                }
                            })
                        ))->data;

                        $area_province = [];
                        if($province){
                            $ap_sql = sprintf("select areaid from destoon_area where parentid=0 and areaname like '%%%s%%'",$province);
                            $area_province = $this->dbm->query($ap_sql)->fetch();
                        }

                        $area_city = [];
                        if($city){
                            $ac_sql = sprintf("select areaid from destoon_area where parentid>0 and areaname like '%%%s%%'",$city);
                            $area_city = $this->dbm->query($ac_sql)->fetch();
                        }

                        $add_data = [
                            'url' => $view_url
                        ];
                        $add_data['pid'] = 1;
                        $add_data['title'] = $data[0]['title'];
                        $add_data['contact'] = $contact;
                        $add_data['phone'] = $phone;
                        $add_data['city'] = $city;
                        $add_data['province_id'] = $area_province['areaid']>0?$area_province['areaid']:0;;
                        $add_data['area_id'] = $area_city['areaid']>0?$area_city['areaid']:0;
                        $add_data['content'] = $data[0]['content1'].$data[0]['content2'];
                        $add_data['img_data'] = json_encode($img_data,JSON_UNESCAPED_UNICODE);
                        $add_data['pub_date'] = $data[0]['time'];
                        $add_data['create_time'] = _time();

                        //保存信息
                        $this->dbm->insert('collect_data',$add_data);
                    }
                }

                if($city_state===false){
                    break;
                }

                $page++;
            }
        }

        $this->stop_task(1);
    }
}