<?php
include_once dirname(__FILE__) . "/../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../util/toolTime.php";
include_once dirname(__FILE__) . "/../../util/toolForAgent.php";
include_once dirname(__FILE__) . "/../../util/toolRunTime.php";
include_once dirname(__FILE__) . "/../../util/logger.php";

class GetMyPlayerList {
    /**
     * 选手列表
     * @param $agentId
     * @param $type
     * @param $page
     * @param $limit
     * @return array
     */
    static function getPlayerList($agentId, $type, $page, $limit) {
        $typeSql = '';
        if ($type === "direct") {
            $typeSql = " and from_uid =''";
        }
        else if($type === "fission") {
            $typeSql = " and from_uid !=''";
        }
        $start = ($page-1)*$limit;

        $routineSql = "
             select id,username,sum(charge_total) as charge_total ,sum(consume_total) as consume_total, tel,create_at from(
             select id,username,ifnull(sum(player_charge.charge_amount),0) as charge_total,0 as consume_total,tel,create_at from player_charge where parent_id = '$agentId'$typeSql group by id 
             union all
             select id,username,0 as charge_total,ifnull(sum(player_consume.consume_amount),0) as consume_total,tel,create_at from ".Config::SQL_DB.".player_consume where status ='1' and parent_id = '$agentId' $typeSql group by id
             ) as total group by id limit $start,$limit
        ";

        $data = array();
        $myPlayerList = ToolMySql::query($routineSql);
        while ($row = $myPlayerList->fetch_assoc()) {
            $row['charge_total'] = round($row['charge_total'],2);
            $row['consume_total'] = round($row['consume_total'],2);
            $data[] = $row;
        }
        return $data;
    }
}