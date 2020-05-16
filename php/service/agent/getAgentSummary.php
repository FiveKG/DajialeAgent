<?php
include_once dirname(__FILE__) . "/../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../util/toolTime.php";
include_once dirname(__FILE__) . "/../../util/toolForAgent.php";
include_once dirname(__FILE__) . "/../../util/toolRunTime.php";
include_once dirname(__FILE__) . "/../../util/logger.php";

class GetAgentSummary {
    /**
     * 获取承办方概况
     * @param $startDate
     * @param $endDate
     * @param $page
     * @param $limit
     * @return array
     */
    static function getSummary($startDate, $endDate, $page, $limit) {
        $dateRange = ToolTime::getDateFromRange($startDate, $endDate);
        $start = ($page-1)*$limit;
        $dateList = array_slice($dateRange,$start,$limit);
        $data = array();

//        //方法二，数据库做排序
        $startDate = $startDate.' 00:00:00';
        $endDate = $endDate.' 23:59:59';
        $sql = "select sum(charge_total) as all_charge_total, sum(consume_total) as all_consume_total, sum(agent_charge_total) as agent_charge_total, sum(agent_consume_total) as agent_consume_total, user_time from (
                select ifnull(sum(charge_amount),0) as charge_total,0 as consume_total , 0 as agent_charge_total, 0 as agent_consume_total, DATE_FORMAT( charge_time, \"%Y-%m-%d\" ) as user_time from ".Config::SQL_DB.". player_charge
                where charge_time > '$startDate' AND charge_time< '$endDate' group by user_time
                union all
                select 0 as charge_total, ifnull(sum(consume_amount),0) as consume_total,0 as agent_charge_total, 0 as agent_consume_total, DATE_FORMAT( consume_time, \"%Y-%m-%d\" ) as user_time from ".Config::SQL_DB.".player_consume
                where consume_time > '$startDate' AND consume_time< '$endDate' and status='1' group by user_time
                union all
                select 0 as charge_total,0 as consume_total , sum(ifnull(charge_amount,0)) as agent_charge_total, 0 as agent_consume_total, DATE_FORMAT( charge_time, \"%Y-%m-%d\" ) as user_time from ".Config::SQL_DB.".player_charge
                where charge_time > '$startDate' AND charge_time< '$endDate' and parent_id != '' group by user_time
                union all
                select 0 as charge_total,0 as consume_total,0 as agent_charge_total,sum(ifnull(consume_amount,0)) as agent_consume_total,DATE_FORMAT( consume_time, \"%Y-%m-%d\" ) as user_time from ".Config::SQL_DB.".player_consume
                where consume_time > '$startDate' AND consume_time< '$endDate' and parent_id != '' and status='1' group by user_time
                ) as test group by user_time limit $start,$limit";

        $result = ToolMySql::query($sql);
        while ($row = $result->fetch_assoc()) {
            $temp = array();
            $temp["date"] = $row['user_time'];
            $temp["all_player_charge"] = round($row['all_charge_total'],2);
            $temp["all_player_consume"] = round($row['all_consume_total'],2);
            $temp["agent_player_consume"] = round($row['agent_charge_total'],2);
            $temp["agent_player_charge"] = round($row['agent_consume_total'],2);
            $data[] = $temp;
        }

        return $data;
    }
}