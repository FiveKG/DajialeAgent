<?php
include_once dirname(__FILE__) . "/../config.php";
class ToolNet {
    /**获取本地ip:http://127.0.0.1/
     * @return mixed
     */
    static function getLocalUrl() {
        $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        socket_connect($sock, "8.8.8.8", 53);
        socket_getsockname($sock, $name); // $name passed by reference

        $url = "http://$name";
        return $url;
    }

    static function push2GameServer($serverUrl) {
        $ch = curl_init($serverUrl);
        $result = curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if (!$result)
            return false;

        $output = curl_exec($ch);//output 为服务端接收消息后返回的信息
        curl_close($ch);
        var_dump('======',$serverUrl);
        var_dump($output);
        return true;
    }

    static function sendByGet($url,$argMap=array(),$headers = array()) {
        if(sizeof($argMap)>0) {
            $url = $url."?";
            foreach ($argMap as $key=>$value) {
                $url = $url.$key."=".$value."&";
            }
            $url = substr($url,0,-1);
        }

        $ch = curl_init();
        if (sizeof($headers)>0) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        //关闭https验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, false);

        $output = curl_exec($ch);//outpue 为服务端接收消息后返回的信息
        curl_close($ch);
        if($output)
            return $output;
        return true;
    }

    static function sendByPost($url, $argMap=array(),$headers = array()) {
        $ch = curl_init();
        $headers[] = "Content-type:application/json;charset=utf-8";
        $headers[] = "X-AjaxPro-Method:ShowList";

        curl_setopt($ch, CURLOPT_URL, $url);
        //关闭https验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30); //最多等待60秒
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //设置curl_exec获取的信息的返回方式
        curl_setopt($ch, CURLOPT_POST, true);//设置发送方式为post请求
        curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($argMap));  //设置post的数据
        $output = curl_exec($ch);//outpue 为服务端接收消息后返回的信息

        $httpCode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        curl_close($ch);
        if($output)
            return $output;
        return true;
    }

}
