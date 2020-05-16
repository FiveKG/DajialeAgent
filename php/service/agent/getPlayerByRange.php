<?php
include_once dirname(__FILE__) . "/../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../util/toolTime.php";
include_once dirname(__FILE__) . "/../../util/toolForAgent.php";
include_once dirname(__FILE__) . "/../../util/toolRunTime.php";
include_once dirname(__FILE__) . "/../../util/logger.php";

class GetPlayerByRange {
    /**
     * 返回某个时间段的消耗/充值总额
     * @param $agentId
     * @param $startDate
     * @param $endDate
     * @param $type
     * @return array
     */
    static function getMyPlayerByRange($agentId, $startDate, $endDate, $type) {
        $data = array("charge_total"=>0, "consume_total"=>0);
        $dateRange = ToolTime::getDateFromRange($startDate, $endDate);
        for ($i=0; $i < sizeof($dateRange); $i++) {
            $result = ToolForAgent::getPlayerCCByDate($agentId, $dateRange[$i], $type);
            $data['charge_total'] += $result['charge_total'];
            $data['consume_total'] += $result['consume_total'];
        }
        $data['charge_total'] = round($data['charge_total'], 2);
        $data['consume_total'] = round($data['consume_total'], 2);
        return $data;
    }
}