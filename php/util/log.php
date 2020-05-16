<?php
/**
 * Created by YX.
 * Date: 2019-05-11
 * Time: 12:04
 */

class Log
{
    static $level = 15;
    public static function DEBUG($msg)
    {
        self::write(1, $msg);
    }

    public static function WARN($msg)
    {
        self::write(4, $msg);
    }

    public static function ERROR($msg)
    {
        $debugInfo = debug_backtrace();
        $stack = "[";
        foreach($debugInfo as $key => $val){
            if(array_key_exists("file", $val)){
                $stack .= ",file:" . $val["file"];
            }
            if(array_key_exists("line", $val)){
                $stack .= ",line:" . $val["line"];
            }
            if(array_key_exists("function", $val)){
                $stack .= ",function:" . $val["function"];
            }
        }
        $stack .= "]";
        self::write(8, $stack . $msg);
    }

    public static function INFO($msg)
    {
        self::write(2, $msg);
    }

    private static function getLevelStr($level)
    {
        switch ($level)
        {
            case 1:
                return 'debug';
                break;
            case 2:
                return 'info';
                break;
            case 4:
                return 'warn';
                break;
            case 8:
                return 'error';
                break;
            default:

        }
    }

    protected static function write_str($msg){

        $fileName = "../log/".date('Y-m-d').".log";
        $file = fopen($fileName,'a+');
        fwrite($file, $msg, 4096);
        fclose($file);
//        $size = filesize(self::$fileName);
    }

    protected static function write($level,$msg)
    {
        if(($level & self::$level) == $level )
        {
            $msg = '['.date('Y-m-d H:i:s').']['.self::getLevelStr($level).'] '.$msg."\n";
            self::write_str($msg);
        }

    }
}

class LogToRedis
{
    public static function log($type, $msg)
    {
        ToolRedis::get()->lPush("backend:log:l:" . $type,
            '[' . date('Y-m-d H:i:s', ToolTime::getLocalSec()). ']' . $msg);
    }
}
