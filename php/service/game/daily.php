<?php
include_once dirname(__FILE__) . "/../../util/toolTime.php";
include_once dirname(__FILE__) . "/../../util/toolRedis.php";
include_once dirname(__FILE__) . "/../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../player/player.php";
include_once dirname(__FILE__) . "/../../config.php";

class Daily {
    static  $data = array("list"=>array(),"total"=>0);
    /**
     * 日报列表,日期有一个为空则默认返回所有数据
     * @param $startDate
     * @param $endDate
     * @param $type {"new_players", "active_players", "charge_players", "game_total"}
     * @param $page
     * @param $limit
     * @return array
     */
    static function getDaily($startDate, $endDate, $type, $page, $limit)
    {
        $start = ($page - 1) * $limit;
        $startStamp = ToolTime::strToUtc($startDate);
        $endStamp = ToolTime::getOneDayEndSec(ToolTime::strToUtc($endDate));

        $timeType = 'when';//按时间查询
        if (!$startDate || !$endDate) {
            $timeType = 'allTime';//查询所有时间
        }


        switch ($type) {
            case "new_players":
                self::getNewPlayer($timeType, $startStamp, $endStamp, $start, $limit);
                break;
            case "active_players":
                self::getActivePlayers($timeType, $startStamp, $endStamp, $start, $limit);
                break;
            case "charge_players":
                self::getChargePlayers($timeType, $startDate, $endDate, $start, $limit);
                break;
            case "game_total":
                self::getGameTotal($timeType, $startDate, $endDate, $start, $limit);
                break;
            default:
                break;
        }
        return self::$data;
    }
    /**
     * 新增玩家查询
     * @param $timeType
     * @param $startStamp
     * @param $endStamp
     * @param $start
     * @param $limit
     */
    static function getNewPlayer($timeType,$startStamp,$endStamp,$start,$limit ) {
        if ($timeType ==='allTime') {
            $playerTotalSql = "	select ifnull(count(id),0) as total,FROM_UNIXTIME(created_time, '%Y-%m-%d')as create_at from ".Config::HB_LOG_SQL_DB.".hb_ci_user
	                                group by FROM_UNIXTIME(created_time, '%Y-%m-%d') order by created_time DESC limit $start,$limit";
            $totalSql = "select ifnull(count(total),0) as total from (select ifnull(count(user_name),0) as total from ".Config::HB_LOG_SQL_DB.".hb_ci_user group by user_name) as temp";
        }else {
            $playerTotalSql = "	select ifnull(count(id),0) as total,FROM_UNIXTIME(created_time, '%Y-%m-%d')as create_at from ".Config::HB_LOG_SQL_DB.".hb_ci_user
	                            where created_time >= '$startStamp' and created_time< '$endStamp' group by FROM_UNIXTIME(created_time, '%Y-%m-%d') order by created_time DESC limit $start,$limit";
            $totalSql = "select ifnull(count(total),0) as total from (
                        select ifnull(count(user_name),0) as total from ".Config::HB_LOG_SQL_DB.".hb_ci_user
                        where created_time >= '$startStamp' and created_time< '$endStamp'  group by user_name) as temp";
        }

        $playerTotalResult = ToolMySql::query_gameServer($playerTotalSql);
        $totalResult = ToolMySql::query_gameServer($totalSql);

        self::$data["total"] = $totalResult->fetch_assoc()['total'];
        while ($row = $playerTotalResult->fetch_assoc()) {
            $list['create_at'] = $row['create_at'];
            $list['oneTotal'] = $row['total'];
            self::$data['list'][] = $list;
        }
    }

    /**
     * 查询活跃玩家
     * @param $timeType
     * @param $startStamp
     * @param $endStamp
     * @param $start
     * @param $limit
     */
    static function getActivePlayers($timeType,$startStamp,$endStamp,$start,$limit ) {
        if ($timeType ==='allTime') {
            $sql = "select old_created_time as `date` ,ifnull(num1,0) as new_player,  ifnull(num2,0) as old_player ,sum(ifnull(num1,0)+ifnull(num2,0) )as oneTotal from
                    (
                    select  sum(num1) as num1,new_created_time from(
                    select count(rid) as num1 , FROM_UNIXTIME(hb_ci_user_access.created_time, '%Y-%m-%d') as new_created_time from ".Config::HB_LOG_SQL_DB.".hb_ci_user_access inner join ".Config::HB_LOG_SQL_DB.".hb_ci_user on
                    FROM_UNIXTIME(hb_ci_user_access.created_time, '%Y-%m-%d') = FROM_UNIXTIME(hb_ci_user.created_time,'%Y-%m-%d') and hb_ci_user_access.rid = hb_ci_user.role_id group by rid ) as temp1 group by new_created_time) as temp1
                    right join 
                    (
                    select  sum(num2) as num2,old_created_time from(
                    select  count(rid) as num2,FROM_UNIXTIME(hb_ci_user_access.created_time, '%Y-%m-%d') as old_created_time from ".Config::HB_LOG_SQL_DB.".hb_ci_user_access inner join ".Config::HB_LOG_SQL_DB.".hb_ci_user on
                    FROM_UNIXTIME(hb_ci_user_access.created_time, '%Y-%m-%d') <> FROM_UNIXTIME(hb_ci_user.created_time,'%Y-%m-%d') and hb_ci_user_access.rid = hb_ci_user.role_id group by rid ) as temp2 group by old_created_time ) as temp2
                    on temp1.new_created_time = temp2.old_created_time group by temp2.old_created_time order by temp2.old_created_time DESC limit $start,$limit;";

            $totalSql = "select ifnull(count(*),0) as total from(
                            select old_created_time as `date` ,ifnull(num1,0) as new_player,  ifnull(num2,0) as old_player ,sum(ifnull(num1,0)+ifnull(num2,0) )as oneTotal from
                            (
                            select  sum(num1) as num1,new_created_time from(
                            select count(rid) as num1 , FROM_UNIXTIME(hb_ci_user_access.created_time, '%Y-%m-%d') as new_created_time from ".Config::HB_LOG_SQL_DB.".hb_ci_user_access inner join ".Config::HB_LOG_SQL_DB.".hb_ci_user on
                            FROM_UNIXTIME(hb_ci_user_access.created_time, '%Y-%m-%d') = FROM_UNIXTIME(hb_ci_user.created_time,'%Y-%m-%d') and hb_ci_user_access.rid = hb_ci_user.role_id group by rid ) as temp1 group by new_created_time) as temp1
                            right join 
                            (
                            select  sum(num2) as num2,old_created_time from(
                            select  count(rid) as num2,FROM_UNIXTIME(hb_ci_user_access.created_time, '%Y-%m-%d') as old_created_time from ".Config::HB_LOG_SQL_DB.".hb_ci_user_access inner join ".Config::HB_LOG_SQL_DB.".hb_ci_user on
                            FROM_UNIXTIME(hb_ci_user_access.created_time, '%Y-%m-%d') <> FROM_UNIXTIME(hb_ci_user.created_time,'%Y-%m-%d') and hb_ci_user_access.rid = hb_ci_user.role_id group by rid ) as temp2 group by old_created_time ) as temp2
                            on temp1.new_created_time = temp2.old_created_time group by temp2.old_created_time) as temp";
        }else {
            $sql = "select old_created_time as `date` ,ifnull(num1,0) as new_player,  ifnull(num2,0) as old_player ,sum(ifnull(num1,0)+ifnull(num2,0) )as oneTotal from
                    (
                    select  sum(num1) as num1,new_created_time from(
                    select count(rid) as num1 , FROM_UNIXTIME(hb_ci_user_access.created_time, '%Y-%m-%d') as new_created_time from ".Config::HB_LOG_SQL_DB.".hb_ci_user_access inner join ".Config::HB_LOG_SQL_DB.".hb_ci_user on
                    FROM_UNIXTIME(hb_ci_user_access.created_time, '%Y-%m-%d') = FROM_UNIXTIME(hb_ci_user.created_time,'%Y-%m-%d')  and hb_ci_user_access.rid = hb_ci_user.role_id
                    where hb_ci_user_access.created_time >='$startStamp' and hb_ci_user_access.created_time<'$endStamp' group by rid ) as temp1 group by new_created_time) as temp1
                    right join 
                    (
                    select  sum(num2) as num2,old_created_time from(
                    select  count(rid) as num2,FROM_UNIXTIME(hb_ci_user_access.created_time, '%Y-%m-%d') as old_created_time from ".Config::HB_LOG_SQL_DB.".hb_ci_user_access inner join ".Config::HB_LOG_SQL_DB.".hb_ci_user on
                    FROM_UNIXTIME(hb_ci_user_access.created_time, '%Y-%m-%d') <> FROM_UNIXTIME(hb_ci_user.created_time,'%Y-%m-%d') and hb_ci_user_access.rid = hb_ci_user.role_id
                    where hb_ci_user_access.created_time >='$startStamp' and hb_ci_user_access.created_time<'$endStamp' group by rid ) as temp2 group by old_created_time ) as temp2
                    on temp1.new_created_time = temp2.old_created_time group by temp2.old_created_time order by temp2.old_created_time DESC limit $start,$limit;";

            $totalSql = "	select ifnull(count(*),0) as total from(
                            select old_created_time as `date` ,ifnull(num1,0) as new_player,  ifnull(num2,0) as old_player ,sum(ifnull(num1,0)+ifnull(num2,0) )as oneTotal from
                            (
                            select  sum(num1) as num1,new_created_time from(
                            select count(rid) as num1 , FROM_UNIXTIME(hb_ci_user_access.created_time, '%Y-%m-%d') as new_created_time from ".Config::HB_LOG_SQL_DB.".hb_ci_user_access inner join ".Config::HB_LOG_SQL_DB.".hb_ci_user on
                            FROM_UNIXTIME(hb_ci_user_access.created_time, '%Y-%m-%d') = FROM_UNIXTIME(hb_ci_user.created_time,'%Y-%m-%d') and hb_ci_user_access.rid = hb_ci_user.role_id
                            where hb_ci_user_access.created_time >='$startStamp' and hb_ci_user_access.created_time<'$endStamp' group by rid ) as temp1 group by new_created_time) as temp1
                            right join 
                            (
                            select  sum(num2) as num2,old_created_time from(
                            select  count(rid) as num2,FROM_UNIXTIME(hb_ci_user_access.created_time, '%Y-%m-%d') as old_created_time from ".Config::HB_LOG_SQL_DB.".hb_ci_user_access inner join ".Config::HB_LOG_SQL_DB.".hb_ci_user on
                            FROM_UNIXTIME(hb_ci_user_access.created_time, '%Y-%m-%d') <> FROM_UNIXTIME(hb_ci_user.created_time,'%Y-%m-%d') and hb_ci_user_access.rid = hb_ci_user.role_id
                            where hb_ci_user_access.created_time >='$startStamp' and hb_ci_user_access.created_time<'$endStamp' group by rid ) as temp2 group by old_created_time ) as temp2
                            on temp1.new_created_time = temp2.old_created_time group by temp2.old_created_time) as temp";
        }

        $playerTotalResult = ToolMySql::query_gameServer($sql);
        $totalResult = ToolMySql::query_gameServer($totalSql);

        $playerTotal = $playerTotalResult->fetch_all(MYSQLI_ASSOC);
        $total = $totalResult->fetch_assoc()['total'];
        self::$data['list'] = $playerTotal;
        self::$data['total'] = $total;
    }

    /**
     * 查询充值玩家
     * @param $timeType
     * @param $startDate
     * @param $endDate
     * @param $start
     * @param $limit
     * @return array
     */
    static function  getChargePlayers($timeType,$startDate,$endDate,$start,$limit ) {
        //充值信息不从游戏数据库拿，从后台数据库拿
        if($timeType ==='allTime')
            $chargeInfoSql = "select user_id,DATE_FORMAT( create_at, \"%Y-%m-%d\" ) as create_at from ".Config::SQL_DB.".charge  group by DATE_FORMAT( create_at, \"%Y-%m-%d\" ),user_id";
        else{
            $startDate = $startDate.' 00:00:00';
            $endDate = $endDate.' 23:59:59';
            $chargeInfoSql = "select user_id,DATE_FORMAT( create_at, \"%Y-%m-%d\" ) as create_at from ".Config::SQL_DB.".charge where create_at > '$startDate' and create_at < '$endDate' group by DATE_FORMAT( create_at, \"%Y-%m-%d\" ),user_id";
        }

        $chargeInfoResult = ToolMySql::query($chargeInfoSql);
        if(!$chargeInfoResult)
            return self::$data;
        $chargeInfoList = $chargeInfoResult->fetch_all(MYSQLI_ASSOC);

        $resultList = array();
        $resultList2 = array();
        foreach ($chargeInfoList as $chargeInfo) {
            $create_time = $chargeInfo['create_at'];
            $user_id = $chargeInfo['user_id'];
            if(array_key_exists($create_time, $resultList2)) {
                array_push($resultList2["$create_time"], $user_id);
            }else {
                $resultList2["$create_time"] = array($user_id);
            }
        }

        //连接游戏数据库查询玩家
        foreach ($resultList2 as $date=>$idList) {
            $useridList = "";
            foreach ($idList as $userid) {
                $useridList  = $useridList."'$userid',";
            }
            $useridList = substr($useridList,0,strlen($useridList)-1);
            $findNewPlayerChargeNumSql = "select ifnull(count(user_name),0) as new_player from (select * from ".Config::HB_LOG_SQL_DB.".hb_ci_user where user_name in( $useridList)
                                        )as temp where FROM_UNIXTIME(created_time,'%Y-%m-%d')='$date'";
            $newPlayer = 0;
            $newPlayerResult = ToolMySql::query_gameServer($findNewPlayerChargeNumSql);
            if($newPlayerResult)
                $newPlayer = $newPlayerResult->fetch_assoc()['new_player'];

            $resultList['date'] = $date;
            $resultList['new_player'] = $newPlayer;
            $resultList['oneTotal'] = sizeof($idList);
            $resultList['old_player'] = $resultList['oneTotal'] - $newPlayer;
            self::$data['list'][] = $resultList;
        }
        self::$data['total'] = sizeof($resultList2);
        self::$data['list'] = array_reverse(self::$data['list']);//倒序
        self::$data['list'] = array_slice(self::$data['list'],$start, $limit);
    }


    /**
     * 游戏场次
     * @param $timeType
     * @param $startDate
     * @param $endDate
     * @param $start
     * @param $limit
     */
    static function getGameTotal($timeType,$startDate,$endDate,$start,$limit ) {
        //数据过大，默认选择时间为最近3天
        if($timeType ==='allTime') {
            $endDate = ToolTime::getToday();
            $startDate = date('Y-m-d', (ToolTime::getLocalSec()-86400*2));
        }
        $dateRange = ToolTime::getDateFromRange($startDate, $endDate);
        self::$data['total'] = sizeof($dateRange);
        $startDate = $startDate.' 00:00:00';
        $endDate = $endDate.' 23:59:59';

        $sql = "select count(localeId) as total,localeId,DATE_FORMAT( create_at, \"%Y-%m-%d\" ) as create_at from(
                    select create_at, localeId from ".Config::SQL_DB.".consume where localeId <> '' and status <> 0 and create_at >='$startDate'  and create_at < '$endDate'
                    ) as temp group by DATE_FORMAT( create_at, \"%Y-%m-%d\" ),localeId ";

        $locateIdListResult = ToolMySql::query($sql);
        $locateIdList = $locateIdListResult->fetch_all(MYSQLI_ASSOC);

        for($i = 0; $i < sizeof($dateRange); $i++) {
            $oneDay = array("date"=>$dateRange[$i], "1"=>0, "2"=>0, "3"=>0, "4"=>0, "5"=>0);
            for($j = 0; $j < sizeof($locateIdList); $j++) {
                if($locateIdList[$j]['create_at'] === $dateRange[$i]) {
                    $raceConfig = json_decode(ToolRedis::get()->hGet(gameConfig::RaceConfig, $locateIdList[$j]['localeId']),true);
                    if(!$raceConfig)
                        continue;
                    $category = $raceConfig['category'];
                    $oneDay["$category"] += $locateIdList[$j]['total'];
                }
            }
            self::$data['list'][] = $oneDay;
        }
        self::$data['list'] = array_reverse(self::$data['list']);//倒序
        self::$data['list'] = array_slice(self::$data['list'],$start, $limit);
    }
}