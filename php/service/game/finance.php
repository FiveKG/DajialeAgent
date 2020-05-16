<?php
include_once dirname(__FILE__) . "/../../util/toolTime.php";
include_once dirname(__FILE__) . "/../../util/toolRedis.php";
include_once dirname(__FILE__) . "/../../util/toolNet.php";
include_once dirname(__FILE__) . "/../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../player/player.php";
include_once dirname(__FILE__) . "/../../config.php";
include_once dirname(__FILE__) . "/../../util/dajialeService.php";

class Finance {
    /**
     * 财务概况-从大家乐拉取
     * @param $startDate
     * @param $endDate
     * @param $page
     * @param $limit
     * @param $userId
     * @return array|bool
     */
    static function financeSummary($startDate, $endDate, $page, $limit) {
        $start = ($page-1)*$limit;
        $dateList = ToolTime::getDateFromRange($startDate,$endDate);
        $data = array('total'=>sizeof($dateList), 'charge_total'=>0,'charge_number'=>0,'charge_count'=>0,'serviceFee'=>0,'list'=>array());

        foreach ($dateList as $date) {
            $startDate = ToolTime::strToUtc($date) *1000;
            $endDate = ToolTime::getOneDayEndSec(ToolTime::strToUtc($date)) *1000;
            $startDateString = $date. " 00:00:00";
            $endDateString = $data. " 23:59:59";

            $temp = array();
            $temp['date'] = $date;
            $temp['charge_total'] = 0;
            $temp["serviceFee"] = 0;
            $temp["charge_number"] = 0;
            $temp['charge_count'] = 0;
            $temp['leQuan_total'] = '-';
            $temp['award_total'] = '-';

            //充值总额
            $temp['charge_total'] = DajialeService::getRechargeTotal($startDate, $endDate);
            $data['charge_total'] += $temp['charge_total'];

            //消费总额,时间格式是字符串，大家乐的坑
            $temp["serviceFee"] = DajialeService::getConsumeTotal($startDateString, $endDateString)['serviceFee'];
            $data['serviceFee'] += $temp["serviceFee"];

            //充值人数
            $temp['charge_number'] = DajialeService::pageRecharge(3,1,$startDate, $endDate)['total'];
            $data['charge_number'] += $temp['charge_number'];

            //充值次数
            //($startDate, $endDate, $page, $limit, $userId ="")
            $temp['charge_count'] = DajialeService::rechargePage($startDate,$endDate,1, 1)['total'];
            $data['charge_count'] += $temp['charge_count'];

            $data['list'][]=$temp;
        }
        $data['serviceFee'] = round($data['serviceFee'],2);
        $data['list'] = array_reverse($data['list']);//倒序
        $data['list'] = array_slice($data['list'],$start,$limit);

        $data['total'] = (int)$data['total'];
        return $data;
    }
}