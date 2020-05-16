<?php
include_once dirname(__FILE__) . "/../util/toolTime.php";
include_once dirname(__FILE__) . "/../util/toolRedis.php";
include_once dirname(__FILE__) . "/../util/toolNet.php";
include_once dirname(__FILE__) . "/../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../player/player.php";
include_once dirname(__FILE__) . "/../config.php";
include_once dirname(__FILE__) . "/../util/logger.php";
include_once dirname(__FILE__) . "/../util/dajialeService.php";
include_once dirname(__FILE__) . "/../util/register2Agent.php";


class Game {
    static $matchType = array('1'=>'快速赛','2'=>'晋级赛','3'=>'定时赛','4'=>'大奖赛','5'=>'好友赛');
    /** 游戏概况
     * @param $date
     * @return array
     */
    static function getSummary($date) {
        $data = array('current'=>0, 'average'=>0, 'max'=>0, 'timeList'=>array());
        $dayStart = ToolTime::strToUtc($date);
        $dayEnd = ToolTime::getOneDayEndSec($dayStart);

        //如果是今天，则取最新时间戳
        if (ToolTime::getToday()==$date) {
            $dayEnd = ToolTime::getLocalSec();
            //当前在线人数
            $currentSql = "select online_num, created_time from ".Config::HB_LOG_SQL_DB.".hb_ci_user_online where  created_time = (select max(created_time) from ".Config::HB_LOG_SQL_DB.".hb_ci_user_online)";
            $currentResult = ToolMySql::query_gameServer($currentSql);
            $data['current'] = $currentResult->fetch_assoc()['online_num'];
        }

        $sql = "select online_num,created_time from ".Config::HB_LOG_SQL_DB.".hb_ci_user_online where created_time >='$dayStart' and created_time <= '$dayEnd'";
        $result = ToolMySql::query_gameServer($sql);
        if (!$result->num_rows)
            return  $data;

        $list = $result->fetch_all(MYSQLI_ASSOC);
        $count = 0;

        $timeList = array_fill(0,24, 0);


        foreach ($list as $value) {
            //最大值
            if ($data['max'] < $value['online_num'])
                $data['max'] = $value['online_num'];
            $count += $value['online_num'];

            //计算每个时间段的平均值
            if ( $value['created_time'] >= $dayStart && $value['created_time'] < $dayStart+1*60*60)
                //0点以内
                $timeList[0] += $value['online_num'];
            if ( $value['created_time'] >= $dayStart+1*60*60 && $value['created_time'] < $dayStart+2*60*60)
                //1点以内
                $timeList[1] += $value['online_num'];
            if ( $value['created_time'] >= $dayStart+2*60*60 && $value['created_time'] < $dayStart+3*60*60)
                //2点以内
                $timeList[2] += $value['online_num'];
            if ( $value['created_time'] >= $dayStart+3*60*60 && $value['created_time'] < $dayStart+4*60*60)
            //3点以内
                $timeList[3] += $value['online_num'];
            if ( $value['created_time'] >= $dayStart+4*60*60 && $value['created_time'] < $dayStart+5*60*60)
                //4点以内
                $timeList[4] += $value['online_num'];
            if ( $value['created_time'] >= $dayStart+5*60*60 && $value['created_time'] < $dayStart+6*60*60)
                //5点以内
                $timeList[5] += $value['online_num'];
            if ( $value['created_time'] >= $dayStart+6*60*60 && $value['created_time'] < $dayStart+7*60*60)
                //6点以内
                $timeList[6] += $value['online_num'];
            if ( $value['created_time'] >= $dayStart+7*60*60 && $value['created_time'] < $dayStart+8*60*60)
                //7点以内
                $timeList[7] += $value['online_num'];
            if ( $value['created_time'] >= $dayStart+8*60*60 && $value['created_time'] < $dayStart+9*60*60)
                //8点以内
                $timeList[8] += $value['online_num'];
            if ( $value['created_time'] >= $dayStart+9*60*60&& $value['created_time'] < $dayStart+10*60*60)
                //9点以内
                $timeList[9] += $value['online_num'];
            if ( $value['created_time'] >= $dayStart+10*60*60 && $value['created_time'] < $dayStart+11*60*60)
                //10点以内
                $timeList[10] += $value['online_num'];
            if ( $value['created_time'] >= $dayStart+11*60*60 && $value['created_time'] < $dayStart+12*60*60)
                //11点以内
                $timeList[11] += $value['online_num'];
            if ( $value['created_time'] >= $dayStart+12*60*60 && $value['created_time'] < $dayStart+13*60*60)
                //12点以内
                $timeList[12] += $value['online_num'];
            if ( $value['created_time'] >= $dayStart+13*60*60 && $value['created_time'] < $dayStart+14*60*60)
                //13点以内
                $timeList[13] += $value['online_num'];
            if ( $value['created_time'] >= $dayStart+14*60*60 && $value['created_time'] < $dayStart+15*60*60)
                //14点以内
                $timeList[14] += $value['online_num'];
            if ( $value['created_time'] >= $dayStart+15*60*60 && $value['created_time'] < $dayStart+16*60*60)
                //15点以内
                $timeList[15] += $value['online_num'];
            if ( $value['created_time'] >= $dayStart+16*60*60 && $value['created_time'] < $dayStart+17*60*60)
                //16点以内
                $timeList[16] += $value['online_num'];
            if ( $value['created_time'] >= $dayStart+17*60*60 && $value['created_time'] < $dayStart+18*60*60)
                //17点以内
                $timeList[17] += $value['online_num'];
            if ( $value['created_time'] >= $dayStart+18*60*60 &&$value['created_time'] < $dayStart+19*60*60)
                //18点以内
                $timeList[18] += $value['online_num'];
            if ( $value['created_time'] >= $dayStart+19*60*60 && $value['created_time'] < $dayStart+20*60*60)
                //19点以内
                $timeList[19] += $value['online_num'];
            if ( $value['created_time'] >= $dayStart+20*60*60 && $value['created_time'] < $dayStart+21*60*60)
                //20点以内
                $timeList[20] += $value['online_num'];
            if ( $value['created_time'] >= $dayStart+21*60*60 && $value['created_time'] < $dayStart+22*60*60)
                //21点以内
                $timeList[21] += $value['online_num'];
            if ( $value['created_time'] >= $dayStart+22*60*60 && $value['created_time'] < $dayStart+23*60*60)
                //22点以内
                $timeList[22] += $value['online_num'];
            if ( $value['created_time'] >= $dayStart+23*60*60 && $value['created_time'] < $dayStart+24*60*60)
                //23点以内
                $timeList[23] += $value['online_num'];
        }

        if(ToolTime::getToday()==$date) {
            $hourAndSecond = date('H:i', ToolTime::getLocalSec());
            $hourAndSecondArray = explode(':', $hourAndSecond);
            $hour = (int)$hourAndSecondArray[0];
            $second = (int)$hourAndSecondArray[1];

            //如果是当天，当前时间点的平均数除数为当前分钟数/5，这个时间点以前的统一为60/5。(数据库5分钟统计一次,一小时统计12次)
            for ($i = 0 ; $i < sizeof($timeList); $i++) {
                if($i === $hour){
                    $timeList[$i] = ceil($timeList[$i]/floor($second/5));
                    break;
                }
                $timeList[$i] =  ceil($timeList[$i]/(60/5));

            }
        }
        else {
            for ($i = 0 ; $i < sizeof($timeList); $i++)
                $timeList[$i] =  ceil($timeList[$i]/(60/5));
        }

        $data['timeList'] = $timeList;
        $data['average'] = ceil($count/sizeof($list));

        return $data;
    }


    /**
     * 日报列表,日期有一个为空则默认返回所有数据
     * @param $startDate
     * @param $endDate
     * @param $type {"new_players", "active_players", "charge_players", "game_total"}
     * @param $page
     * @param $limit
     * @return array
     */
    static function getDaily($startDate, $endDate, $type, $page, $limit) {
        $start = ($page-1)*$limit;
        $data = array("list"=>array(),"total"=>0);
        $list = array();
        $startStamp = ToolTime::strToUtc($startDate);
        $endStamp = ToolTime::getOneDayEndSec(ToolTime::strToUtc($endDate));

        //新增玩家
        if ($type === "new_players") {
            if (!$startDate || !$endDate) {
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

            $data["total"] = $totalResult->fetch_assoc()['total'];
            while ($row = $playerTotalResult->fetch_assoc()) {
                $list['create_at'] = $row['create_at'];
                $list['oneTotal'] = $row['total'];
                array_push($data['list'], $list);
            }
        }

        //活跃玩家
        if ($type === "active_players") {
            if (!$startDate || !$endDate) {
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
            $data['list'] = $playerTotal;
            $data['total'] = $total;
        }

        //充值玩家
        if ($type === "charge_players") {
            //充值信息不从游戏数据库拿，从后台数据库拿
            if(!$startDate|| !$endDate)
                $chargeInfoSql = "select user_id,DATE_FORMAT( create_at, \"%Y-%m-%d\" ) as create_at from ".Config::SQL_DB.".charge  group by DATE_FORMAT( create_at, \"%Y-%m-%d\" ),user_id";
            else{
                $startDate = $startDate.' 00:00:00';
                $endDate = $endDate.' 23:59:59';
                $chargeInfoSql = "select user_id,DATE_FORMAT( create_at, \"%Y-%m-%d\" ) as create_at from ".Config::SQL_DB.".charge where create_at > '$startDate' and create_at < '$endDate' group by DATE_FORMAT( create_at, \"%Y-%m-%d\" ),user_id";
            }

            $chargeInfoResult = ToolMySql::query($chargeInfoSql);
            if(!$chargeInfoResult)
                return $data;
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
                $resultList['old_player'] = $resultList['oneTotal']- $newPlayer;
                array_push($data['list'], $resultList);
            }
            $data['total'] = sizeof($resultList2);
        }

        //总场次
        if ($type === "game_total") {
            ToolMySql::close();
            ToolMySql::conn();
            //数据过大，默认选择时间为最近3天
            if(!$startDate|| !$endDate) {
                $endDate = ToolTime::getToday();
                $startDate = date('Y-m-d', (ToolTime::getLocalSec()-86400*2));
            }
            $dateRange = ToolTime::getDateFromRange($startDate, $endDate);
            $data['total'] = sizeof($dateRange);
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
                $data['list'][] = $oneDay;
            }
            $data['list'] = array_slice($data['list'],$start, $limit);
        }
        $data['total'] = (int)$data['total'];
        return $data;
    }

    /**
     * 查询一个玩家信息
     * @param $rid
     * @return array
     */
    static function  getPlayer($rid) {
        $data = array("total"=>0, 'list'=>array());
            $checkPlayerSql = "	SELECT `rid`,`open_id`,`nick`,`mobile_phone` FROM `".Config::HB_SQL_DB."`.`hb_role` where  rid = '$rid'";

        $checkPlayerResult = ToolMySql::query_gameServer($checkPlayerSql);
        if (!$checkPlayerResult )
            return $data;
        $data['total'] = $checkPlayerResult->num_rows;
        if (!$data['total'])
            return $data;
        $player = $checkPlayerResult->fetch_all(MYSQLI_ASSOC);
        $data['list'][] = self::playerInfo($player[0]);
        $data['total'] = (int)$data['total'];
        return $data;
    }

    /**
     * 返回所有选手列表
     * @param $tel
     * @param $page
     * @param $limit
     * @return array
     */
    static function getAllPlayers( $page, $limit) {
        $data = array("total"=>0, 'list'=>array());

        $start = ($page-1)*$limit;
        $checkPlayerSql = "	SELECT `rid`,`open_id`,`nick`,`mobile_phone`
	                        FROM `".Config::HB_SQL_DB."`.`hb_role` where ai=0 && wxunionid <>''
	                        ORDER BY `rid` DESC LIMIT $start,$limit";
        $totalSql = "select count(rid) as total from `".Config::HB_SQL_DB."`.`hb_role` where ai=0 && wxunionid <>''";

        $checkPlayerResult = ToolMySql::query_gameServer($checkPlayerSql);
        $totalResult = ToolMySql::query_gameServer($totalSql);
        $data['total'] = $totalResult->fetch_assoc()['total'];
        $players = $checkPlayerResult->fetch_all(MYSQLI_ASSOC);

        foreach ($players as $player) {
            $data['list'][] = self::playerInfo($player);
        }
        $data['total'] = (int)$data['total'];
        return $data;
    }

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
        $player['leQuan'] = $account[1]['accountAmount'];
        $player['award'] = $account[2]['accountAmount'];


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
     * 获取玩家充值信息
     * @param $userId
     * @param $page
     * @param $limit
     * @return array|bool
     */
    static function getChargeInfo($userId, $startDate, $endDate, $page, $limit) {
        $data = array('total'=>0, 'list'=>array());
        if ($startDate)
            $startDate = ToolTime::utcToLocalSec(ToolTime::strToUtc($startDate)) * 1000;
        else
            $startDate = '' ;
        if ($endDate)
            $endDate = ToolTime::getOneDayEndSec(ToolTime::utcToLocalSec(ToolTime::strToUtc($endDate))) *1000;
        else
            $endDate = '';
        $result = DajialeService::rechargePage($startDate, $endDate, $page, $limit, $userId);
        if ($result === false)
            return false;
        $data['total'] = $result["total"];
        foreach ($result['records'] as $info) {
            $temp = array();
            $temp['createTime'] = date('Y-m-d H:i', $info['createTime']/1000);
            $temp['amount'] = $info['amount'];
            $temp['subject'] = $info['subject'];
            $temp['status'] = DajialeService::$statusType[$info['status']];
            $temp['mode'] = DajialeService::$modeType[$info['mode']];
            $data["list"][] = $temp;
        }
        $data['total'] = (int)$data['total'];
        return $data;
    }

    /**
     * 玩家提现信息
     * @param $userId
     * @param $startDate
     * @param $endDate
     * @param $page
     * @param $limit
     * @return array|bool
     */
    static function getCaseInfo($userId, $startDate, $endDate, $page, $limit) {
        $data = array('total'=>0, 'list'=>array());
        if ($startDate)
            $startDate = ToolTime::utcToLocalSec(ToolTime::strToUtc($startDate)) * 1000;
        else
            $startDate = '' ;
        if ($endDate)
            $endDate = ToolTime::getOneDayEndSec(ToolTime::utcToLocalSec(ToolTime::strToUtc($endDate))) *1000;
        else
            $endDate = '';

        $result = DajialeService::cashPage($userId, $startDate, $endDate, $page, $limit);
        if ($result === false)
            return false;
        $data['total'] = $result['total'];
        foreach ($result['records'] as $info) {
            $temp = array();
            $temp['createTime'] = date('Y-m-d H:i', $info['createTime']/1000);
            $temp['tradeNo'] = $info['tradeNo'];
            $temp['amount'] = $info['amount'];
            $temp['status'] = DajialeService::$caseStatusType[$info['status']];
            $data["list"][] = $temp;
        }
        $data['total'] = (int)$data['total'];
        return $data;
    }

    /**
     * 玩家的参赛信息
     * @param $userId
     * @param $startDate
     * @param $endDate
     * @param $page
     * @param $limit
     * @return array
     */
    static function getJoinInfo($userId, $startDate, $endDate, $page, $limit) {
        $start = ($page-1)*$limit;
        $data = array('total'=>0,'consume_total'=>0,"award_total"=>0,"ladder_total"=>0,"1"=>0,"2"=>0,"3"=>0,"4"=>0,"5"=>0,'list'=>array());

        $where = "";
        if ($startDate && $endDate)
            $where = "and create_at >='$startDate 00:00:00' and create_at < '$endDate 23:59:59'";

        $sql = "select matchid,consume_amount,create_at,localeId,rank,score,ladder
                from ".Config::SQL_DB.".consume where user_id = '$userId' $where and status = 1 order by create_at DESC limit $start,$limit ;";

        $totalSql = "select count(*) as total
                     from ".Config::SQL_DB.".consume where user_id = '$userId' $where and status = 1 order by create_at ";

        $raceTotal = "select localeId, count(*) as total from consume where user_id = '$userId' and status = 1 group by localeId";

        $result = ToolMySql::query($sql);
        $totalResult = ToolMySql::query($totalSql);
        if ($totalResult)
            $data['total'] = $totalResult->fetch_assoc()['total'];
        $raceTotalResult = ToolMySql::query($raceTotal);
        if ($raceTotalResult) {//统计各类赛事次数
            $row = $raceTotalResult->fetch_all(MYSQLI_ASSOC);

            foreach ($row as $totalInfo) {
                $raceConfig = json_decode(ToolRedis::get()->hGet(gameConfig::RaceConfig, $totalInfo['localeId']),true);
                $category = $raceConfig['category'];
                $data[$category] += $totalInfo['total'];
            }
        }
        if(!$result)
            return $data;
        $row = $result->fetch_all(MYSQLI_ASSOC);
        //单场比赛
        foreach ($row as $info) {
            $temp = array();
            $temp['matchid'] = $info['matchid'];
            $temp['localeName'] = '';
            $temp['category'] ='';
            $temp['consume_amount'] = $info['consume_amount'];
            $temp['create_at'] = $info['create_at'];
            $temp['localeId'] = $info['localeId'];
            $temp['rank'] = $info['rank'];
            $temp['score'] = $info['score'];
            $temp['ladder'] = $info['ladder'];
            $temp['award'] = 0;

            $localeId = $info['localeId'];
            $temp['rank'] = (int)$info['rank'];

            //赛事名称，赛事类型
            $raceConfig = json_decode(ToolRedis::get()->hGet(gameConfig::RaceConfig, $localeId),true);
            $temp['localeName'] = $raceConfig['localeName'];
            $temp['category'] = self::$matchType[$raceConfig['category']];

            //奖励,从大家乐获取
            $awardResult = DajialeService::matchPrizes($info['matchid'], $userId);
            $temp['award'] = $awardResult['records'][0]['amount'];

            $data["list"][] = $temp;
            $data['consume_total'] += $info['consume_amount'];
            $data['score_total'] +=  $info['score'];
            $data['award_total'] += $temp['award'];
        }
        $data['total'] = (int)$data['total'];
        return $data;
    }

    /**
     * 创建天梯配置
     * @param $id
     * @param $grad
     * @return bool
     */
    static function  createLadderScore($id, $grad) {
        //开启事务
        ToolMySql::setAutocommit(false);
        $insertSql = "insert into ".Config::SQL_DB.".ladderscore (id, grad) values ('$id','$grad');";
        $result = ToolMySql::query($insertSql);
        if(!$result) {
            ToolMySql::rollback();
            return "创建天梯配置失败";
        }
        ToolMySql::commit();
        ToolMySql::setAutocommit(true);
        return true;
    }

    /**
     * 创建称号
     * @param $scoreMax
     * @param $title
     * @return bool|string
     */
    static function createLadderTitle($scoreMax, $title) {
        //开启事务
        ToolMySql::setAutocommit(false);
        $insertSql = "insert into ".Config::SQL_DB.".laddertitle (scoreMax, title) values ('$scoreMax', '$title')";
        $result = ToolMySql::query($insertSql);
        if(!$result) {
            ToolMySql::rollback();
            return "创建称号配置失败";
        }


        ToolMySql::commit();
        ToolMySql::setAutocommit(true);
        return true;
    }

    /**
     * 创建天梯奖励
     * @param $rankMax
     * @param $weekGiveback
     * @param $weekTicketNumb
     * @param $weekTicketId
     * @param $monthGiveback
     * @param $monthTicketNumb
     * @param $monthTicketId
     * @return bool|string
     */
    static function createLadderAward($rankMax, $weekGiveback, $weekTicketNumb, $weekTicketId, $monthGiveback, $monthTicketNumb, $monthTicketId) {
        //开启事务
        ToolMySql::setAutocommit(false);
        $insertSql = "insert into ".Config::SQL_DB.".ladderaward (rankMax, weekGiveback, weekTicketNumb, weekTicketId, monthGiveback, monthTicketNumb, monthTicketId) 
                        values ('$rankMax','$weekGiveback','$weekTicketNumb','$weekTicketId','$monthGiveback','$monthTicketNumb', '$monthTicketId');";
        $result = ToolMySql::query($insertSql);
        if(!$result) {
            ToolMySql::rollback();
            return "创建奖励配置失败";
        }

        ToolMySql::commit();
        ToolMySql::setAutocommit(true);
        return true;
    }

    static function pushLadder2Game() {
        //生成文件
        $fileResult = self::ladder2File();
        if (!$fileResult) {
            ToolMySql::rollback();
            return "创建天梯配置文件失败";
        }

        // 推送游戏服务端
        //todo 修改成服务器url
        $edition = time();
        $indexFileUrl = Config::Url."ladderConf/FileIndex.json?$edition";
        $serverUrl = Config::ServerMatchUrl.urlencode($indexFileUrl);
        $pushResult = ToolNet::push2GameServer($serverUrl);

        if (!$pushResult) {
            ToolMySql::rollback();
            return "推送游戏服务端失败";
        }
        //把配置推送到redis
        $redisResult = self::ladder2Redis();
        if ($redisResult ===false) {
            ToolMySql::rollback();
            return "同步redis失败";
        }
        return true;
    }

    /**
     * 把天梯配置/称号/奖励,写入文件,并且修改md5表
     * @return bool
     */
    static function ladder2File() {
        $ladderScoreSql = "select * from ".Config::SQL_DB.".ladderscore";
        $ladderTitleSql = "select * from ".Config::SQL_DB.".laddertitle";
        $ladderAwardSql = "select * from ".Config::SQL_DB.".ladderaward";

        $path = dirname(__FILE__).'/../backend/ladderConf';
        if(!file_exists($path)){//检查文件夹是否存在
            mkdir($path,0777 , true);    //没有就创建一个新文件夹
            chmod($path, 0777);
        }

        //写入天梯配置文件
        $ladderScoreResult = ToolMySql::query($ladderScoreSql);
        if (!$ladderScoreResult)
            return false;
        $data = $ladderScoreResult->fetch_all(MYSQLI_ASSOC);
        $fileName = dirname(__FILE__).'/../backend/ladderConf/LadderScoreConfig.json';
        if(!file_exists($fileName)) {
            touch($fileName,0777,true);
            $json_data = array("name"=>"LadderScoreConfig", "pk"=>"id", "data"=>array());
            $json_strings = json_encode($json_data);
            file_put_contents($fileName,$json_strings);//写入
        }

        $json_string = file_get_contents($fileName);
        $jsonData = json_decode($json_string,true);
        $jsonData['data'] =  $data;
        $json_strings = json_encode($jsonData);
        file_put_contents($fileName,$json_strings);//写入
        $LadderScoreConfigMd5 = md5_file($fileName);
        $ladderScoreResult->close();

        //写入称号配置
        $ladderTitleResult = ToolMySql::query($ladderTitleSql);
        if (!$ladderTitleResult)
            return false;
        $data = $ladderTitleResult->fetch_all(MYSQLI_ASSOC);
        $fileName = dirname(__FILE__).'/../backend/ladderConf/LadderTitleConfig.json';
        if(!file_exists($fileName)) {
            touch($fileName,0777,true);
            $json_data = array("name"=>"LadderTitleConfig", "pk"=>"id", "data"=>array());
            $json_strings = json_encode($json_data);
            file_put_contents($fileName,$json_strings);//写入
        }

        $json_string = file_get_contents($fileName);
        $jsonData = json_decode($json_string,true);
        $jsonData['data'] =  $data;
        $json_strings = json_encode($jsonData);
        file_put_contents($fileName,$json_strings);//写入
        $LadderTitleConfigMd5 = md5_file($fileName);
        $ladderTitleResult->close();

        //写入奖励配置
        $ladderAwardResult = ToolMySql::query($ladderAwardSql);
        if (!$ladderAwardResult)
            return false;
        $data = $ladderAwardResult->fetch_all(MYSQLI_ASSOC);
        $fileName = dirname(__FILE__).'/../backend/ladderConf/LadderAwardConfig.json';
        if(!file_exists($fileName)) {
            touch($fileName,0777,true);
            $json_data = array("name"=>"LadderAwardConfig", "pk"=>"id", "data"=>array());
            $json_strings = json_encode($json_data);
            file_put_contents($fileName,$json_strings);//写入
        }

        $json_string = file_get_contents($fileName);
        $jsonData = json_decode($json_string,true);
        $jsonData['data'] =  $data;
        $json_strings = json_encode($jsonData);
        file_put_contents($fileName,$json_strings);//写入
        $LadderAwardConfigMd5 = md5_file($fileName);
        $ladderAwardResult->close();


        //添加进索引文件
        $indexData = array();
        $fileName = dirname(__FILE__)."/../backend/ladderConf/FileIndex.json";
        if(!file_exists($fileName)) {
            touch($fileName,0777,true);
        }

        $ladderScoreData = Config::Url."ladderConf/LadderScoreConfig.json?md5=".$LadderScoreConfigMd5;
        $ladderTitleData = Config::Url."ladderConf/LadderTitleConfig.json?md5=".$LadderTitleConfigMd5;
        $ladderAwardData = Config::Url."ladderConf/LadderAwardConfig.json?md5=".$LadderAwardConfigMd5;

        $indexData[] = $ladderScoreData;
        $indexData[] = $ladderTitleData;
        $indexData[] = $ladderAwardData;

        $json_strings = json_encode($indexData);
        file_put_contents($fileName,$json_strings);//写入

        //修改数据表的MD5
        $updateSql = "replace into ".Config::SQL_DB.".confmd5(id, fileMd5) values('LadderAwardConfig.json', '$LadderAwardConfigMd5')
                                                                        ,('LadderScoreConfig.json', '$LadderScoreConfigMd5')
                                                                        ,('LadderTitleConfigMd5.json', '$LadderTitleConfigMd5')";

        $updateResult = ToolMySql::query($updateSql);
        if (!$updateResult)
            return false;
        return true;

    }

    /**
     * 从配置文件获取数据导入redis
     */
    static function ladder2Redis() {
        $ladderScorePath = dirname(__FILE__).'/../backend/ladderConf/LadderScoreConfig.json';
        $ladderTitlePath = dirname(__FILE__).'/../backend/ladderConf/LadderTitleConfig.json';
        $ladderAwardPath = dirname(__FILE__).'/../backend/ladderConf/LadderAwardConfig.json';

        $pathArray = array($ladderScorePath,$ladderTitlePath,$ladderAwardPath);

        foreach ($pathArray as $value) {
            if (file_exists($value)) {
                $redisName = 'backend:ladderConfig:';
                $ladderConfigString = file_get_contents($value);//将整个文件内容读入到一个字符串中
                $ladderConfig = json_decode($ladderConfigString,true);
                $redisName = $redisName. $ladderConfig['name'].":";
                $pk = $ladderConfig['pk'];

                //先清除旧的键值对
                ToolRedis::get()->del($redisName,'*');
                foreach ($ladderConfig['data'] as $value) {
                    $result = ToolRedis::get()->hSet($redisName, $value[$pk], json_encode($value));
                    if ($result === false) {
                        return false;
                    }

                }
            }
        }
        return true;
    }

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
     * a
     * 从数据库拉取最新配置，并且推送到游戏服务器和redis
     * @return bool|string
     */
    static function pushMatch2Game() {
        //生成文件
        $fileResult = self::getCfg2File();
        if (!$fileResult) {
            ToolMySql::rollback();
            return "创建赛事配置文件失败";
        }

        //把配置推送到redis
        $redisResult = self::file2Redis();
        if ($redisResult === false) {
            ToolMySql::rollback();
            return "同步redis失败";
        }

        // 推送游戏服务端
        //todo 修改成服务器url
        $edition = time();
        $indexFileUrl = Config::Url."matchConf/FileIndex.json?$edition";
        $serverUrl = Config::ServerMatchUrl.urlencode($indexFileUrl);
        $pushResult = ToolNet::push2GameServer($serverUrl);

        if (!$pushResult) {
            return "推送游戏服务端失败";
        }
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
                $sql = "select `id`,`localeId`,`localeName`,`raceTime`,`enrollTime`,`round`,`pernum`,`userNumb`,`score`,`condition`,`category`,`outRule`,`roomCost`,`baseLine`,`edition` from ".Config::SQL_DB.".race where localeId = '$localeId'";
                break;
            case "casino" :
                $sql = "select `id`,`localeId`,`localeName`,`condition`,`category`,`outRule`,`entranceFee`,`roomCost`,`carryMax`,`winMax`,`baseLine`,`edition` from ".Config::SQL_DB.".casino where localeId = '$localeId'";
                break;
            case "matchValidator":
                $sql = "select `group`,`sameIp`,`onlyAi`,`sameDesk`,`winRatio`,`winStreak`,`onoff`,`ipNoDesk`,`edition` from ".Config::SQL_DB.".matchValidator where `group` in (select `group` from  ".Config::SQL_DB.".`match` where localeId = '$localeId')";
                break;
            case "award" :
                $sql = "select `id`,`localeId`,`minId`,`maxId`,`awards`,`edition` from  ".Config::SQL_DB.".award where localeId = '$localeId'";
                break;
            default :
                return false;
                break;
        }
        $result = ToolMySql::query($sql);
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        return $rows[0];
    }


    /**
     * 修改赛事
     * @param $data
     * @return bool|mysqli_result
     */
    static function  updateMatches($data) {
        //开启事务
        ToolMySql::setAutocommit(false);
        foreach ($data as $key => $element) {
            switch ($key)
            {
                case "match" :
                    $matchSet = '';
                    $matchSql = '';
                    foreach ($element as $k=>$value) {
                        $matchSet = $matchSet."`match`.`$k` = '$value',";
                    }
                    $matchSet = substr($matchSet, 0, -1);

                    if($element->category === 1) {
                        //娱乐场
                        $casinoSql = ", `casino`.localeName = '$element->localeName'";
                        $matchSql = "update ".Config::SQL_DB.".`match` as `match`,".Config::SQL_DB.".`casino` as `casino`  set "
                            .$matchSet. $casinoSql." where `match`.localeId = '$element->localeId' and `match`.localeId = `casino`.localeId";
                    }
                    if($element->category === 2) {
                        //比赛场
                        $raceSql = ", `race`.localeName = '$element->localeName'";
                        $matchSql = "update ".Config::SQL_DB.".`match` as `match`,".Config::SQL_DB.".`race` as `race`  set "
                                    .$matchSet. $raceSql." where `match`.localeId = '$element->localeId' and `match`.localeId = `race`.localeId";
                    }
                    $result = ToolMySql::query($matchSql);
                    if (!$result) {
                        ToolMySql::rollback();
                        return "更新match失败";
                    }
                    break;
                case "casino":
                    $casinoSet = "";
                    foreach ($element as $k=>$value) {
                        $casinoSet = $casinoSet."`casino`.`$k` = '$value',";
                    }
                    $casinoSet = substr($casinoSet, 0 ,-1);
                    $casinoSql = "update ".Config::SQL_DB.".`casino` as `casino` set ".$casinoSet." where `casino`.id = $element->id";

                    $result = ToolMySql::query($casinoSql);
                    if (!$result)
                        return "更新casino失败";
                    break;
                case "race":
                    $raceSet = "";
                    foreach ($element as $k=>$value) {
                        $raceSet = $raceSet."`race`.`$k` = '$value',";
                    }
                    $raceSet = substr($raceSet, 0 ,-1);
                    $raceSql = "update ".Config::SQL_DB.".`race` as `race` set ".$raceSet." where `race`.id = $element->id";

                    $result = ToolMySql::query($raceSql);
                    if (!$result) {
                        ToolMySql::rollback();
                        return "更新race失败";
                    }
                    break;
                case "award":
                    $awardSet = "";
                    foreach ($element as $k=>$value) {
                        $awardSet = $awardSet."`award`.`$k` = '$value',";
                    }
                    $awardSet = substr($awardSet, 0 ,-1);
                    $awardSql = "update ".Config::SQL_DB.".`award` as `award` set ".$awardSet." where `award`.id = $element->id";

                    $result = ToolMySql::query($awardSql);
                    if (!$result) {
                        ToolMySql::rollback();
                        return "更新award失败";
                    }
                    break;
                case "matchValidator":
                    $matchValidatorSet = "";
                    foreach ($element as $k=>$value) {
                        $matchValidatorSet = $matchValidatorSet."`matchvalidator`.`$k` = '$value',";
                    }
                    $matchValidatorSet = substr($matchValidatorSet, 0 ,-1);
                    $matchValidatorSql = "update ".Config::SQL_DB.".`matchvalidator` as `matchvalidator` set ".$matchValidatorSet." where `matchvalidator`.group = $element->group";

                    $result = ToolMySql::query($matchValidatorSql);
                    if (!$result) {
                        ToolMySql::rollback();
                        return "更新matchValidatorSql失败";
                    }
                    break;
                default:
                    break;
            }
        }
        ToolMySql::commit();
        ToolMySql::setAutocommit(true);
        return true;
    }

    /**
     * 写入最新配置表和索引表
     * @return bool
     */
    static function getCfg2File() {
        $getMatchSql  = "SELECT * FROM ".Config::SQL_DB.".`match`";
        $getCasinoSql = "SELECT * FROM ".Config::SQL_DB.".`casino`";
        $getRaceSql = "SELECT * FROM ".Config::SQL_DB.".`race`";
        $getAwardSql = "SELECT * FROM ".Config::SQL_DB.".`award`";
        $getValidatorSql = "SELECT * FROM ".Config::SQL_DB.".`matchvalidator`";

        $confData = array("MatchConfig"=>'',"CasinoConfig"=>'',"RaceConfig"=>'',"AwardConfig"=>'',"MatchValidatorConfig"=>'');
        $path = dirname(__FILE__).'/../backend/matchConf';
        if(!file_exists($path)){//检查文件夹是否存在
            mkdir ($path,0777 , true);    //没有就创建一个新文件夹
            chmod($path, 0777);
        }

        //获取match
        $getMatchResult = ToolMySql::query($getMatchSql);
        if(!$getMatchResult)
            return false;
        $data = $getMatchResult->fetch_all(MYSQLI_ASSOC);

        //写入配置文件
        $fileName = dirname(__FILE__).'/../backend/matchConf/MatchConfig.json';
        if(!file_exists($fileName)) {
            touch($fileName,0777,true);
            $json_data = array("name"=>"MatchConfig", "pk"=>"localeId", "data"=>array());
            $json_strings = json_encode($json_data);
            file_put_contents($fileName,$json_strings);//写入
        }

        $json_string = file_get_contents($fileName);
        $jsonData = json_decode($json_string,true);
        $jsonData['data'] =  $data;
        $json_strings = json_encode($jsonData);
        file_put_contents($fileName,$json_strings);//写入
        $confData['MatchConfig'] = md5_file($fileName);
        $getMatchResult->close();

        //获取Casino
        $getCasinoResult = ToolMySql::query($getCasinoSql);
        if(!$getCasinoResult)
            return false;
        $data = $getCasinoResult->fetch_all(MYSQLI_ASSOC);

        //写入文件
        $fileName = dirname(__FILE__).'/../backend/matchConf/CasinoConfig.json';
        if(!file_exists($fileName)) {
            touch($fileName,0777,true);

            $json_data = array("name"=>"CasinoConfig", "pk"=>"localeId", "data"=>array());
            $json_strings = json_encode($json_data);
            file_put_contents($fileName,$json_strings);//写入
        }

        $json_string = file_get_contents($fileName);
        $jsonData = json_decode($json_string,true);
        $jsonData['data'] =  $data;
        $json_strings = json_encode($jsonData);
        file_put_contents($fileName,$json_strings);//写入
        $confData['CasinoConfig'] = md5_file($fileName);
        $getCasinoResult->close();

        //获取Race
        $getRaceResult = ToolMySql::query($getRaceSql);
        if(!$getRaceResult)
            return false;
        $data = $getRaceResult->fetch_all(MYSQLI_ASSOC);

        //写入文件
        $fileName = dirname(__FILE__).'/../backend/matchConf/RaceConfig.json';
        if(!file_exists($fileName)) {
            touch($fileName,0777,true);

            $json_data = array("name"=>"RaceConfig", "pk"=>"localeId", "data"=>array());
            $json_strings = json_encode($json_data);
            file_put_contents($fileName,$json_strings);//写入
        }

        $json_string = file_get_contents($fileName);
        $jsonData = json_decode($json_string,true);
        $jsonData['data'] =  $data;
        $json_strings = json_encode($jsonData);
        file_put_contents($fileName,$json_strings);//写入
        $confData['RaceConfig'] = md5_file($fileName);
        $getRaceResult->close();

        //获取Award
        $getAwardResult = ToolMySql::query($getAwardSql);
        if(!$getAwardResult)
            return false;
        $data = $getAwardResult->fetch_all(MYSQLI_ASSOC);

        //写入文件
        $fileName = dirname(__FILE__).'/../backend/matchConf/AwardConfig.json';
        if(!file_exists($fileName)) {
            touch($fileName,0777,true);

            $json_data = array("name"=>"AwardConfig", "pk"=>"id", "data"=>array());
            $json_strings = json_encode($json_data);
            file_put_contents($fileName,$json_strings);//写入
        }

        $json_string = file_get_contents($fileName);
        $jsonData = json_decode($json_string,true);
        $jsonData['data'] =  $data;
        $json_strings = json_encode($jsonData);
        file_put_contents($fileName,$json_strings);//写入
        $confData['AwardConfig'] = md5_file($fileName);
        $getAwardResult->close();

        //获取Validator
        $getValidatorResult = ToolMySql::query($getValidatorSql);
        if(!$getValidatorResult)
            return false;
        $data = $getValidatorResult->fetch_all(MYSQLI_ASSOC);

        //写入文件
        $fileName = dirname(__FILE__).'/../backend/matchConf/MatchValidatorConfig.json';
        if(!file_exists($fileName)) {
            touch($fileName,0777,true);

            $json_data = array("name"=>"MatchValidatorConfig", "pk"=>"group", "data"=>array());
            $json_strings = json_encode($json_data);
            file_put_contents($fileName,$json_strings);//写入
        }

        $json_string = file_get_contents($fileName);
        $jsonData = json_decode($json_string,true);
        $jsonData['data'] =  $data;
        $json_strings = json_encode($jsonData);
        file_put_contents($fileName,$json_strings);//写入
        $confData['MatchValidatorConfig'] = md5_file($fileName);
        $getValidatorResult->close();

        //添加进索引文件
        $indexData = array();
        $fileName = dirname(__FILE__)."/../backend/matchConf/FileIndex.json";
        if(!file_exists($fileName)) {
            touch($fileName,0777,true);
        }

        $matchIndexData = Config::Url."matchConf/MatchConfig.json?md5=".$confData['MatchConfig'];
        $raceIndexData = Config::Url."matchConf/RaceConfig.json?md5=".$confData['RaceConfig'];
        $casinoIndexData = Config::Url."matchConf/CasinoConfig.json?md5=".$confData['CasinoConfig'];
        $awardIndexData = Config::Url."matchConf/AwardConfig.json?md5=".$confData['AwardConfig'];
        $matchValidatorIndexData = Config::Url."matchConf/MatchValidatorConfig.json?md5=".$confData['MatchValidatorConfig'];
        $indexData[] = $matchIndexData;
        $indexData[] = $raceIndexData;
        $indexData[] = $casinoIndexData;
        $indexData[] = $awardIndexData;
        $indexData[] = $matchValidatorIndexData;

        $json_strings = json_encode($indexData);
        file_put_contents($fileName,$json_strings);//写入

        $AwardConfigMd5 = $confData['AwardConfig'];
        $MatchConfigMd5 = $confData['MatchConfig'];
        $CasinoConfigMd5 = $confData['CasinoConfig'];
        $RaceConfigMd5 = $confData['RaceConfig'];
        $MatchValidatorConfigMd5 = $confData['MatchValidatorConfig'];

        //修改数据表的MD5
        $updateSql = "REPLACE INTO confmd5 (id, fileMd5) VALUES('AwardConfig.json','$AwardConfigMd5')
                                                              ,('MatchConfig.json','$MatchConfigMd5')  
                                                              ,('CasinoConfig.json', '$CasinoConfigMd5')
                                                              ,('RaceConfig.json', '$RaceConfigMd5')
                                                              ,('MatchValidatorConfig.json', '$MatchValidatorConfigMd5')";

        $updateResult= ToolMySql::query($updateSql);
        if(!$updateResult)
            return false;
        return true;
    }

    /**
     * 从配置文件获取数据导入redis
     */
    static function file2Redis() {
        $matchConfigPath = dirname(__FILE__).'/../backend/matchConf/MatchConfig.json';
        $casinoConfigPath = dirname(__FILE__).'/../backend/matchConf/CasinoConfig.json';
        $raceConfigPath = dirname(__FILE__).'/../backend/matchConf/RaceConfig.json';
        $awardConfigPath = dirname(__FILE__).'/../backend/matchConf/AwardConfig.json';
        $matchValidatorConfigPath = dirname(__FILE__).'/../backend/matchConf/MatchValidatorConfig.json';
        $pathArray = array($matchConfigPath, $casinoConfigPath, $raceConfigPath, $awardConfigPath, $matchValidatorConfigPath);

        foreach ($pathArray as $value) {
            if(file_exists($value)){
                $redisName = 'backend:gameConfig:';
                $matchConfigString = file_get_contents($value);//将整个文件内容读入到一个字符串中
                $matchConfig = json_decode($matchConfigString,true);
                $redisName = $redisName. $matchConfig['name'].":";
                $pk = $matchConfig['pk'];

                //先清除旧的键值对
                ToolRedis::get()->del($redisName,'*');
                foreach ($matchConfig['data'] as $value){
                    $result = ToolRedis::get()->hSet($redisName, $value[$pk], json_encode($value));
                    if ($result === false)
                        return false;
                }
            }
        }

        //单独把好友赛(5)和快速赛(1)归类为快速赛放进redis
        $raceConfigString = file_get_contents($raceConfigPath);//将整个文件内容读入到一个字符串中
        $raceConfigList = json_decode($raceConfigString,true);
        $pk = $raceConfigList['pk'];
        $raceConfigData = $raceConfigList['data'];
        $fastRedisName = gameConfig::FastConfig;

        //清除旧键值对
        ToolRedis::get()->del($fastRedisName,'*');
        foreach ($raceConfigData as $raceConfigInfo) {
            if($raceConfigInfo['category'] ==='1' || $raceConfigInfo['category'] ==='5') {
                $result = ToolRedis::get()->hSet($fastRedisName, $raceConfigInfo[$pk], json_encode($raceConfigInfo));
                if ($result === false)
                    return false;
            }
        }
        return true;
    }

    /**
     * 从数据库拉取所有配置并且更新到redis
     * @param $type
     * @return bool|string
     */
    static function pushCfg2Game($type) {
        switch ($type)
        {
            case 'match' :
                return self::pushMatch2Game();
                break;
            case 'ladder':
                return self::pushLadder2Game();
                break;
            default:
                $result = self::pushMatch2Game();
                if ($result !== true)
                    return $result;
                $result = self::pushLadder2Game();
                if ($result !== true)
                    return $result;
                return $result;
                break;
        }
    }

    /**
     * 获取赛事列表,静态数据
     * @param $startDate
     * @param $endDate
     * @param $page
     * @param $limit
     * @return array
     */
    static function  getMatchList($page, $limit) {
        $start = ($page-1)*$limit;
        $matchListSql = "SELECT * FROM ".Config::SQL_DB.".`match` limit $start,$limit";
        $dataTotalSql = "select ifnull(count(localeId),0) AS data_total from ".Config::SQL_DB.".`match`";

        $matchListResult = ToolMySql::query($matchListSql);
        $dataTotalResult = ToolMySql::query($dataTotalSql);

        $data = array("total"=>0, "list"=>array());
        $data["total"] = $dataTotalResult->fetch_assoc()["data_total"];
        $data['list'] =  $matchListResult->fetch_all(MYSQLI_ASSOC);
        $data['total'] = (int)$data['total'];
        return $data;
    }

    /**
     * 获取正在比赛的列表，从游戏服务端获取数据
     * @param $page
     * @param $limit
     * @param $type
     * @return array
     */
    static function  getMatchingList($page, $limit, $type) {
        $start = ($page-1)*$limit;
        $now = ToolTime::utcToLocalSec(time());

        $Sql = "SELECT *,count(area_id) as many FROM(
                SELECT *
                FROM `".Config::HB_SQL_DB."`.`hb_race_data_1`
                union all
                SELECT *
                FROM `".Config::HB_SQL_DB."`.`hb_race_data_2`
                union all
                SELECT *
                FROM `".Config::HB_SQL_DB."`.`hb_race_data_3`
                union all
                SELECT*
                FROM `".Config::HB_SQL_DB."`.`hb_race_data_4`
                union all
                SELECT *
                FROM `".Config::HB_SQL_DB."`.`hb_race_data_5`
                union all
                SELECT *
                FROM `".Config::HB_SQL_DB."`.`hb_race_data_6`
                union all
                SELECT *
                FROM `".Config::HB_SQL_DB."`.`hb_race_data_7`
                union all
                SELECT *
                FROM `".Config::HB_SQL_DB."`.`hb_race_data_8`
                union all
                SELECT *
                FROM `".Config::HB_SQL_DB."`.`hb_race_data_9`
                union all
                SELECT *
                FROM `".Config::HB_SQL_DB."`.`hb_race_data_10`) as temp";

        $data = array("total"=>0, "many"=>0, "list"=>array());
        $matchingListSql = $Sql ." where `end`=0 and race_time < '$now' group by  area_id  order by enroll_time DESC";

        $matchingListResult = ToolMySql::query_gameServer($matchingListSql);
        $list =  $matchingListResult->fetch_all(MYSQLI_ASSOC);

        //从redis拉取比赛的配置,目前只是拉取比赛的配置
        $tempList = array();
        for($i = 0; $i< sizeof($list); $i++) {
            $locale_id = $list[$i]['locale_id'];
            $many = $list[$i]['many'];
            //如果redis拿不到这些数据就会导致返回的数据为空
            $matchConfig = json_decode(ToolRedis::get()->hGet(gameConfig::MatchConfig, $locale_id),true);
            $raceConfig = json_decode(ToolRedis::get()->hGet(gameConfig::RaceConfig, $locale_id),true);
            switch ($type)
            {
                case "waiting":
                    //如果是定时赛，只要开赛时间小于系统时间就是待开赛,开赛时间
                    if ((int)$many < (int)$raceConfig['userNumb']) {
                        $temp = array_merge($list[$i], $matchConfig);
                        $temp['condition'] =  $raceConfig['condition'];
                        $temp['round'] =  $raceConfig['round'];
                        $data['total'] += 1;
                        $data['many'] += $many;
                        $tempList[] = $temp;
                    }
                    break;
                case "doing":
                    if ((int)$many === (int)$raceConfig['userNumb']) {
                        $temp = array_merge($list[$i], $matchConfig);
                        $temp['condition'] =  $raceConfig['condition'];
                        $temp['round'] =  $raceConfig['round'];
                        $data['total'] += 1;
                        $data['many'] += $many;
                        $tempList[] = $temp;
                    }
                    break;
                default:
                    break;
            }
        }

        $data['list'] = array_slice($tempList, $start ,$limit);
        $data['total'] = (int)$data['total'];
        return $data;
    }
    /**
     * 创建管理员
     * @param $requireDate
     * @return bool|mysqli_result
     */
    static function addAdmin($requireDate) {
        $id = uniqid();
        $key = "id";
        $value = "'$id'";
        foreach ($requireDate as $keys => $values) {
            if($keys ==='password') {
                $values = password_hash('$values', PASSWORD_DEFAULT);
            }
            $key =$key.' ,'.$keys;
            $value = $value .' ,'."'$values'";
        }

        $addAdminSql = "insert into adminusers ($key) values ($value)";
        $addAdminResult = ToolMySql::query($addAdminSql);
        return $addAdminResult;
    }

    /**
     * 获取管理员列表
     * @return array
     */
    static function getAdminList() {
        $data = array("list"=>array(),'total'=>0);
        $getAdminListSql = "select id,username,weight,remark  from adminusers";
        $totalSql = "select count(id) as total from adminusers;";
        $getAdminListResult = ToolMySql::query($getAdminListSql);
        $totalSqlResult = ToolMySql::query($totalSql);

        if ($row =$totalSqlResult->fetch_assoc())
            $data['total'] = $row['total'];
        if ($row = $getAdminListResult->fetch_all(MYSQLI_ASSOC))
            $data['list'] = $row;
        $data['total'] = (int)$data['total'];
        return $data;
    }

    /**
     * 删除管理员
     * @param $userId
     * @return bool|mysqli_result
     */
    static function deleteAdmin($userId) {
        $sql = "delete from adminusers where id = '$userId'";
        $result = ToolMySql::query($sql);
        return $result;
    }

    /**
     * 存储客户端的异常信息
     * @param $error
     * @return bool|mysqli_result
     */
    static function saveError($error) {
        $sql = "INSERT INTO ".Config::SQL_DB.".fronterror (`error`) VALUES ('$error')";
        $result = ToolMySql::query($sql);
        return $result;
    }

    /**
     * 获取错误信息
     * @param $startDate
     * @param $endDate
     * @param $page
     * @param $limit
     * @return array
     */
    static function getErrorList($startDate, $endDate, $page, $limit) {
        $start = ($page-1)*$limit;
        $errorListSql = "SELECT `id`,`error`,`create_at` FROM ".Config::SQL_DB.".`fronterror` order by create_at limit $start,$limit";
        $errorTotalSql = "select ifnull(count(id),0) AS data_total from ".Config::SQL_DB.".`fronterror`";

        if($startDate && $endDate) {
            $startDate = $startDate.' 00:00:00';
            $endDate = $endDate.' 23:59:59';
            $errorListSql = "SELECT `id`,`error`,`create_at` FROM ".Config::SQL_DB.".`fronterror` where create_at > '$startDate' and create_at < '$endDate' order by create_at limit $start,$limit";
            $errorTotalSql = "select ifnull(count(id),0) AS data_total from ".Config::SQL_DB.".`fronterror` where create_at > '$startDate' and create_at < '$endDate' ";
        }

        $errorListResult = ToolMySql::query($errorListSql);
        $errorTotalResult = ToolMySql::query($errorTotalSql);

        $data = array("total"=>0, "list"=>array());
        $data["total"] = $errorTotalResult->fetch_assoc()["data_total"];
        $data['list'] =  $errorListResult->fetch_all(MYSQLI_ASSOC);
        $data['total'] = (int)$data['total'];
        return $data;
    }

    /**
     * 预绑定
     * @param $unionid
     * @param $iv_code
     * @param $role
     * @return bool|mysqli_result
     */
    static function preBind($unionid, $iv_code, $role) {
        $sql = "INSERT INTO ".Config::SQL_DB.".`prebind`(`unionid`,`iv_code`,`role`,`status`)VALUES('$unionid','$iv_code','$role','0')";
        $checkSql = "select unionid from ".Config::SQL_DB.".`prebind` where unionid = '$unionid'";
        $isExist = ToolMySql::query($checkSql);
        if($isExist->fetch_assoc())
            return 'the account exist ';

        if ($role === 1) {
            $checkAgentStatusSql = "select status from agentusers where id = '$iv_code'";
            $checkAgentStatusResult = ToolMySql::query($checkAgentStatusSql);
            $checkAgentStatus = $checkAgentStatusResult->fetch_assoc()['status'];
            if ($checkAgentStatus !== '1')
                return 'agent wrong';
        }
        $result = ToolMySql::query($sql);
        if(!$result)
            return  $sql;
        return true;
    }



    /**
     * @param $type int 999
     * @param $obj Object {
     *                  "companyId": "111111111111111111",
                        "gameId": "22222222222222222",
                        "userId": "33333333333333",
                        "unionId": "wxREWfr35gw5fur32r23e2f23t32fwffewf",
                        "phoneNumber": "13111111111",
                        "name": "张三",
                        "gender": 0,
                        "role": ""|1|2
                        "inviteCode": 游戏服务端发
                        "createdAt": "2020-04-01 15:00:00",
                        "invitationCode" : "玩家邀请码",
                        "contractorCode" : "代理邀请码",
                        "myInvitationCode" : "我的邀请码"
     *                  }
     * @return bool|string
     */
    static function playerRegister($type, $obj) {
        //获取玩家rid
        $getRidSql = "select rid from ".Config::HB_SQL_DB.".hb_role where open_id = '$obj->userId'";
        $ridResult = ToolMySql::query_gameServer($getRidSql);
        if (!$ridResult->num_rows)
            return "查找玩家open_id:'$obj->userId'失败";
        $rid = $ridResult->fetch_assoc()['rid'];
        //如果是玩家邀请，通过邀请码获取邀请者的userId
        $from_uid = '';
        if ($ivcode = $obj->invitationCode) {
            $findPlayerIdSql = "select id from ".Config::SQL_DB.".playerusers where ivcode = '$ivcode'";
            $result = ToolMySql::query($findPlayerIdSql);
            $from_uid = $result->fetch_assoc()['id'];
        }
        //注册
        $insertSql = "insert into ".Config::SQL_DB.".playerusers (`id`,`rid`,`wxunionid`,`username`,`parent_id`,`tel`,`from_uid`,`ivcode`) values ('$obj->userId','$rid','$obj->unionId','$obj->name', '$obj->contractorCode', '$obj->phoneNumber','$from_uid','$obj->myInvitationCode')";
        $result = ToolMySql::query($insertSql);
        if (!$result)
            return "注册玩家失败，sql:".$insertSql;

        if (!Game::checkPlayer2Redis($obj->userId))
            return $obj->userId.'推送到redis出错';
        return true;
    }

    /**
     * 考虑
     * 同步玩家rid
     * @param $limit
     * @return bool
     */
    static function synRid($limit) {
        $checkSql = "select id from ".Config::SQL_DB.".playerusers where rid = 0 order by create_at limit 0,$limit ;";
        $checkResult = ToolMySql::query($checkSql);
        $row = $checkResult->fetch_all(MYSQLI_ASSOC);
        foreach ($row as $value) {
            //从游戏数据库查找玩家
            $open_id = $value['id'];
            $findGamePlayerSql = "select rid from ".Config::HB_SQL_DB.".hb_role where open_id ='$open_id'";
            $findGamePlayerResult = ToolMySql::query_gameServer($findGamePlayerSql);

            if(!$rid = $findGamePlayerResult->fetch_assoc()['rid'])
                continue;

            //更新后台服务器的玩家rid
            $updatePlayerSql = "update ".Config::SQL_DB.".playerusers set rid = '$rid' where id = '$open_id'";
            $result = ToolMySql::query($updatePlayerSql);
            if (!$result) {
                Logger::debug("同步玩家'$open_id'失败");
                continue;
            }
        }
        return true;
    }

    /**
     * 考虑
     * 同步玩家unionId
     * @param $limit
     * @return bool
     */
    static function synUnionId() {
        $checkSql = "select id from ".Config::SQL_DB.".playerusers";
        $checkResult = ToolMySql::query($checkSql);
        $row = $checkResult->fetch_all(MYSQLI_ASSOC);

        foreach ($row as $value) {
            //从游戏数据库查找玩家
            $open_id = $value['id'];
            $findGamePlayerSql = "select wxunionid from ".Config::HB_SQL_DB.".hb_role where open_id ='$open_id'";
            $findGamePlayerResult = ToolMySql::query_gameServer($findGamePlayerSql);
            if(!$wxunionid = $findGamePlayerResult->fetch_assoc()['wxunionid'])
                continue;
            //更新后台服务器的玩家rid
            $updatePlayerSql = "update ".Config::SQL_DB.".playerusers set wxunionid = '$wxunionid' where id = '$open_id'";

            $result = ToolMySql::query($updatePlayerSql);
            if (!$result) {
                Logger::debug("同步玩家'$open_id'失败");
                continue;
            }
        }
        return true;
    }

    /**
     * 考虑
     * 同步游戏服务器玩家到后台服务器,如果后台已经有了，什么都不做，没有则插进来
     * @return bool|string
     */
    static function synPlayers() {
        $checkPlayersSql = "select rid,wxunionid, open_id, nick, mobile_phone from ".Config::HB_SQL_DB.".hb_role where ai = 0 and wxunionid <>''";
        $checkPlayersResult = ToolMySql::query_gameServer($checkPlayersSql);
        if (!$checkPlayersResult)
            return '抓取数据失败';
        $row = $checkPlayersResult->fetch_all(MYSQLI_ASSOC);
        foreach ($row as $info) {
            $rid = $info['rid'];
            $wxunionid = $info['wxunionid'];
            $open_id = $info['open_id'];
            $username = addslashes($info['nick']);
            $tel = $info['mobile_phone'];
            //查询后台是否有此玩家,有就更新，没有插入
            $isExistSql = "select id from ".Config::SQL_DB.".playerusers where id = '$open_id'";

            $isExistResult = ToolMySql::query($isExistSql);
            if (!$isExistResult)
                return '后台数据库查询出错';

            //后台已经存在该玩家
            if($id = $isExistResult->fetch_assoc()['id']) {
//                $updateSql = "update ".Config::SQL_DB.".playerusers set rid = '$rid' where id = '$open_id'";
//                $updateResult = ToolMySql::query($updateSql);
//                if (!$updateResult)
//                    return "更新后台玩家'$open_id'的rid失败";
            }
            else {
                //不存在则重新插入,需要构建
                $findRoleSql = "select FROM_UNIXTIME(created_time, '%Y-%c-%d %h:%i:%s' )as create_time from ".Config::HB_LOG_SQL_DB.".hb_ci_user where role_id = '$rid'  group by role_id";
                $findRoleResult = ToolMySql::query_gameServer($findRoleSql);
                if (!$findRoleResult)
                    return "查询'$open_id'创建时间失败";
                $create_at =  $findRoleResult->fetch_assoc()['create_time'];
                $insertSql = "insert into ".Config::SQL_DB.".playerusers (id, rid, wxunionid,username, parent_id, create_at, tel, from_uid) values ('$open_id', '$rid','$wxunionid', '$username', '','$create_at','$tel', '')";
                //插入后台服务器
                $insertResult = ToolMySql::query($insertSql);
                if (!$insertResult)
                    continue;
                    //return "插入玩家'$open_id'入后台服务器失败,'$insertSql'";
            }
        }
        return true;
    }

    /**
     * 玩家充值
     * @param $userId
     * @param $orderNo
     * @param $gameId
     * @param $amount
     * @param $subject
     * @param $mode
     * @return bool|mysqli_result
     */
    static function playerCharge($userId, $orderNo, $gameId, $amount, $subject, $mode) {
            $insertChargeSql = "insert into ".Config::SQL_DB.".charge (`id`,`user_id`,`charge_amount`,`currency`,`status`,`mode`,`game_id`) values ('$orderNo', '$userId', '$amount', '$subject', '1', '$mode', '$gameId')";
            $result = ToolMySql::query($insertChargeSql);
            if (!$result)
                return  $insertChargeSql;
            return $result;
    }

    /**
     * 玩家消耗
     * @param $userId
     * @param $gameId
     * @param $amount
     * @param $subject
     * @return bool|mysqli_result
     */
    static function playerConsume($userId, $matchId, $amount, $status, $localeId) {
        $insertConsumeSql = "insert into ".Config::SQL_DB.".consume (`user_id`,`matchid`,`consume_amount`,`status`,`localeId`) values ('$userId', '$matchId', '$amount', '$status','$localeId')";
        $checkConsumeSql = "select id from ".Config::SQL_DB.".consume where matchid='$matchId' and user_id='$userId'";

        $isExist = ToolMySql::query($checkConsumeSql);
        $rows = $isExist->fetch_assoc()['id'];
        if($rows) {
            $updateSql = "update ".Config::SQL_DB.".consume set status = '$status' where id = '$rows'";
            return ToolMySql::query($updateSql);
        }
        $result = ToolMySql::query($insertConsumeSql);
        if(!$result)
            return $insertConsumeSql;
        return true;
    }

    /**
     * 玩家比赛结果，对于三人斗地主可以验证农名和地主
     * @param $localeId
     * @param $matchId
     * @param $list
     * @return bool|string
     */
    static function playerPrize($localeId, $matchId, $list) {
        $setRank = '';
        $setScore = '';
        $setLord = '';
        $setLadder = '';
        $winners = array();
        $losers = array();

        //获取相应的天梯积分
        $ladderScoreConfig = json_decode(ToolRedis::get()->hGet(ladderConfig::LadderScoreConfig, $localeId),true);
        $winScore = $ladderScoreConfig['win'];
        $loseScore = $ladderScoreConfig['lose'];

        foreach ($list as $one) {
            $userId = $one->userId;
            $rank = $one->rank;
            $score = $one->score;

            if ($rank == '1') {
                //赢
                $winners[] = $userId;
                $setLadder = $setLadder." WHEN '$userId' THEN '$winScore'";
            }
            else {
                //输
                $losers[] = $userId;
                $setLadder = $setLadder. " WHEN '$userId' THEN '$loseScore'";
            }

            $setRank = $setRank." WHEN '$userId' THEN '$rank'";
            $setScore = $setScore." WHEN '$userId' THEN '$score'";
        }

        //如果胜利人数为1人，代表是地主胜利；如果胜利人数大于等于2，代表胜利的为农民，输的为地主
        if (sizeof($winners) === 1) {
            $setLord = $setLord."WHEN '$winners[0]' THEN '1'";
            foreach ($losers as $loser) {
                $setLord = $setLord ."WHEN '$loser' THEN '0'";
            }
        }
        elseif (sizeof($winners) >= 2) {
            $setLord = $setLord ."WHEN '$losers[0]' THEN '1'";
            foreach ($winners as $winner)
                $setLord = $setLord ."WHEN '$winner' THEN '0'";
        }

        $sql = "UPDATE ".Config::SQL_DB.".consume 
                SET rank = CASE user_id 
                    $setRank
                END, 
                score = CASE user_id 
                    $setScore
                END,
                is_lord = CASE user_id
                    $setLord
                END,
                ladder = CASE user_id
                    $setLadder
                END
                WHERE matchid = '$matchId' and status = 1;";

        $result = ToolMySql::query($sql);
        if(!$result)
            return $sql;
        return true;
    }

    /**
     * 生成门票
     * @param $count
     * @param $type
     * @return bool|mysqli_result
     */
    static function generateTicket($count,$type) {
        $insert = '';
        for ($i =0; $i < (int)$count; $i++) {
            $hash = strtoupper(md5(uniqid()));
            $ticket = substr($hash, 0, 8) .
                '-' .
                substr($hash, 8, 6) .
                '-' .
                substr($hash, 14, 6) .
                '-' .
                substr($hash, 20, 6) .
                '-' .
                substr($hash, 26, 12) ;

            $insert = $insert."('$ticket','0','$type'),";
        }
        $insert = substr($insert, 0, -1);

        $insertSql = "insert into ".Config::SQL_DB.".ticket values".$insert;
        return ToolMySql::query($insertSql);
    }

    /**
     * 返回门票列表
     * @param $page
     * @param $limit
     * @param $statue
     * @param $type
     * @return array|bool
     */
    static function getAllTicket($page, $limit, $statue, $type) {
        $data = array('total'=>0, "list"=>array());
        $start = ($page-1)*$limit;
        $where = '';
        if (is_numeric($statue))
            $where = $where."where status = '$statue'";

        if (is_numeric($type))
            $where = $where."and type='$type'";
        $sql = "select * from ".Config::SQL_DB.".ticket $where order by id limit $start,$limit";
        $totalSql = "select count(*) as total from ".Config::SQL_DB.".ticket ".$where;

        $result = ToolMySql::query($sql);
        if(!$result)
            return  false;

        $totalResult = ToolMySql::query($totalSql);
        $data['total'] = $totalResult->fetch_assoc()['total'];
        $data['list'] = $result->fetch_all(MYSQLI_ASSOC);
        $data['total'] = (int)$data['total'];
        return $data;
    }

    /**
     * 获取一张门票
     * @param $id
     * @return array
     */
    static function  getTicket($id) {
        $data = array("total"=>1, "list"=> array());
        $sql = "select * from ".Config::SQL_DB.".ticket where id = '$id'";

        $result = ToolMySql::query($sql);
        $data['list']= $result->fetch_all(MYSQLI_ASSOC);
        $data['total'] = (int)$data['total'];
        return $data;
    }

    /**
     * 检查门票
     * @param $id
     * @return bool|string
     */
    static function  checkTicket($id) {
        $sql = "select status from ".Config::SQL_DB.".ticket where id = '$id'";
        $result = ToolMySql::query($sql);
        if (!$result)
            return '查无此门票';

        $status = $result->fetch_assoc()['status'];
        if ((int)$status!==0)
            return '门票不可用';
        return true;
    }

    /**
     * 财务概况-从大家乐拉取
     * @param $startDate
     * @param $endDate
     * @param $page
     * @param $limit
     * @param $userId
     * @return array|bool
     */
    static function financeSummary($startDate, $endDate, $page, $limit) {
        $start = ($page-1)*$limit;
        $dateList = ToolTime::getDateFromRange($startDate,$endDate);
        $data = array('total'=>sizeof($dateList), 'charge_total'=>0,'charge_number'=>0,'charge_count'=>0,'serviceFee'=>0,'list'=>array());

        foreach ($dateList as $date) {
            $startDate = ToolTime::strToUtc($date) *1000;
            $endDate = ToolTime::getOneDayEndSec(ToolTime::strToUtc($date)) *1000;
            $startDateString = $date. " 00:00:00";
            $endDateString = $data. " 23:59:59";

            $temp = array();
            $temp['date'] = $date;
            $temp['charge_total'] = 0;
            $temp["serviceFee"] = 0;
            $temp["charge_number"] = 0;
            $temp['charge_count'] = 0;
            $temp['leQuan_total'] = '-';
            $temp['award_total'] = '-';

            //充值总额
            $temp['charge_total'] = DajialeService::getRechargeTotal($startDate, $endDate);
            $data['charge_total'] += $temp['charge_total'];

            //消费总额,时间格式是字符串，大家乐的坑
            $temp["serviceFee"] = DajialeService::getConsumeTotal($startDateString, $endDateString)['serviceFee'];
            $data['serviceFee'] += $temp["serviceFee"];

            //充值人数
            $temp['charge_number'] = DajialeService::pageRecharge(3,1,$startDate, $endDate)['total'];
            $data['charge_number'] += $temp['charge_number'];

            //充值次数
            //($startDate, $endDate, $page, $limit, $userId ="")
            $temp['charge_count'] = DajialeService::rechargePage($startDate,$endDate,1, 1)['total'];
            $data['charge_count'] += $temp['charge_count'];

            $data['list'][]=$temp;
        }
        $data['serviceFee'] = round($data['serviceFee'],2);
        $data['list'] = array_slice($data['list'],$start,$limit);

        $data['total'] = (int)$data['total'];
        return $data;
    }

    /**
     * 返回充值记录
     * @param $startDate
     * @param $endDate
     * @param $page
     * @param $limit
     * @param $tradeNo
     * @return array
     */
    static function chargeList($startDate, $endDate, $page, $limit, $tradeNo) {
        $data = array('total'=>0, 'charge_total'=>0,'list'=>array());
        if ($startDate && $endDate) {
            $startDate = ToolTime::strToUtc($startDate) *1000;
            $endDate = ToolTime::getOneDayEndSec(ToolTime::strToUtc($endDate)) *1000;
        }

        $chargeList = DajialeService::rechargePage($startDate,$endDate,$page,$limit,'',$tradeNo);

        $data['total'] = $chargeList['total'];
        foreach ($chargeList['records'] as $chargeInfo) {
            //由于大家乐接口没有提供玩家信息，需要自己从redis拉取用户信息
            $username = '-';
            //如果是承接商下载充值是没有userId的
            if($chargeInfo['userId']) {
                $username = DbPlayerInfo::hGet( $chargeInfo['userId'], "username");
                if (!$username) {
                    //同步游戏服务端和自身服务器的玩家数据，并推送到redis
                    $checkResult = self::checkPlayer2Redis($chargeInfo['userId']);
                    if ($checkResult === true)
                        $username = DbPlayerInfo::hGet( $chargeInfo['userId'], "username");
                    else {
                        $username = $checkResult;
                    }
                }
            }
            $temp = array();
            $temp['tradeNo'] = $chargeInfo['tradeNo'];
            $temp['createTime'] = date('Y-m-d H:i',$chargeInfo['createTime']/1000);
            $temp['userId'] = $chargeInfo['userId'];;
            $temp['username'] = $username;
            $temp['amount'] = $chargeInfo['amount'];
            $temp['subject'] = $chargeInfo['subject'];
            $temp['mode'] = DajialeService::$modeType[$chargeInfo['mode']];
            $temp['status'] = $chargeInfo['remark'];

            //1待支付 2充值处理中 3充值成功 4充值失败
            if($chargeInfo['status'] === 3)
                $data['charge_total'] += $chargeInfo['amount'];
            $data['list'][] = $temp;
        }

        $data['total'] = (int)$data['total'];
        return $data;
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
            //insert into ".Config::SQL_DB.".playerusers (`id`,`username`,`parent_id`,`tel`,`from_uid`) values ('$id','$username', '$parent_id', '$tel','$from_uid')";
//            $selectSql = "select rid,open_id,wxunionid, nick, mobile_phone from ".Config::HB_SQL_DB.".hb_role where open_id = '$userId';";
//            $selectResult = ToolMySql::query_gameServer($selectSql);
//
//            if (!$selectResult)
//                return "从游戏服务器查询出错,玩家:'$userId'";
//            $selectInfo = $selectResult->fetch_all(MYSQLI_ASSOC)[0];
//            $rid = $selectInfo['rid'];
//            $open_id = $selectInfo['open_id'];
//            $wxunionid = $selectInfo['wxunionid'];
//            $username = $selectInfo['nick'];
//            $tel = $selectInfo['mobile_phone'];
//
//
//            //不存在则重新插入,需要构建
//            $findRoleSql = "select FROM_UNIXTIME(created_time, '%Y-%c-%d %h:%i:%s' )as create_time from ".Config::HB_LOG_SQL_DB.".hb_ci_user where role_id = '$rid'  group by role_id";
//            $findRoleResult = ToolMySql::query_gameServer($findRoleSql);
//            if (!$findRoleResult)
//                return "查询'$open_id'创建时间出错";
//            $create_at =  $findRoleResult->fetch_assoc()['create_time'];
//            $insertSql = "insert into ".Config::SQL_DB.".playerusers (id, rid, wxunionid,username, parent_id, create_at, tel, from_uid) values ('$open_id', '$rid','$wxunionid', '$username', '','$create_at','$tel', '')";
//            //插入后台服务器
//            $insertResult = ToolMySql::query($insertSql);
//            if (!$insertResult)
//                return "同步玩家数据到自身服务器失败,玩家:'$insertSql',数据:$selectInfo";
//
//            $checkSql = "select * from ".Config::SQL_DB.".playerusers where id = '$userId'";
//            $checkResult = ToolMySql::query($checkSql);
//            if (!$checkResult)
//                return "从自身服务器查询失败2,玩家:'$userId'";
//            ToolMySql::close_gameServer();
        }
        $userInfo = $checkResult->fetch_all(MYSQLI_ASSOC)[0];
        $userId = $userInfo['id'];
        DbPlayerInfo::hMSet($userId, $userInfo);
        return true;
    }


    /**
     * 考虑 一次性用 给湖南长沙传输数据用
     * @param $userId
     * @param $amount
     * @param $count
*/
    static function sendToCS() {
        $sql = "select * from ".Config::HB_SQL_DB.".hb_role where wxunionid <> '' and ai=0";
        $result = ToolMySql::query_gameServer($sql);
        $row = $result->fetch_all(MYSQLI_ASSOC);

        $data = array("type"=>999,"obj"=>array());
        $companyId = Config::DajialeUserId;
        $gameId = Config::gameId;
        foreach ($row as $info) {
            $temp = array();
            $userId = $info['open_id'];
            $unionId = $info['wxunionid'];
            $phoneNumber = $info['mobile_phone'];
            $name = $info['nick'];
            if ($info['sex'] === 1)
                $gender = 0;
            else
                $gender = 1 ;

            //查找创建时间
            $checkNameSql = "select created_time from ".Config::HB_LOG_SQL_DB.".hb_ci_user where user_name = '$userId' group by user_name; ";
            $checkNameResult = ToolMySql::query_gameServer($checkNameSql);
            $created_time = $checkNameResult->fetch_assoc()['created_time'];
            $created_time = date('Y-m-d H:i', $created_time);
            $temp['companyId'] = $companyId;
            $temp['gameId'] = $gameId;
            $temp['userId'] = $userId;
            $temp['unionId'] = $unionId;
            $temp['phoneNumber'] = $phoneNumber;
            $temp['name'] = $name;
            $temp['gender'] = $gender;
            $temp['agentInviteCode'] = '';
            $temp['createdAt'] = $created_time;
            $data["obj"] = $temp;

            $result = Register2Agent::saveRecord($data);
            if(!$result ===true)
                return $result;
        }
        return true;
    }



}
//ToolMySql::conn();
////////Game::daily('2020-01-17', '2020-02-22','charge_players');
////////Game::allPlayers('',1,5);
//////Game::getChargeInfo('GBDV-dwGaAtgjDFj',1,5);
////Game::joinMatch(1,50);
////Game::saveError('das1d3qw54');
//Game::playerRegister('sad4sd4',10064,'a15a4','18814144587');
//Game::preBind('$openid', '10016', 1);
//Game::file2Redis();
//var_dump(Game::getCfg2File());
//ToolMySql::close();
//ToolMySql::conn();
//Game::checkPlayer2Redis("2020032814000189019");
//ToolMySql::close();
