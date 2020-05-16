<?php
include_once dirname(__FILE__) . "/../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../util/toolTime.php";
include_once dirname(__FILE__) . "/../../util/toolForAgent.php";
include_once dirname(__FILE__) . "/../../util/toolRunTime.php";
include_once dirname(__FILE__) . "/../../util/logger.php";

class ResetPwdBySup {

    /**
     * 上级重置下级密码
     * @param $myAgentId
     * @param $subAgentId
     * @param $password
     * @return bool|mysqli_result|string
     */
    static function  resetPwd($myAgentId, $subAgentId, $password) {
        $subAgentInfo = ToolForAgent::findSupAgent($subAgentId);
        if($myAgentId!== $subAgentInfo['parent_id'] && $myAgentId!==$subAgentInfo['level3']) {
            return "该代理不属于你的下级,无法重置密码";
        }
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $updateSql = "update ".Config::SQL_DB.".agentusers set password = '$passwordHash' where id = '$subAgentId'; ";
        return ToolMySql::query($updateSql);
    }

}