<?php
/**
 * Created by PhpStorm.
 * User: kangly
 * Date: 2018/4/22
 * Time: 16:41
 */

/**
 * 自动加载类文件
 * @param $className
 */
spl_autoload_register(function($className){
    //linux环境
    $pathArr = explode('/',getcwd());
    //windows环境
    //$pathArr = explode('\\',getcwd());
    $filePath = $pathArr[count($pathArr)-1];
    $include_path = dirname(__FILE__).'/class/'.$filePath.'/';
    set_include_path($include_path);
    include_once($className . '.php');
});

/**
 * curl get
 * @param $url
 * @param array $params
 * @param array $headers
 * @return bool|mixed
 */
function http_get($url, array $params = array(),array $headers = array()){
    if($params){
        if(strpos($url, '?')){
            $url.= "&" . http_build_query($params);
        }else{
            $url.= "?" . http_build_query($params);
        }
    }
    $time = 30;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, $time);
    if (strpos($url, 'https') === 0) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    } else {
        curl_setopt($ch, CURLOPT_URL ,$url);
    }
    $body = curl_exec($ch);
    curl_close($ch);
    return $body;
}

/**
 * curl post
 * @param $url
 * @param string $json_data json数据
 * @return mixed
 */
function http_post($url,$json_data){
    $timeout = 30;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

/**
 * 获取文件后缀名
 * @param $name_or_url
 * @return string
 */
function get_ext_name($name_or_url){
    $url = strstr($name_or_url,'?',true);
    if(!$url){
        $url = strstr($name_or_url,'@',true);
    }
    if(!$url){
        $url = strstr($name_or_url,'#',true);
    }
    if(!$url){
        $url = $name_or_url;
    }
    $ext = substr($url,strrpos($url,'.')+1);
    if(!$ext){
        $ext = '';
    }
    return $ext;
}

/**
 * 输出信息
 * @param $str
 */
function de($str){
    echo "\n";
    print_r($str);
    echo "\n";
}

/**
 * 当前时间
 * @return bool|string
 */
function _time(){
    return date('Y-m-d H:i:s');
}

/**
 * 去除空格、换行等字符
 * @param $content
 * @param string $replace
 * @return mixed
 */
function _str_replace($content,$replace=''){
    $content = str_replace(['&nbsp;','&#160;'],$replace,$content);
    $content = str_replace("    ",$replace,$content);
    $content = str_replace([" ","　"," ","\t","\n","\r"],$replace,$content);
    return $content;
}

/**
 * 写入数据到文件
 * @param $log
 * @param string $file
 */
function _log($log,$file='test'){
    $log = is_array($log)?var_export($log,true):$log;
    file_put_contents('../log/'.$file.'.txt', $log."\n", FILE_APPEND);
}