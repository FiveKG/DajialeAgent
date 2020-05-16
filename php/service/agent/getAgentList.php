<?php
include_once dirname(__FILE__) . "/../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../util/toolTime.php";
include_once dirname(__FILE__) . "/../../util/toolForAgent.php";
include_once dirname(__FILE__) . "/../../util/toolRunTime.php";
include_once dirname(__FILE__) . "/../../util/logger.php";

class GetAgentList {
    /**
     * 分页返回我的代理商数据
     * @param $agentId 代理商id
     * @param $page 页数
     * @param $limit 偏移量
     * @return array|null
     */
    static function getMyAgentList($agentId, $page, $limit) {
        $start = ($page-1)*$limit;
        $data = array();
        //我的代理商总量
        $myAgentTotalSql = "select ifnull(sum(case when status = 1 then 1 else 0 end),0) as agent_total from ".Config::SQL_DB.".agentusers where parent_id = '$agentId';";
        $myAgentTotalResult = ToolMySql::query($myAgentTotalSql);
        $data["agent_total"] = $myAgentTotalResult->fetch_assoc()['agent_total'];
        $data["my_agent_list"] = array();
        $SubAgentInfoSql = "select id,username,tel,level,status,apply_at,note from ".Config::SQL_DB.". agentusers where parent_id = '$agentId' and status = 1 order by create_at asc limit $start,$limit";
        $SubAgentInfoResult = ToolMySql::query($SubAgentInfoSql);

        //我的下级代理
        while ($subAgentRow = $SubAgentInfoResult->fetch_assoc()) {
            $subAgent = array();
            $mySubAgentId = $subAgentRow["id"];
            $playerCCTotal = ToolForAgent::getPlayerTotal2($mySubAgentId);
            $playerAgentTotal = self::getPlayerAgentTotal($mySubAgentId);
            $subSubAgentCCTotal = ToolForAgent::getAgentTotal($mySubAgentId);

            //获取代理的分成
            $sql = "select profit from ".Config::SQL_DB.".agentusers where id = '$mySubAgentId'";
            $result = ToolMySql::query($sql);

            $profit =  $result->fetch_assoc()['profit'];
            $subAgent["id"] = $subAgentRow["id"];
            $subAgent["username"] = $subAgentRow["username"];
            $subAgent["tel"] = $subAgentRow["tel"];
            $subAgent["level"] = $subAgentRow["level"];
            $subAgent["status"] = $subAgentRow["status"];
            $subAgent["apply_at"] = $subAgentRow["apply_at"];
            $subAgent["note"] = $subAgentRow["note"];
            $subAgent["player_total"] = $playerAgentTotal["player_total"];
            $subAgent["agent_total"] = $playerAgentTotal["agent_total"];
            $subAgent["charge_total"] = round($playerCCTotal["charge_total"],2);
            $subAgent["consume_total"] = round($playerCCTotal["consume_total"],2);
            $subAgent["sub_charge_total"] = $subSubAgentCCTotal["charge_total"];
            $subAgent["sub_consume_total"] = $subSubAgentCCTotal["consume_total"];
            $subAgent["sub_player_total"] = 0;
            $subAgent["profit"] = $profit;

            $subSubAgentIdSql = "select id from ".Config::SQL_DB.". agentusers where parent_id ='$mySubAgentId' and status = 1";
            $subSubAgentIdResult = ToolMySql::query($subSubAgentIdSql);
            if($subSubAgentIdResult->num_rows > 1) {
                while ($row = $subSubAgentIdResult->fetch_assoc()) {
                    $subSubId = $row['id'];
                    $playerAmountSql = "select ifnull(sum(case when parent_id = '$subSubId' then 1 else 0 end ),0) as player_total from ".Config::SQL_DB.". playerusers";
                    $playerAmountResult = ToolMySql::query($playerAmountSql);
                    $subAgent["sub_player_total"] += (int)$playerAmountResult->fetch_assoc()['player_total'];
                }
            }
            array_push($data["my_agent_list"], $subAgent);
        }
        return $data;
    }

    /**
     * 返回代理商的玩家/代理商总量(agent_total,player_total)
     * @param $agentId
     * @return array|null
     */
    static function getPlayerAgentTotal($agentId) {
        $totalSql = "
        select sum(player_total) as player_total , sum(agent_total) as agent_total from (
        select 
            ifnull(sum(case when status=1 then 1 else 0 end ),0) as agent_total,0 as player_total
        from ".Config::SQL_DB.".agentusers 
        where parent_id = '$agentId'
        union all
        select 
        0 as agent_total,ifnull(sum(case when parent_id = '$agentId' then 1 else 0 end ),0) as player_total
        from  ".Config::SQL_DB.".playerusers) as temp";
        $totalResult = ToolMySql::query($totalSql);
        return $totalResult->fetch_assoc();
    }
}