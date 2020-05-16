<?php
/**
 * Created by YX.
 * Date: 2019-03-25
 * Time: 14:18
 */

include_once dirname(__FILE__) . "/../util/toolRedis.php";
include_once dirname(__FILE__) . "/../util/toolTime.php";
include_once dirname(__FILE__) . "/../util/util.php";

class Player
{
    var $userId;
    var $playerId;
    var $nickname;
    var $head;
    var $sex;
    var $token;
    var $newCharge;
    var $leDou;
    var $lastGetLeDouTime;

    static function existsUserId($userId)
    {
        return DbUserId::exists($userId);
    }
}

class DbUserId
{
    /**
     * u:userId:k:{$userId}
     * @param $userId
     * @return string
     */
    static function getRedisKey($userId)
    {
        return "u:userId:k:" . $userId;
    }

    static function getRedisSdkUserIdKey($sdkUserId)
    {
        return "u:sdkUserId:k:" . $sdkUserId;
    }

    static function getRedisKeyAllUserId($userId)
    {
        return "u:userId:s:" . ToolRedis::StringToInt($userId);
    }

    static function getRedisKeyAllPlayerId($playerId)
    {
        return "u:playerId:s:" . ToolRedis::StringToInt($playerId);
    }

    static function getRedisKeyAllSdkUserId($sdkUserId)
    {
        return "u:sdkUserId:s:" . ToolRedis::StringToInt($sdkUserId);
    }

    static function exists($userId)
    {
        return ToolRedis::get()->exists(self::getRedisKey($userId));
    }

    static function get($userId)
    {
        return ToolRedis::get()->get(self::getRedisKey($userId));
    }

    static function save($userId, $playerId, $sdkUserId)
    {
        ToolRedis::get()->set(self::getRedisKey($userId), $playerId);
        if ("" != $sdkUserId)
            ToolRedis::get()->set(self::getRedisSdkUserIdKey($sdkUserId), $playerId);
        ToolRedis::get()->sAdd(self::getRedisKeyAllUserId($userId), $userId);
        ToolRedis::get()->sAdd(self::getRedisKeyAllPlayerId($playerId), $playerId);
        ToolRedis::get()->sAdd(self::getRedisKeyAllSdkUserId($userId), $sdkUserId);
    }

    static function delUser($userId)
    {
        $playerId = DbUserId::get($userId);
        ToolRedis::get()->del(
            DbUserId::getRedisKey($userId),
            DbPlayerInfo::getRedisKey($playerId)
        );
    }

    static function delPlayer($playerId)
    {
        ToolRedis::get()->del(
            DbPlayerInfo::getRedisKey($playerId)
        );
    }
}

class DbPlayerInfo
{
    const Field_UserId = "userId";
    const Field_Create_Utc = "create_utc";
    const Field_LastLogin_Utc = "last_login_utc";
    const Field_IntoGame_Utc = "last_into_game_utc";

    /**
     * u:info:h:{$playerId}
     *      userId, nickname, token, gold
     * @param $playerId
     * @return string
     */
    static function getRedisKey($playerId)
    {
        return "u:info:h:" . $playerId;
    }

    static function hMGet($playerId, $arrayField)
    {
        return ToolRedis::get()->hMGet(
            self::getRedisKey($playerId),
            $arrayField
        );
    }

    static function hGet($playerId, $field)
    {
        return ToolRedis::get()->hGet(self::getRedisKey($playerId), $field);
    }

    static function hMSet($playerId, $arrayField)
    {
        return ToolRedis::get()->hMSet(
            self::getRedisKey($playerId),
            $arrayField
        );
    }

    static function hSet($playerId, $field, $value)
    {
        ToolRedis::get()->hSet(
            DbPlayerInfo::getRedisKey($playerId)
            , $field, $value
        );
    }

    static function getFieldJson($playerId,$field){
        $value = self::hGet($playerId, $field);
        if($value !== false && $value !== "")
        {
            return json_decode($value);
        }
        return null;
    }
}
