<?php
include_once dirname(__FILE__) . "/../../util/toolTime.php";
include_once dirname(__FILE__) . "/../../util/toolRedis.php";
include_once dirname(__FILE__) . "/../../util/toolNet.php";
include_once dirname(__FILE__) . "/../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../player/player.php";
include_once dirname(__FILE__) . "/../../config.php";

class Match {
    /**
     * 创建赛事
     * @param array $data
     * @return bool
     */
    static function createMatch(array $data) {
        //开启事务
        ToolMySql::setAutocommit(false);
        //匹配校验器
        $matchValidatorKey = "";
        $matchValidatorValue = "";
        foreach ($data['matchValidator'] as $keys => $values) {
            $matchValidatorKey = "`$keys`".' ,'."$matchValidatorKey";
            $matchValidatorValue = "'$values'" .' ,'."$matchValidatorValue";
        }
        $matchValidatorKey= substr($matchValidatorKey, 0, -1);
        $matchValidatorValue= substr($matchValidatorValue, 0, -1);
        $insertMatchValidatorSql = "insert into `matchvalidator` ($matchValidatorKey) values ($matchValidatorValue)";

        $insertMatchValidatorResult = ToolMySql::query($insertMatchValidatorSql);
        if (!$insertMatchValidatorResult) {
            ToolMySql::rollback();
            return "创建检验器配置失败";
        }
        $group = mysqli_insert_id(ToolMySql::$_conn);

        //比赛配置
        $match = $data["match"];
        $matchKey = "`group`";
        $matchValue = "'$group'";
        foreach ($match as $keys => $values) {
            $matchKey = "`$keys`".' ,'."$matchKey";
            $matchValue = "'$values'" ." ,"."$matchValue";
        }
        $insertMatchSql = "insert into `match` ($matchKey) values ($matchValue)";
        $insertMatchResult = ToolMySql::query($insertMatchSql);
        if (!$insertMatchResult) {
            ToolMySql::rollback();
            return "创建赛事配置失败";
        }
        $localeId = mysqli_insert_id(ToolMySql::$_conn);

        //娱乐赛配置
        if ($match->category == 1) {
            $enterKey = "`localeId`, `localeName`";
            $enterValue = "'$localeId','$match->localeName'";
            foreach ($data["casino"] as $keys => $values) {
                $enterKey = "`$keys`".' ,'."$enterKey";
                $enterValue = "'$values'" .' ,'."$enterValue";
            }
            $insertCasinoSql = "insert into casino ($enterKey) values ($enterValue)";
            $insertCasinoResult = ToolMySql::query($insertCasinoSql);

            if (!$insertCasinoResult){
                ToolMySql::rollback();
                return "创建娱乐赛失败";
            }
        }
        else if ($match->category == 2) {
            //比赛场配置
            $conKey = "`localeId`, `localeName`";
            $conValue = "'$localeId','$match->localeName'";
            foreach ($data['race'] as $keys => $values) {
                $conKey = "`$keys`".' ,'."$conKey";
                $conValue = "'$values'" .' ,'."$conValue";
            }

            //奖励配置
            $awardKey = '`localeId`';
            $awardValue = "'$localeId'";
            foreach ($data['award'] as $keys => $values) {
                $awardKey = "`$keys`".' ,'."$awardKey";
                $awardValue = "'$values'" .' ,'."$awardValue";
            }
            $insertRaceSql = "insert into race ($conKey) values ($conValue)";
            $insertRaceResult = ToolMySql::query($insertRaceSql);

            if (!$insertRaceResult){
                ToolMySql::rollback();
                return "创建比赛失败";
            }

            $insertAwardSql = "insert into award ($awardKey) values ($awardValue)";
            $insertAwardResult = ToolMySql::query($insertAwardSql);
            if (!$insertAwardResult) {
                ToolMySql::rollback();
                return "创建比赛奖励失败";
            }
        }

        ToolMySql::commit();
        ToolMySql::setAutocommit(true);
        return true;
    }

    /**
     * 返回详细的赛事属性
     * @param $localeId
     * @param $type
     * @return mixed
     */
    static function detailMatch($localeId, $type) {
        switch ($type) {
            case "race" :
                $sql = "select * from ".Config::SQL_DB.".race where localeId = '$localeId'";
                break;
            case "casino" :
                $sql = "select * from ".Config::SQL_DB.".casino where localeId = '$localeId'";
                break;
            case "matchValidator":
                $sql = "select * from ".Config::SQL_DB.".matchValidator where `group` in (select `group` from  ".Config::SQL_DB.".`match` where localeId = '$localeId')";
                break;
            case "award" :
                $sql = "select * from  ".Config::SQL_DB.".award where localeId = '$localeId'";
                break;
            default :
                return false;
                break;
        }
        $result = ToolMySql::query($sql);
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        return $rows[0];
    }
}
