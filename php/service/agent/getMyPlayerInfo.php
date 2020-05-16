<?php
include_once dirname(__FILE__) . "/../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../util/toolTime.php";
include_once dirname(__FILE__) . "/../../util/toolForAgent.php";
include_once dirname(__FILE__) . "/../../util/toolRunTime.php";
include_once dirname(__FILE__) . "/../../util/logger.php";

class GetMyPlayerInfo {
    /**
     * 按类型返回代理的所有玩家总额信息
     * @param $agentId
     * @param $type
     * @return array|null
     */
    static function getPlayerListTotal($agentId, $type) {
        $typeSql1 = '';
        $typeSql2= '';
        if ($type === "direct") {
            $typeSql1 = " where from_uid =''";
            $typeSql2 = " and from_uid = ''";
        }
        else if($type === "fission") {
            $typeSql1 = " where from_uid !=''";
            $typeSql2 = " and from_uid !=''";
        }

        //保留月充值查询的语法
        $sql = "
          select sum(total) as total,sum(today) as today,sum(yesterday) as yesterday,sum(charge_total) as charge_total,sum(consume_total) as consume_total,sum(month_charge) as month_charge,sum(month_consume) as month_consume from(
          select ifnull(sum(case when parent_id = '$agentId' then 1 else 0 end ),0) as total,0 as today,0 as yesterday,0 as charge_total,0 as consume_total,0 as month_charge,0 as month_consume from  ".Config::SQL_DB.".playerusers $typeSql1
          Union all
          select 0 as total,ifnull(sum(case when parent_id = '$agentId' then 1 else 0 end ),0) as today,0 as yesterday,0 as charge_total,0 as consume_total,0 as month_charge,0 as month_consume from".Config::SQL_DB.". playerusers where DATEDIFF(create_at,curdate())=0 $typeSql2
          union all
          select 0 as total ,0 as today,ifnull(sum(case when parent_id = '$agentId' then 1 else 0 end ),0) as yesterday,0 as charge_total,0 as consume_total,0 as month_charge,0 as month_consume from".Config::SQL_DB.". playerusers where DATEDIFF(create_at,curdate())=-1 $typeSql2
          union all
          select 0 as total,0 as today,0 as yesterday,ifnull(sum(case when parent_id = '$agentId'  then charge_amount else 0 end),0) as charge_total,0 as consume_total,0 as month_charge,0 as month_consume from".Config::SQL_DB.". player_charge $typeSql1 
          Union all
          select 0 as total,0 as today,0 as yesterday,0 as charge_total,ifnull(sum(case when parent_id = '$agentId' and status ='1' then consume_amount else 0 end),0) as consume_total,0 as month_charge,0 as month_consume  from ".Config::SQL_DB.".player_consume $typeSql1 
          union all
          select 0 as total ,0 as today,0 as yesterday,0 as charge_total,0 as consume_total,ifnull(sum(case when parent_id = '$agentId' then charge_amount else 0 end),0) as month_charge,0 as month_consume  from ".Config::SQL_DB.".player_charge where month(charge_time) = month(curdate()) and year(curdate()) $typeSql2
          union all 
          select 0 as total,0 as today,0 as yesterday,0 as charge_total,0 as consume_total,0 as month_charge,ifnull(sum(case when parent_id = '$agentId' and status ='1' then consume_amount else 0 end),0) as month_consume from ".Config::SQL_DB.".player_consume where month(consume_time) = month(curdate()) and year(curdate()) $typeSql2
        ) as my_player_total;
        ";
        $sql2 = "
          select sum(total) as total,sum(today) as today,sum(yesterday) as yesterday,sum(charge_total) as charge_total,sum(consume_total) as consume_total from(
          select ifnull(sum(case when parent_id = '$agentId' then 1 else 0 end ),0) as total,0 as today,0 as yesterday,0 as charge_total,0 as consume_total from  ".Config::SQL_DB.".playerusers $typeSql1
          Union all
          select 0 as total,ifnull(sum(case when parent_id = '$agentId' then 1 else 0 end ),0) as today,0 as yesterday,0 as charge_total,0 as consume_total from ".Config::SQL_DB.".playerusers where DATEDIFF(create_at,curdate())=0 $typeSql2
          union all
          select 0 as total ,0 as today,ifnull(sum(case when parent_id = '$agentId' then 1 else 0 end ),0) as yesterday,0 as charge_total,0 as consume_total from ".Config::SQL_DB.".playerusers where DATEDIFF(create_at,curdate())=-1 $typeSql2
          union all
          select 0 as total,0 as today,0 as yesterday,ifnull(sum(case when parent_id = '$agentId'  then charge_amount else 0 end),0) as charge_total,0 as consume_total from ".Config::SQL_DB.".player_charge $typeSql1 
          Union all
          select 0 as total,0 as today,0 as yesterday,0 as charge_total,ifnull(sum(case when parent_id = '$agentId' and status ='1' then consume_amount else 0 end),0) as consume_total from ".Config::SQL_DB.".player_consume $typeSql1 
          union all
          select 0 as total ,0 as today,0 as yesterday,0 as charge_total,0 as consume_total from ".Config::SQL_DB.".player_charge where month(charge_time) = month(curdate()) and year(curdate()) $typeSql2
          union all 
          select 0 as total,0 as today,0 as yesterday,0 as charge_total,0 as consume_total from ".Config::SQL_DB.".player_consume where month(consume_time) = month(curdate()) and year(curdate()) $typeSql2
        ) as my_player_total;
        ";
        $myPlayerInfo = ToolMySql::query($sql2);
        $row = $myPlayerInfo->fetch_assoc();

        $row['charge_total'] = round($row['charge_total'], 2);
        $row['consume_total'] = round($row['consume_total'], 2);

        $myPlayerInfo->close();
        return $row;
    }
}