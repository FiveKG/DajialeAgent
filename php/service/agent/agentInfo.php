<?php
include_once dirname(__FILE__) . "/../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../util/toolTime.php";
include_once dirname(__FILE__) . "/../../util/toolForAgent.php";
include_once dirname(__FILE__) . "/../../util/toolRunTime.php";
include_once dirname(__FILE__) . "/../../util/logger.php";

class AgentInfo {
    /**
     * 返回代理商个人信息
     * @param $agentId 代理商id
     * @return array|null
     */
    static function myInfo($agentId) {
        $myInfoSql = "
        select
		myinfo.id,
		myinfo.level,
		myinfo.username,
		myinfo.profit,
		pInfo.tel as parent_tel
	    from ".Config::SQL_DB.".agentusers as myinfo left join ".Config::SQL_DB.".agentusers as pInfo on myinfo.parent_id = pInfo.id where myinfo.id = '$agentId' ";

        $myInfoResult = ToolMySql::query($myInfoSql);
        $totalResult = self::getPlayerAgentTotal($agentId);

        $data = array_merge($myInfoResult->fetch_assoc(), $totalResult);
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


