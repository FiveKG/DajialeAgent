<?php
include_once dirname(__FILE__) . "/../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../util/toolTime.php";
include_once dirname(__FILE__) . "/../../util/toolForAgent.php";
include_once dirname(__FILE__) . "/../../util/toolRunTime.php";
include_once dirname(__FILE__) . "/../../util/logger.php";

class GetAgentApplyList {
    /**
     * 返回代理商审核列表
     * @param $agentId
     * @return array
     */
    static function getApplyList($agentId) {
        $data = array();
        $findApplySql = "select id, username, level, tel, create_at from ".Config::SQL_DB.".agentusers where parent_id ='$agentId' and status = '0'";

        $findApplyResult = ToolMySql::query($findApplySql);
        while ($row = $findApplyResult->fetch_assoc()) {
            $temp = array();
            $temp['id'] = $row['id'];
            $temp['username'] = $row['username'];
            $temp['level'] = $row['level'];
            $temp['tel'] = $row['tel'];
            $temp['create_at'] = $row['create_at'];
            $data[] = $temp;
        }
        return $data;
    }
}