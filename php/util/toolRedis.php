<?php
/**
 * Created by YX.
 * Date: 2019-03-25
 * Time: 14:05
 */

include_once dirname(__FILE__) . "/../config.php";


class ToolRedis
{
    static $_instance;
    var $_redis;

    private function __construct()
    {
        $this->_redis = new Redis();
        if (!$this->_redis->connect(Config::Redis_Host, Config::Redis_Port))
            throw new Exception(json_decode(array("error" => "服务器忙"), JSON_UNESCAPED_UNICODE));
        if (Config::Redis_Auth != "")
            $this->_redis->auth(Config::Redis_Auth);
        if (Config::$Redis_Db != 0)
            $this->_redis->select(Config::$Redis_Db);
    }

    function __destruct()
    {
        $this->_redis->close();
    }

    /**
     * @return Redis
     */
    static function get()
    {
        if (null == ToolRedis::$_instance)
            ToolRedis::$_instance = new ToolRedis();
        return ToolRedis::$_instance->_redis;
    }

    const LetterToInt = array(
        "a" => 0, "b" => 1, "c" => 2, "d" => 3, "e" => 4,
        "f" => 5, "g" => 6, "h" => 7, "i" => 8, "j" => 9,
        "k" => 10, "l" => 11, "m" => 12, "n" => 13, "o" => 14,
        "p" => 15, "q" => 16, "r" => 17, "s" => 18, "t" => 19,
        "u" => 20, "v" => 21, "w" => 22, "x" => 23, "y" => 24,
        "z" => 25,
        "0" => 0, "1" => 1, "2" => 2, "3" => 3, "4" => 4,
        "5" => 5, "6" => 6, "7" => 7, "8" => 8, "9" => 9,
    );

    /**
     * @param $s
     * @return int
     */
    static function StringToInt($s)
    {
        $s = strtolower($s);
        $strLen = strlen($s);
        $iWorld = 0;
        for ($iS = 0; $iS < $strLen; ++$iS) {
            if (array_key_exists($s[$iS], ToolRedis::LetterToInt)) {
                $iWorld += ToolRedis::LetterToInt[$s[$iS]];
            }
        }
        return $iWorld % 128;
    }
}

class RedisKey
{
    static function getUserInfo($userId)
    {
        return "u:info:h:" . $userId;
    }

    static function getRedisKeyAllUserId($userId)
    {
        return "u:userId:s:" . ToolRedis::StringToInt($userId);
    }
}


class gameConfig
{
    /**
     * 游戏配置在redis的命名
     */
    const AwardConfig = "backend:gameConfig:AwardConfig:";
    const CasinoConfig = "backend:gameConfig:CasinoConfig:";
    const MatchConfig = "backend:gameConfig:MatchConfig:";
    const MatchValidatorConfig = "backend:gameConfig:MatchValidatorConfig:";
    const RaceConfig = "backend:gameConfig:RaceConfig:";
    const FastConfig = "backend:gameConfig:FastConfig:";
}

class ladderConfig
{
    /**
     * 天梯配置
     */
    const LadderAwardConfig = "backend:ladderConfig:LadderAwardConfig:";
    const LadderScoreConfig = "backend:ladderConfig:LadderScoreConfig:";
    const LadderTitleConfig = "backend:ladderConfig:LadderTitleConfig:";
}

class GameLockH
{
    var $_key;
    var $_field;
    var $_release = false;
    var $success = false;

    function __construct($key, $field)
    {
        $this->_key = $key;
        $this->_field = $field;

        $sleepCount = 0;
        while (true) {
            $utcMSec = ToolTime::getUtcMillisecond();
            if (ToolRedis::get()->hSetNx($key, $field, $utcMSec + 3000) == true) {
                $this->success = true;
                break;
            }
            $lockToUtcMSec = (int)ToolRedis::get()->hGet($key, $field);
            if (false === $lockToUtcMSec) {
                if (ToolRedis::get()->hSetNx($key, $field, $utcMSec + 3000) == true) {
                    $this->success = true;
                    break;
                }
                else {
                    sleep(1);
                    $sleepCount++;
                    continue;
                }
            }

            if ($lockToUtcMSec < $utcMSec) {
                $incr = $utcMSec + 3000 - $lockToUtcMSec;
                $newUtcMSec = ToolRedis::get()->hIncrBy($key, $field, $incr);
                if ($newUtcMSec == $utcMSec + 3000) {
                    $this->success = true;
                    break;
                }
                else {
                    ToolRedis::get()->hIncrBy($key, $field, -$incr);
                }

                sleep(1);
                $sleepCount++;
            } else {
                sleep(1);
                $sleepCount++;
            }
            if ($sleepCount > 3) {
                break;
            }
        }
    }

    function __destruct()
    {
        $this->release();
    }

    function release()
    {
        if (!$this->success)
            return;
        if ($this->_release)
            return;
        $this->_release = true;
        ToolRedis::get()->hDel($this->_key, $this->_field);
    }
}
