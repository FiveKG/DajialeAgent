<?php
include_once dirname(__FILE__) . "/../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../util/toolTime.php";
include_once dirname(__FILE__) . "/../../util/toolForAgent.php";
include_once dirname(__FILE__) . "/../../util/toolRunTime.php";
include_once dirname(__FILE__) . "/../../util/logger.php";

class ChangePassword {
    /**
     * 更改密码
     * @param $agentId
     * @param $old
     * @param $new
     * @return bool|string */
    static function  changePwd($agentId, $old, $new) {
        // $oldPasswordHash = password_hash($old, PASSWORD_DEFAULT);
        $getAgentSql = "select password from ".Config::SQL_DB.".agentusers where id = '$agentId'";
        $oldPwdResult = ToolMySql::query($getAgentSql);
        $oldPwd = $oldPwdResult->fetch_assoc()['password'];

        $isRight = password_verify ( $old, $oldPwd);
        if ($isRight) {
            $newPasswordHash = password_hash($new, PASSWORD_DEFAULT);
            $updateSql = "update ".Config::SQL_DB.".agentusers set password = '$newPasswordHash' where id = '$agentId'";
            $result = ToolMySql::query($updateSql);
            if (!$result)
                return '更新失败';
        }
        else
            return '密码错误';
        return true;
    }
}