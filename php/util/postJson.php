<?php
/**
 * Created by YX.
 * Date: 2019-03-24
 * Time: 14:45
 */
include_once dirname(__FILE__) . "/../util/logger.php";

class JsonKey
{
    const UserId = "userId";
    const Token = "token";
}

class JsonObj
{
    var $_arr;

    function __construct($json)
    {
        if ($json == false) {
            $this->_arr = null;
            return;
        }
        if (gettype($json) == "string") {
            $json = urldecode($json);
            $this->_arr = json_decode($json);
        }
        else
            $this->_arr = $json;
        //$this->_arr = json_decode($json, true);
    }

    function get($field)
    {
        if (null == $this->_arr)
            return "";
        if (property_exists($this->_arr, $field))
            return $this->_arr->{$field};
//        if (array_key_exists($field, $this->_arr)) {
//            return $this->_arr[$field];
//        }
        return "";
    }
}

class GamePostJson
{
    var $json;

    function __construct()
    {
        $str = file_get_contents("php://input");
        $this->json = new JsonObj($str);
    }

    function getStr($field)
    {
        return $this->json->get($field);
    }
}

class GamePostOrGetJson
{
    var $json;

    function __construct()
    {
        $str = file_get_contents("php://input");
        $this->json = new JsonObj($str);
    }

    function getStr($field)
    {
        $str = $this->json->get($field);

        if ($str === "" )
            $str = $_GET[$field];

        return $str;
    }
}
