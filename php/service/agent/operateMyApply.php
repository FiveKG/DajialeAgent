<?php
include_once dirname(__FILE__) . "/../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../util/toolTime.php";
include_once dirname(__FILE__) . "/../../util/toolForAgent.php";
include_once dirname(__FILE__) . "/../../util/toolRunTime.php";
include_once dirname(__FILE__) . "/../../util/logger.php";

class OperateMyApply {
    /**
     * 审核代理商
     * @param $agentId
     * @param $type
     * @param $note
     * @return bool
     */
    static function operateApply($cid, $type, $note) {
        //开启事务
        ToolMySql::setAutocommit(false);

        //先更新代理状态，才能把玩家代理到该代理下
        $operateSql = "update ".Config::SQL_DB.".agentusers set status = 1, note ='$note', apply_at =now() where id = '$cid'";
        if ($type === "refuse")
            $operateSql = "update ".Config::SQL_DB.".agentusers set status = -1, note ='$note', apply_at =now() where id = '$cid'";

        //更新状态
        if (ToolMySql::query($operateSql) !== true) {
            ToolMySql::rollback();
            return "更新代理商状态出错";
        }

        //查询该代理下是否已经存在游戏账号,默认归属到自己代理下
        if ($type === "agree") {
            $findAgentSql = "select tel from ".Config::SQL_DB.".agentusers where id = '$cid'";
            $findAgentResult = ToolMySql::query($findAgentSql);
            if (!$findAgentResult) {
                ToolMySql::rollback();
                return "数据库查找出错";
            }
            $agentTel = $findAgentResult->fetch_assoc()['tel'];

            //查找玩家信息
            $findPlayerSql = "select id from ".Config::SQL_DB.".playerusers where tel = '$agentTel'";
            $findPlayerResult = ToolMySql::query($findPlayerSql);

            if ($findPlayerResult->num_rows === 1) {
                $userId = $findPlayerResult->fetch_assoc()['id'];
                $updatePlayerSql = "update ".Config::SQL_DB.".playerusers set parent_id = '$cid' where id ='$userId'";
                $updatePlayerResult = ToolMySql::query($updatePlayerSql);
                if (!$updatePlayerResult) {
                    ToolMySql::rollback();
                    return "更新玩家代理出错";
                }
            }
        }
        ToolMySql::commit();
        ToolMySql::setAutocommit(true);
        return true;
    }
}