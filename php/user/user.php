<?php
/**
 * Created by YX.
 * Date: 2019-03-25
 * Time: 14:18
 */

include_once dirname(__FILE__) . "/../util/toolRedis.php";
include_once dirname(__FILE__) . "/../util/toolTime.php";
include_once dirname(__FILE__) . "/../util/util.php";

class DbUserInfo
{
    const Field_UserId = "userId";
    const Field_Create_Utc = "create_utc";
    const Field_LastLogin_Utc = "last_login_utc";
    const Field_IntoGame_Utc = "last_into_game_utc";
    const Field_UserHour = "use_hour";
    const Field_IntoGameCount = "into_count";

    /**
     * @param $userId
     * @return string
     */
    static function getRedisKey($userId)
    {
        return RedisKey::getUserInfo($userId);
    }

    static function hMGet($userId, $arrayField)
    {
        return ToolRedis::get()->hMGet(
            self::getRedisKey($userId),
            $arrayField
        );
    }

    static function hGet($userId, $field)
    {
        return ToolRedis::get()->hGet(self::getRedisKey($userId), $field);
    }

    static function hMSet($userId, $arrayField)
    {
        return ToolRedis::get()->hMSet(
            self::getRedisKey($userId),
            $arrayField
        );
    }

    static function hSet($userId, $field, $value)
    {
        ToolRedis::get()->hSet(
            DbUserInfo::getRedisKey($userId)
            , $field, $value
        );
    }

    static function getFieldJson($userId, $field){
        $value = self::hGet($userId, $field);
        if($value !== false && $value !== "")
        {
            return json_decode($value);
        }
        return null;
    }

    static function incrUserUseMinute($userId)
    {
        ToolRedis::get()->hIncrByFloat(RedisKey::getUserInfo($userId), DbUserInfo::Field_UserHour, 0.017);
    }

    static function incrUserIntoGameCount($userId)
    {
        ToolRedis::get()->hIncrBy(RedisKey::getUserInfo($userId), DbUserInfo::Field_IntoGameCount, 1);
    }
}

