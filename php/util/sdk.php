<?php
/**
 * Created by YX.
 * Date: 2019/5/14
 * Time: 12:21
 */

include_once dirname(__FILE__) . "/toolTime.php";

class SdkQNHD
{
//    static function decodeUnicode($str)
//    {
//        return preg_replace_callback('/\\\\u([0-9a-f]{4})/i',
//            create_function(
//                '$matches',
//                'return mb_convert_encoding(pack("H*", $matches[1]), "UTF-8", "UCS-2BE");'
//            ),
//            $str);
//    }

    static function get($uri)
    {
        $utc = ToolTime::getUtc() * 1000;
        $mapSign = array(
            "companyId" => Config::QNHDCompanyId,
            "timestamp" => $utc,
            "uri" => $uri,
        );
        ksort($mapSign);
        $strSign = "";
        foreach ($mapSign as $k => $v) {
            $strSign = $strSign . $k . "=" . $v . "&";
        }
        $strSign = substr($strSign, 0, strlen($strSign) - 1);
        $strSign = $strSign . Config::QNHDSecretKey;
//        var_dump($strSign);
        $sign = strtoupper(hash("sha256", $strSign));
//        var_dump($sign);

        $headers = array(
            "companyId:" . Config::QNHDCompanyId,
            "timestamp:" . $utc,
            "sign:" . $sign,
        );
//        var_dump($headers);

        //初始化 curl
        $ch = curl_init();
        //设置目标服务器
        curl_setopt($ch, CURLOPT_URL, Config::UrlSdkQNHD . $uri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //超时时间
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        // 头
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//            // post数据
//            curl_setopt($ch, CURLOPT_POST, 1);
//            // post的变量
//            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        $output = curl_exec($ch);
//        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

//        var_dump($output);
        return $output;
    }

    static function post($uri, $mapPost)
    {
        $utc = ToolTime::getUtc() * 1000;
        $mapSign = array(
            "companyId" => Config::QNHDCompanyId,
            "timestamp" => $utc,
            "uri" => $uri,
        );
        foreach ($mapPost as $k => $v) {
//            var_dump("k:".$k."v:".$v);
            $mapSign[$k] = $v;
        }
        ksort($mapSign);
        $strSign = "";
        foreach ($mapSign as $k => $v) {

            if(is_array($v))
            {
//                ksort($v);
//                $jsonStr = self::decodeUnicode(json_encode($v));
                $jsonStr = json_encode($v,JSON_UNESCAPED_UNICODE);
//                var_dump("k:".$k."v:".$jsonStr);
                $strSign = $strSign . $k . "=" . $jsonStr . "&";
            }
            else
            {
                $strSign = $strSign . $k . "=" . $v . "&";
            }

//            if(is_numeric($v) || is_string($v))
//            {
//                $strSign = $strSign . $k . "=" . $v . "&";
//            }
//            else
//            {
//                $jsonStr = json_encode($v);
//                $strSign = $strSign . $k . "=" . $jsonStr . "&";
//            }

        }
        $strSign = substr($strSign, 0, strlen($strSign) - 1);
        $strSign = $strSign . Config::QNHDSecretKey;
//        var_dump("签名：".$strSign);
        $sign = strtoupper(hash("sha256", $strSign));
//        var_dump($sign);

        $headers = array(
            "Content-Type: text/plain",
            "companyId:" . Config::QNHDCompanyId,
            "timestamp:" . $utc,
            "sign:" . $sign,
//            "sign:" . $strSign,
        );
//        var_dump($headers);

        //初始化 curl
        $ch = curl_init();
        //设置目标服务器
        curl_setopt($ch, CURLOPT_URL, Config::UrlSdkQNHD . $uri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //超时时间
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        // 头
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        // post数据
        curl_setopt($ch, CURLOPT_POST, 1);
        // post的变量
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($mapPost));
        //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/plain'));// 必须声明请求头
//        var_dump(json_encode($mapPost));
        $output = curl_exec($ch);
//        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

//        var_dump($output);
        return $output;
    }

    static function put($uri, $mapPost)
    {
        $utc = ToolTime::getUtc() * 1000;
        $mapSign = array(
            "companyId" => Config::QNHDCompanyId,
            "timestamp" => $utc,
            "uri" => $uri,
        );
        foreach ($mapPost as $k => $v) {
            $mapSign[$k] = $v;
        }
        ksort($mapSign);
        $strSign = "";
        foreach ($mapSign as $k => $v) {
            if(is_array($v))
            {
                $jsonStr = json_encode($v);
                $strSign = $strSign . $k . "=" . $jsonStr . "&";
            }
            else
            {
                $strSign = $strSign . $k . "=" . $v . "&";
            }

        }
        $strSign = substr($strSign, 0, strlen($strSign) - 1);
        $strSign = $strSign . Config::QNHDSecretKey;
//        var_dump($strSign);
        $sign = strtoupper(hash("sha256", $strSign));
//        var_dump($sign);

        $headers = array(
            "Content-Type: text/plain",
            "companyId:" . Config::QNHDCompanyId,
            "timestamp:" . $utc,
            "sign:" . $sign,
        );
//        var_dump($headers);

        //初始化 curl
        $ch = curl_init();
        //设置目标服务器
        curl_setopt($ch, CURLOPT_URL, Config::UrlSdkQNHD . $uri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //超时时间
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        // 头
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        // post数据
//        curl_setopt($ch, CURLOPT_PUT, 1);
        curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        // post的变量
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($mapPost));
//        curl_setopt ($ch, CURLOPT_HTTPHEADER, array('Content-type:application/json'));
        //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/plain'));// 必须声明请求头
//        var_dump(json_encode($mapPost));
        $output = curl_exec($ch);
//        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

//        var_dump($output);
        return $output;
    }

    static function createOrderNo()
    {
        return ToolTime::getUtc() + ToolRedis::get()->incr("sys:player_id_incr:k");
    }
}
