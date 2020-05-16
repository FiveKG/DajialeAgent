<?php
/**
 * Created by YX.
 * Date: 2019-03-25
 * Time: 15:29
 */

class ToolTime
{
    const OneHourSec = 3600;
    const OneMinSec = 60;
    const OneDaySec = 86400;
    const OneWeekSec = 604800;

    static $offSet = 0;

    static function getUtcMillisecond()
    {
        list($msec, $sec) = explode(' ', microtime());
        return (int)(((float)$msec + (int)$sec) * 1000);
    }

    static function getUtc()
    {
        return time() + ToolTime::$offSet;
    }

    static function getLocalSec()
    {
        return time() + 28800 + ToolTime::$offSet;
    }

    static function getLocalDayEndSec()
    {
        $local = ToolTime::getLocalSec();
        return $local - (int)$local % ToolTime::OneDaySec + ToolTime::OneDaySec;
    }

    /**
     * 获取某一天的结束时间戳
     */
    static function getOneDayEndSec($now)
    {
            return $now+86399;
    }

    static function utcToLocalSec($utc)
    {
        return $utc + 28800;
    }

    static function localSecToUtc($local)
    {
        return $local - 28800;
    }

    static function isSameDay($secA, $secB)
    {
        return floor($secA / ToolTime::OneDaySec) == floor($secB / ToolTime::OneDaySec);
    }

    static function isSameWeek($utcA, $utcB)
    {
        return self::getWeekStartUtc($utcA) == self::getWeekStartUtc($utcB);
    }

    static function isSameMon($utcA, $utcB)
    {
        $dateA = date('Y-m', self::utcToLocalSec($utcA));
        $dateB = date('Y-m', self::utcToLocalSec($utcB));
        return $dateA == $dateB;
    }

    static function week()
    {
        $w = (int)date("w");
        if ($w == 0)
            $w = 7;
        return $w;
    }

    static function getWeek($utc)
    {
        $w = (int)date("w", self::utcToLocalSec($utc));
        if ($w == 0)
            $w = 7;
        return $w;
    }

    /**
     * 每周开始的utc时间，周末开始
     * @param $utc
     */
    static function getWeekStartUtc($utc)
    {
        $localSec = ToolTime::utcToLocalSec($utc);
        $w = (int)date("w", $localSec);
        $localSec = $localSec - $localSec % ToolTime::OneDaySec - $w * ToolTime::OneDaySec;
        return ToolTime::localSecToUtc($localSec);
    }

    static function getMonStartUtc($utc)
    {
        $localSec = ToolTime::utcToLocalSec($utc);
        $d = (int)date("d", $localSec) - 1;
        $localSec = $localSec - $localSec % ToolTime::OneDaySec - $d * ToolTime::OneDaySec;
        return ToolTime::localSecToUtc($localSec);
    }

    static function strToUtc($str)
    {
        return self::localSecToUtc(strtotime($str));
    }

    static function getMonth() {
          return date('Y-m', self::getLocalSec());
    }

    static function getToday() {
        return date('Y-m-d', self::getLocalSec());
    }

    static  function  getYesterday() {
        return date('Y-m-d', (self::getLocalSec()-86400));
    }
    static function getLastMonth() {
        return date('Y-m',strtotime('-1 month'));
    }

    static function isDate($str) {
        if ($stamp = strtotime($str)) {
            if ($stamp <= self::getLocalSec()) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $startDate "2020-02-01"
     * @param $endDate "2020-02-02"
     * @return array
     */
    static function getDateFromRange($startDate, $endDate) {
        $startDate = strtotime($startDate." 00:00:00");
        $endDate = strtotime($endDate. " 23:59:59");

        $dataRange = array($startDate);
        while(!self::isSameDay($startDate, $endDate)) {
            $startDate += 86400;
            $dataRange[] = $startDate;
        }

        for ($i = 0; $i < sizeof($dataRange); $i++) {
            $dataRange[$i] = date("Y-m-d",$dataRange[$i]);
        }
//        var_dump($dataRange);
        return $dataRange;
    }

    /**
     * 获得毫秒级现在时间
     * @return false|float
     */
    static function current_millis() {
        list($usec, $sec) = explode(" ", microtime());
        return round(((float)$usec + (float)$sec) * 1000);
    }
}
//var_dump(ToolTime::today());
//var_dump(date('Y-m',strtotime('-1 month')));
//var_dump(ToolTime::getYestoday());
//ToolTime::getDateFromRange('2020-01-01','2020-02-02');


