<?php
include_once dirname(__FILE__) . "/../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../util/toolTime.php";
include_once dirname(__FILE__) . "/../util/toolRedis.php";
include_once dirname(__FILE__) . "/../util/dajialeService.php";

class ToolForGame {
    /**
     * 查询玩家的乐券/奖金/天梯/冠军/
     * @param $player
     * @return mixed
     */
    static function playerInfo ($player) {
        $player['leQuan'] = 0;
        $player['award'] = 0;
        $player['ladder'] = 0;
        $player['champion'] = 0;
        $player['race'] = 0;
        $player['online'] = 0;

        $rid = $player['rid'];
        $userId = $player['open_id'];
        //查询大家乐余额
        $account = DajialeService::getAccountData($player['open_id']);
        //乐券和奖金
        if(sizeof($account) > 0){
            $player['leQuan'] = $account[1]['accountAmount'];
            $player['award'] = $account[2]['accountAmount'];
        }

        //天梯积分
        $tiantiSql = "select ifnull(sum(total_score),0) as ladder from `".Config::HB_SQL_DB."`.`hb_ladder_data` where rid = '$rid'";
        if ($tianResult = ToolMySql::query_gameServer($tiantiSql)) {
            $player['ladder'] = $tianResult->fetch_assoc()['ladder'];
        }

        //平均在线,分钟
        $onlineSql = "select avg(online_sec)/60 as online from ".Config::HB_LOG_SQL_DB.".hb_ci_user_access where rid = '$rid'";
        if ($onlineResult = ToolMySql::query_gameServer($onlineSql)) {
            $player['online'] = round($onlineResult->fetch_assoc()['online'],2);
        }

        //冠军次数
        $prizeSql = "select count(id) as total from `".Config::SQL_DB."`.`consume` where user_id = '$userId' and status =1 and rank =1 ;";
        if ($prizeResult = ToolMySql::query($prizeSql)) {
            $player['champion'] = $prizeResult->fetch_assoc()['total'];
        }
        //比赛次数
        //从自己数据库获取
//        $raceSql = "select count(id) as race from ".Config::SQL_DB.".consume where user_id = '$userId' and status =1";
//        if ($raceResult = ToolMySql::query($raceSql)) {
//            $player['race'] = $raceResult->fetch_assoc()['race'];
//        }
        //从大家乐获取matchSignUpPage
        $player['race'] = DajialeService::matchSignUpPage("","",4,$player['open_id'])['total'];
        return $player;
    }

    /**
     * 工具，将玩家信息从数据库推送到redis
     * @param $userId
     * @return bool|string 正确返回TRUE,错误返回错误信息
     */
    static function checkPlayer2Redis($userId) {
        $checkSql = "select * from ".Config::SQL_DB.".playerusers where id = '$userId'";
        $checkResult = ToolMySql::query($checkSql);
        if (!$checkResult)
            return "从自身服务器查询出错,玩家:'$userId'";

        //去游戏服务器同步数据
        if ($checkResult->num_rows === 0) {
            return "AI玩家:$userId";
            ToolMySql::conn_gameServer();
        }
        $userInfo = $checkResult->fetch_all(MYSQLI_ASSOC)[0];
        $userId = $userInfo['id'];
        DbPlayerInfo::hMSet($userId, $userInfo);
        return true;
    }

}