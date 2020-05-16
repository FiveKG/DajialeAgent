<?php
include_once dirname(__FILE__) . '/toolTime.php';
class Logger
{
    static function debug(string $str, $object = '')
    {
        $from = debug_backtrace();

        $fromFile = $from[0]["file"];
        $fromFun = $from[0]["function"];
        $fromLine = $from[0]["line"];
        $obj = json_encode($object);
        $time = date('Y-m-d H:i:s', ToolTime::getLocalSec());
        $msg = "[" . $time . "] " . "[DEBUG]: " . $fromFile . " : " . $fromLine . ": message: " . $str . " " . $obj."\n";

        $fileName = dirname(__FILE__).'/../backend/log/' . date('Y-m-d', ToolTime::getLocalSec()) . ".log";
        $file = fopen($fileName, 'a+');
        fwrite($file, $msg, 4096);
        fclose($file);

    }

    static function error(string $str, ...$args)
    {
        $from = debug_backtrace();

        $fromFile = $from[0]["file"];
        $fromFun = $from[0]["function"];
        $fromLine = $from[0]["line"];
        $obj = json_encode($args);
        $time = date('Y-m-d H:i:s', ToolTime::getLocalSec());
        $msg = "[" . $time . "] " . "[ERROR]: " . $fromFile . " : " . $fromLine . ": message: " . $str . " result: " . $obj."\n";

        $fileName = dirname(__FILE__).'/../backend/log/' . date('Y-m-d', ToolTime::getLocalSec()) . ".log";
        $file = fopen($fileName, 'a+');
        fwrite($file, $msg, 4096);
        fclose($file);
    }

}
