<?php
include_once dirname(__FILE__) . "/../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../util/toolTime.php";
include_once dirname(__FILE__) . "/../../util/toolForAgent.php";
include_once dirname(__FILE__) . "/../../util/toolRunTime.php";
include_once dirname(__FILE__) . "/../../util/logger.php";

class SetSubProfit {

    /**
     * 修改代理商的收益比例
     * @param $MyAgentId
     * @param $agentId
     * @param $num
     * @return bool|mysqli_result
     */
    static function setProfit($MyAgentId,$agentId, $num) {
        $getProfit = "select profit from ".Config::SQL_DB.".agentusers where id = '$MyAgentId'";
        $result = ToolMySql::query($getProfit);
        $myProfit = (int)$result->fetch_assoc()['profit'];
        if ((int)$num  > $myProfit)
            return '分成比例不能大于自身';
        $sql = "update ".Config::SQL_DB.".agentusers  set profit = '$num' where id = '$agentId'";
        return $result = ToolMySql::query($sql);
    }

}