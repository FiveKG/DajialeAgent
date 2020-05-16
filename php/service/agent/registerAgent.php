<?php
include_once dirname(__FILE__) . "/../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../util/toolTime.php";
include_once dirname(__FILE__) . "/../../util/toolForAgent.php";
include_once dirname(__FILE__) . "/../../util/toolRunTime.php";
include_once dirname(__FILE__) . "/../../util/logger.php";

class RegisterAgent {
    /**
     * 注册代理商
     * @param $pid
     * @param $username
     * @param $password
     * @param $tel
     * @return bool|int|string
     */
    static function register($pid, $username, $password, $tel) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $findPidSql = "select id,level,status,parent_id from ".Config::SQL_DB.".agentusers where id = '$pid'";
        $findPidResult = ToolMySql::query($findPidSql);
        $findPid = $findPidResult->fetch_assoc();
        $pLevel = $findPid['level'];
        $id =  $findPid['id'];
        $pStatus = $findPid['status'];
        $pParen_id = $findPid['parent_id'];
        $level3 = '';
        if (!$id)
            return '此代理不存在';
        if ((int)$pLevel ===3) {
            return '此代理不可再生成下级代理';
        }

        $level = ((int)$pLevel)+1;
        if ((int)$pStatus !== 1)
            return '此代理状态不正常';

        $findUsernameSql = "select id from ".Config::SQL_DB.".agentusers where username = '$username'";
        $findUsernameResult = ToolMySql::query($findUsernameSql);
        $row = $findUsernameResult->num_rows;
        if ($row)
            return '用户名已存在';

        $findTelSql = "select id from ".Config::SQL_DB.". agentusers where tel = '$tel'";
        $findTelResult = ToolMySql::query($findTelSql);
        $row = $findTelResult->num_rows;
        if ($row)
            return '电话号码已存在';
        if ((int)$pLevel === 2) {
            $level3 = $pParen_id;
        }
        $registerSql = "INSERT INTO `".Config::SQL_DB."`.`agentusers`
                        (`username`,`password`,`parent_id`,`level`,`level3`,`status`,`tel`)
                    VALUES
                        ('$username','$passwordHash','$pid','$level','$level3','0','$tel')";
        if (ToolMySql::query($registerSql)  !== true) {
            return false;
        }
        return true;
    }
}