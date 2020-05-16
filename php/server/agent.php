<?php
include_once dirname(__FILE__) . "/../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../util/toolTime.php";
include_once dirname(__FILE__) . "/../util/logger.php";

class Agent {
    /**
     * @param $userId 用户id
     * @param $date 查询日期：最小单位为月
     * @param $role 查看角色，1为下级代理商，2为代理玩家
     * @param $type type = array("fission", "direct", "all")
     * @return array|null
     *
     */

    static function _getDataFromCalendar($userId, $date, $role, $type) {
        $sql = "select all_total from calendar  where user_id = '$userId' and date_list='$date' and role='$role'";
        if($type === "fission") {
            $sql = "select fission_total from calendar  where user_id = '$userId' and date_list='$date' and role='$role'";
        }
        elseif ($type ==="direct" ) {
            $sql = "select direct_total from calendar  where user_id = '$userId' and date_list='$date' and role='$role'";
        }

        $result = ToolMySql::query($sql);
        $row = $result->fetch_assoc();

        if($row===null) {
            return null;
        }

        $data = array();
        $data['charge_total'] = 0;
        $data['consume_total'] = 0;
        $total = json_decode($row[$type.'_total']);

        if ($total) {
            $data['charge_total'] = $total->charge_total;
            $data['consume_total'] = $total->consume_total;
        }

        return $data;
    }

    /**
     * @param $userId 用户id
     * @param $chargeTotal 充值总额
     * @param $consumeTotal  消费总额
     * @param $date   日期
     * @param $role    查看角色，1为下级代理商，2为代理玩家
     * @param $type type = array("fission", "direct", "all")
     * @return bool
     */
    static function _insertCalendar($userId, $chargeTotal, $consumeTotal, $date, $role, $type) {
        //ToolMySql::conn();
        $id = uniqid();
        $total = json_encode(array("charge_total"=>$chargeTotal, "consume_total"=>$consumeTotal));

        $searchSql = "select id from operation.calendar where user_id = '$userId' and date_list= '$date'";
        $searchResult = ToolMySql::query($searchSql);
        $searchId = $searchResult->fetch_assoc()['id'];

        $insertSql = "INSERT INTO operation.calendar(id,user_id,all_total,date_list,role)VALUES('$id','$userId', '$total' , '$date','$role')";
        $updateSql = "update operation.calendar set all_total = '$total' where id = '$searchId'";
        if($type === "fission") {
            $insertSql = "INSERT INTO operation.calendar(id,user_id,fission_total,date_list,role)VALUES('$id','$userId','$total','$date','$role')";
            $updateSql = "update operation.calendar set fission_total = '$total' where id = '$searchId'";
        }elseif ($type === "direct") {
            $insertSql = "INSERT INTO operation.calendar(id,user_id,direct_total,date_list,role)VALUES('$id','$userId','$total','$date','$role')";
            $updateSql = "update operation.calendar set direct_total = '$total' where id = '$searchId'";
        }

        if($searchId) {
            if (ToolMySql::query($updateSql) !== true) {
                return false;
            }
        }
        else {
            if (ToolMySql::query($insertSql) !== true) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param $agentId
     * @param date 格式"2020-02"
     * @return array|null
     */
    static function getPlayerCCByMonth($agentId, $date) {
        //ToolMySql::conn();
        $role = 2;
        $data = array();
        $data["agentId"] = $agentId;
        $data["date"] = $date;
        $data["charge_total"] = 0;
        $data["consume_total"] = 0;
        $chargeTotalSql = "
            select ifnull(sum(case when parent_id = '$agentId' then charge_amount else 0 end),0) as charge_total 
            from player_charge
            where  strcmp(date_format(charge_time,'%Y-%m'),'$date') = 0;
        ";

        $consumeTotalSql = "
        select ifnull(sum(case when parent_id = '$agentId' then consume_amount else 0 end),0) as consume_total 
        from player_consume 
        where  strcmp(date_format(consume_time,'%Y-%m'),'$date') = 0;
        ";
        //先查询日历表里有没有该用户这个月份的数据，没有则实时查询插入日历表，有直接返回
        //如果不是本月
        if ($date !== ToolTime::getMonth()) {
            $calendarResult = self::_getDataFromCalendar($agentId, $date, $role);
            if ($calendarResult) {
                //var_dump("查询月calendar有数据:");
                $data["charge_total"] = $calendarResult["charge_total"];
                $data["consume_total"] = $calendarResult["consume_total"];
                return $calendarResult;
            }
        }

        $chargeTotal = ToolMySql::query($chargeTotalSql);
        $consumeTotal = ToolMySql::query($consumeTotalSql);
        $data["charge_total"] = (int)$chargeTotal->fetch_assoc()['charge_total'];
        $data["consume_total"] = (int)$consumeTotal->fetch_assoc()['consume_total'];

        if ($date !== ToolTime::getMonth()) {
            self::_insertCalendar($agentId, $data['charge_total'], $data['consume_total'], $date, $role);
        }

        $chargeTotal->close();
        $consumeTotal->close();
        //ToolMySql::close();

        return $data;
    }

    /**
     * 按时间返回代理玩家总额
     * @param $agentId 代理商id
     * @param $date 查询年月:xxxx-xx 查询天:xxxx-xx-xx
     * @param $type 类型，全部，直属，分裂
     * @return array|null
     */
    static function getPlayerCCByDate($agentId, $date, $type) {
        $data = array();
        $role = 2;
        $data["charge_total"] = 0;
        $data["consume_total"] = 0;
        $chargeSql = "";
        $consumeSql = "";
        $dayOrMonth = "";
        $typeSql = '';
        if ($type === "direct") {
            $typeSql = " from_uid ='' and";
        }
        else if($type === "fission") {
            $typeSql = " from_uid !='' and";
        }

        //ToolMySql::conn();
        $chargeByDaySql = "
            select ifnull(sum(case when parent_id = '$agentId' then charge_amount else 0 end),0) as charge_total
            from player_charge
            where $typeSql DATEDIFF(charge_time,'$date')=0 ;
        ";
        $consumeByDaySql = "
            select ifnull(sum(case when parent_id = '$agentId' then consume_amount else 0 end),0) as consume_total
            from player_consume
            where $typeSql DATEDIFF(consume_time,'$date')=0 ;
        ";
        $chargeByMonthSql = "
            select ifnull(sum(case when parent_id = '$agentId' then charge_amount else 0 end),0) as charge_total 
            from player_charge
            where $typeSql strcmp(date_format(charge_time,'%Y-%m'),'$date') = 0;
        ";

        $consumeByMonthSql = "
            select ifnull(sum(case when parent_id = '$agentId' then consume_amount else 0 end),0) as consume_total 
            from player_consume 
            where $typeSql strcmp(date_format(consume_time,'%Y-%m'),'$date') = 0;
        ";

        //如果日期格式是天/月
        if (preg_match('/\d{4}-\d{1,2}-\d{1,2}/', $date)) {
            $chargeSql = $chargeByDaySql;
            $consumeSql = $consumeByDaySql;
            $dayOrMonth = ToolTime::getToday();
            //var_dump('查询的是天');
        }
        else if (preg_match('/\d{4}-\d{1,2}/', $date)) {
            $chargeSql = $chargeByMonthSql;
            $consumeSql = $consumeByMonthSql;
            $dayOrMonth = ToolTime::getMonth();
            //var_dump('查询的是月');
        }
        //判断查询日期是否不是今天/这个月,如果是，calendar又存在,可以避免全表查询
        if ($data !== $dayOrMonth) {
            $calendarResult = self::_getDataFromCalendar($agentId, $date, $role, $type);
            if ($calendarResult) {
                $data["charge_total"] = $calendarResult["charge_total"];
                $data["consume_total"] = $calendarResult["consume_total"];
                return $data;
            }
        }

        $chargeTotal = ToolMySql::query("$chargeSql");
        $consumeTotal = ToolMySql::query("$consumeSql");
        $data["charge_total"] = (int)$chargeTotal->fetch_assoc()['charge_total'];
        $data["consume_total"] = (int)$consumeTotal->fetch_assoc()['consume_total'];

        if ($date !== $dayOrMonth) {
            self::_insertCalendar($agentId, $data["charge_total"], $data["consume_total"], $date, $role, $type);
        }
        $chargeTotal->close();
        $consumeTotal->close();
        //ToolMySql::close();
        return $data;
    }

    /**
     * 获取直属玩家总额
     * @param $agentId
     * @return array
     */
    static function getPlayerTotal($agentId) {
        $data = array();
        $data["charge_total"] = 0;
        $data["consume_total"] = 0;
        //ToolMySql::conn();

        $sql = "
              select ifnull(sum(charge_total),0) as charge_total,ifnull(sum(consume_total),0) as consume_total from(
              select ifnull(sum(case when parent_id = '$agentId' then charge_amount else 0 end),0) as charge_total,0 as consume_total from player_charge
              Union all
              select 0 as charge_total,ifnull(sum(case when parent_id = '$agentId' then consume_amount else 0 end),0) as consume_total from player_consume
              ) as PlayerTotal;
        ";

        $PlayerTotal = ToolMySql::query($sql);
        $total = $PlayerTotal->fetch_assoc();
        $data["charge_total"] = (int)$total['charge_total'];
        $data["consume_total"] = (int)$total["consume_total"];

        $PlayerTotal->close();
        //ToolMySql::close();
        return $data;
    }

    /** 按时间返回所有下级代理商总额
     * @param $agentId 代理商id
     * @param $date 查询年月:xxxx-xx 查询天:xxxx-xx-xx
     * @param $type 类型，全部，直属，分裂
     * @return array|null
     */
    static function getAgentCCByDate($agentId, $date, $type) {
        $data = array();
        $role = 1;
        $data["agentId"] = $agentId;
        $data["date"] = $date;
        $data["charge_total"] = 0;
        $data["consume_total"] = 0;
        $dayOrMonth = "";

        //ToolMySql::conn();
        $myAgentListSql = "select id from agentusers where parent_id = '$agentId'";
        //如果日期格式是天/月
        if (preg_match('/\d{4}-\d{1,2}-\d{1,2}/', $date)) {
            $dayOrMonth = ToolTime::getToday();
            //var_dump('查询代理的是天');
        }
        else if (preg_match('/\d{4}-\d{1,2}/', $date)) {
            $dayOrMonth = ToolTime::getMonth();
            //var_dump('查询代理的是月');
        }

        $myAgentList = ToolMySql::query($myAgentListSql);
        while ($row = $myAgentList->fetch_assoc()["id"]) {
            $result = self::getPlayerCCByDate($row, $date, $type);
            $chargeTotal = $result["charge_total"];
            $consumeTotal = $result["consume_total"];

            $data["charge_total"] += (int)$chargeTotal;
            $data["consume_total"] += (int)$consumeTotal;

        }

        if ($date !== $dayOrMonth) {
            self::_insertCalendar($agentId, $data["charge_total"], $data["consume_total"], $date, $role,$type);
        }

        $myAgentList->close();
        //ToolMySql::close();
        return $data;
    }

    /**
     * 获取下级代理商总额
     * @param $agentId
     * @return array
     */
    static function getAgentTotal($agentId) {
        $data = array();
        $data["charge_total"] = 0;
        $data["consume_total"] = 0;
        //ToolMySql::conn();

        $myAgentListSql = "
            select id from agentusers where parent_id = '$agentId';
        ";

        $myAgentList = ToolMySql::query("$myAgentListSql");
        while ($row = $myAgentList->fetch_assoc()["id"]) {
            $result = self::getPlayerTotal($row);
            $data["charge_total"] += $result["charge_total"];
            $data["consume_total"] += $result["consume_total"];
        }

        $myAgentList->close();
        //ToolMySql::close();
        return $data;
    }

    /**
     * 返回代理商个人信息
     * @param $agentId 代理商id
     * @return array|null
     */
    static function myInfo($agentId) {
        $data = array();
        $myInfoSql = ToolMySql::query(
            "
        select id,level,username,agent_invite_code,player_invite_code,parent_tel,sum(agent_total) as agent_total,sum(player_total) as player_total from (
            select 
            agentusers1.id,
            agentusers1.level,
            agentusers1.username,
            agentusers1.agent_invite_code,
            agentusers1.player_invite_code,
            agentusers2.tel as parent_tel,
            0 as agent_total,
            0 as player_total
            from operation.agentusers as agentusers1 left join operation.agentusers as agentusers2 on agentusers1.parent_id = agentusers2.id where agentusers1.id = '$agentId'
            union all
            select
            0 as id,
            0 as level,
            0 as username,
            0 as agent_invite_code,
            0 as player_invite_code,
            0 as parent_tel,
            ifnull(sum(case when parent_id = '$agentId' then 1 else 0 end ),0) as agent_total,
            0 as player_total
            from  agentusers
            union all
            select
            0 as id,
            0 as level,
            0 as username,
            0 as agent_invite_code,
            0 as player_invite_code,
            0 as parent_tel,
            0 as agent_total,
            ifnull(sum(case when parent_id = '$agentId' then 1 else 0 end ),0) as player_total
            from  playerusers
        ) as myInfo"
        );
        $row = $myInfoSql->fetch_assoc();
        $myInfoSql->close();

        return $row;
    }

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

        $sql = "
            select sum(total) as total,sum(today) as today,sum(yesterday) as yesterday,sum(charge_total) as charge_total,sum(consume_total) as consume_total,sum(month_charge) as month_charge,sum(month_consume) as month_consume from(
          select ifnull(sum(case when parent_id = '$agentId' then 1 else 0 end ),0) as total,0 as today,0 as yesterday,0 as charge_total,0 as consume_total,0 as month_charge,0 as month_consume from  playerusers $typeSql1
          Union all
          select 0 as total,ifnull(sum(case when parent_id = '$agentId' then 1 else 0 end ),0) as today,0 as yesterday,0 as charge_total,0 as consume_total,0 as month_charge,0 as month_consume from playerusers where DATEDIFF(create_at,curdate())=0 $typeSql2
          union all
          select 0 as total ,0 as today,ifnull(sum(case when parent_id = '$agentId' then 1 else 0 end ),0) as yesterday,0 as charge_total,0 as consume_total,0 as month_charge,0 as month_consume from playerusers where DATEDIFF(create_at,curdate())=-1 $typeSql2
          union all
          select 0 as total,0 as today,0 as yesterday,ifnull(sum(case when parent_id = '$agentId' then charge_amount else 0 end),0) as charge_total,0 as consume_total,0 as month_charge,0 as month_consume from player_charge $typeSql1
          Union all
          select 0 as total,0 as today,0 as yesterday,0 as charge_total,ifnull(sum(case when parent_id = '$agentId' then consume_amount else 0 end),0) as consume_total,0 as month_charge,0 as month_consume  from player_consume $typeSql1
          union all
          select 0 as total ,0 as today,0 as yesterday,0 as charge_total,0 as consume_total,ifnull(sum(case when parent_id = '$agentId' then charge_amount else 0 end),0) as month_charge,0 as month_consume  from player_charge where month(charge_time) = month(curdate()) and year(curdate()) $typeSql2
          union all 
          select 0 as total,0 as today,0 as yesterday,0 as charge_total,0 as consume_total,0 as month_charge,ifnull(sum(case when parent_id = '$agentId' then consume_amount else 0 end),0) as month_consume from player_consume where month(consume_time) = month(curdate()) and year(curdate()) $typeSql2
        ) as my_player_total;
        ";

        $myPlayerInfo = ToolMySql::query($sql);
        $row = $myPlayerInfo->fetch_assoc();
        unset($row["month_charge"]);
        unset($row["month_consume"]);

        $myPlayerInfo->close();
        return $row;
    }

    static function getPlayerList($agentId, $type, $page, $limit) {
        $typeSql = '';
        if ($type === "direct") {
            $typeSql = " and from_uid =''";
        }
        else if($type === "fission") {
            $typeSql = " and from_uid !=''";
        }
        $start = ($page-1)*$limit;
        $sql = "
            	 select id,username,sum(charge_total) as charge_total ,sum(consume_total) as consume_total, tel,create_at from(
                     select id,username,sum(player_charge.charge_amount) as charge_total,0 as consume_total,tel,create_at 
                     from player_charge 
                     where id 
                     in (select player_id from 
                        (select player_id from playerindex where parent_id = '$agentId' $typeSql and id > $limit*($page-1) limit $limit )
                         as playerindex) group by id
                     union all 
                     select id,username,0 as consume_total,sum(player_consume.consume_amount) as charge_total,tel,create_at
                     from player_consume
                     where id 
                     in (select player_id from 
                        (select player_id from playerindex where parent_id = '$agentId' $typeSql and id > $limit*($page-1) limit $limit )
                         as playerindex) group by id
                 )
                 as total GROUP BY id;
        ";

        $routineSql = "
             select id,username,sum(charge_total) as charge_total ,sum(consume_total) as consume_total, tel,create_at from(
             select id,username,ifnull(sum(player_charge.charge_amount),0) as charge_total,0 as consume_total,tel,create_at from player_charge where parent_id = '$agentId'$typeSql group by id 
             union all
             select id,username,0 as charge_total,ifnull(sum(player_consume.consume_amount),0) as consume_total,tel,create_at from player_consume where parent_id = '$agentId' $typeSql group by id
             ) as total group by id limit $start,$limit
        ";

        $data = array();
        $myPlayerList = ToolMySql::query($routineSql);
        while ($row = $myPlayerList->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }

    /**
     * 分页犯规我的代理商数据
     * @param $agentId 代理商id
     * @param $page 页数
     * @param $limit 偏移量
     * @return array|null
     */
    static function getAgentList($agentId, $page, $limit) {
        $start = ($page-1)*$limit;
        $myAgentSql = "select id,username,tel,level,status,create_at,player_total,agent_total from agentusers where parent_id = '$agentId' order by create_at asc";
        $data = array();
        $data["agent_total"] = 0;
        $agent_total = 0;
        $myAgentResult = ToolMySql::query($myAgentSql);

        if (($agent_total+=$myAgentResult->num_rows) < 1) {
            return null;
        }

        $data["agent_total"] += $agent_total;
        $data["my_agent_list"] = array();
        //我的代理
        while ($myAgentRow = $myAgentResult->fetch_assoc()) {
            $myAgent = array();
            $myAgentId = $myAgentRow["id"];
            $total = self::getPlayerTotal($myAgentId);

            $myAgent["username"] = $myAgentRow["username"];
            $myAgent["tel"] = $myAgentRow["tel"];
            $myAgent["level"] = $myAgentRow["level"];
            $myAgent["status"] = $myAgentRow["status"];
            $myAgent["create_at"] = $myAgentRow["create_at"];
            $myAgent["player_total"] = $myAgentRow["player_total"];
            $myAgent["agent_total"] = $myAgentRow["agent_total"];
            $myAgent["charge_total"] = $total["charge_total"];
            $myAgent["consume_total"] = $total["consume_total"];
            $myAgent["sub_charge_total"] = 0;
            $myAgent["sub_consume_total"] = 0;

            //我的代理的下级代理
            $mySubAgentSql = "select id,username,tel,level,status,create_at,player_total,agent_total from agentusers where parent_id ='$myAgentId' order by create_at asc ";
            $mySubAgentResult = ToolMySql::query($mySubAgentSql);
            $mySubAgentTotal = $mySubAgentResult->num_rows;

            if ($mySubAgentTotal > 0) {
                $data["agent_total"] += $mySubAgentTotal;
                while ($mySubAgentRow = $mySubAgentResult->fetch_assoc()) {
                    $subAgent = array();
                    $mySubAgentId = $mySubAgentRow["id"];
                    $total = self::getPlayerTotal($mySubAgentId);

                    $subAgent["username"] = $mySubAgentRow["username"];
                    $subAgent["tel"] = $mySubAgentRow["tel"];
                    $subAgent["level"] = $mySubAgentRow["level"];
                    $subAgent["status"] = $mySubAgentRow["status"];
                    $subAgent["create_at"] = $mySubAgentRow["create_at"];
                    $subAgent["player_total"] = $mySubAgentRow["player_total"];
                    $subAgent["agent_total"] = $mySubAgentRow["agent_total"];
                    $subAgent["charge_total"] = $total["charge_total"];
                    $subAgent["consume_total"] = $total["consume_total"];
                    $subAgent["sub_charge_total"] = 0;
                    $subAgent["sub_consume_total"] = 0;

                    //累加给上一级代理的总额并把下级代理信息放进总列表
                    $myAgent["sub_charge_total"] += $subAgent["charge_total"];
                    $myAgent["sub_consume_total"] += $subAgent["consume_total"];
                    array_push($data["my_agent_list"], $subAgent);

                }
            }
            array_push($data["my_agent_list"], $myAgent);
        }

        $data["my_agent_list"] = array_slice($data["my_agent_list"], $start, $limit);
        return $data;
    }

    /**
     * @param $startDate "2020-01-01"
     * @param $endDate "2020-02-01"
     * @return array
     */
    static function getAgentSummary($startDate, $endDate) {
        $dateRange = ToolTime::getDateFromRange($startDate, $endDate);

        $list = array();
        for ($i = 0; $i < sizeof($dateRange); $i++) {
            $temp = array();
            $allPlayerChargeSql = "select ifnull(sum( charge_amount),0) as charge_total from player_charge where DATEDIFF(charge_time, '$dateRange[$i]')=0";
            $allPlayerConsumeSql = "select ifnull(sum(consume_amount),0) as consume_total from player_consume where DATEDIFF(consume_time, '$dateRange[$i]')=0";
            $agentPlayerChargeSql = "select ifnull(sum(case when parent_id != '' then charge_amount else 0 end),0) as charge_total from player_charge where DATEDIFF(charge_time,'$dateRange[$i]')=0";
            $agentPlayerConsumeSql = "select ifnull(sum(case when parent_id = 'agent-test1' then consume_amount else 0 end),0) as consume_total from player_consume where DATEDIFF(consume_time,'$dateRange[$i]')=0";

            $allPlayerChargeResult = (ToolMySql::query($allPlayerChargeSql))->fetch_assoc()["charge_total"];
            $allPlayerConsumeResult = (ToolMySql::query($allPlayerConsumeSql))->fetch_assoc()["consume_total"];
            $agentPlayerChargeResult = (ToolMySql::query($agentPlayerChargeSql))->fetch_assoc()["charge_total"];
            $agentPlayerConsumeResult = (ToolMySql::query($agentPlayerConsumeSql))->fetch_assoc()["consume_total"];
            $temp["date"] = $dateRange[$i];
            $temp["all_player_charge"] = $allPlayerChargeResult;
            $temp["all_player_consume"] = $allPlayerConsumeResult;
            $temp["agent_player_consume"] = $agentPlayerConsumeResult;
            $temp["agent_player_charge"] = $agentPlayerChargeResult;
            $list[] = $temp;
        }
        return $list;
    }
}
//ToolMySql::conn();
//
////var_dump(Agent::getPlayerCCByMonth('agent-test1','2019-12'));
////var_dump(Agent::getPlayerCCByDate('agent-test1','2020-01-07'));
//var_dump(Agent::getPlayerTotal('test6'));
//var_dump(Agent::getAgentCCByDate('agent-test1','2020-01','fission'));
////var_dump(Agent::getAgentTotal('agent-test1'));
////var_dump(Agent::myInfo('agent-test1'));
//var_dump(Agent::playerListTotal('agent-test1', 2));
//var_dump(Agent::getPlayerList('agent-test1', 'all', 1, 5));
//var_dump(Agent::_getDataFromCalendar('agent-test2','2020-01',2,'all'));
//$userId, $chargeTotal, $consumeTotal, $date, $role, $type
//var_dump(Agent::_insertCalendar('agent-test2',1001,123, '2020-07', 1,'all_total'));
//var_dump(Agent::getAgentList('agent-test2',1,10));
//var_dump(Agent::getAgentSummary("2020-01-01","2020-02-20"));
//ToolMySql::close();


