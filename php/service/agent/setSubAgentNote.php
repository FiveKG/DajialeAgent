<?php
include_once dirname(__FILE__) . "/../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../util/toolTime.php";
include_once dirname(__FILE__) . "/../../util/toolForAgent.php";
include_once dirname(__FILE__) . "/../../util/toolRunTime.php";
include_once dirname(__FILE__) . "/../../util/logger.php";

class SetSubAgentNote {
    /**
     * 上级给下级设置备注
     * @param $myAgentId
     * @param $subAgentId
     * @param $note
     * @return bool|mysqli_result|string
     */
    static function setNote2SubAgent($myAgentId, $subAgentId, $note) {
        $subAgentInfo = ToolForAgent::findSupAgent($subAgentId);
        if($myAgentId!== $subAgentInfo['parent_id'] && $myAgentId!==$subAgentInfo['level3']) {
            return "该代理不属于你的下级,无法修改备注";
        }

        $update = "update ".Config::SQL_DB.".agentusers set note = '$note' where id = '$subAgentId';";
        return ToolMySql::query($update);
    }
}