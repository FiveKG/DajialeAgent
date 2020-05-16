<?php
include_once dirname(__FILE__) . "/../../util/toolTime.php";
include_once dirname(__FILE__) . "/../../util/toolRedis.php";
include_once dirname(__FILE__) . "/../../util/toolForGame.php";
include_once dirname(__FILE__) . "/../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../player/player.php";
include_once dirname(__FILE__) . "/../../config.php";
include_once dirname(__FILE__) . "/../../util/dajialeService.php";

class Charge {
    /**
     * 返回充值记录
     * @param $startDate
     * @param $endDate
     * @param $page
     * @param $limit
     * @param $tradeNo
     * @return array
     */
    static function chargeList($startDate, $endDate, $page, $limit, $tradeNo) {
        $data = array('total'=>0, 'charge_total'=>0,'list'=>array());
        if ($startDate && $endDate) {
            $startDate = ToolTime::strToUtc($startDate) *1000;
            $endDate = ToolTime::getOneDayEndSec(ToolTime::strToUtc($endDate)) *1000;
        }

        $chargeList = DajialeService::rechargePage($startDate,$endDate,$page,$limit,'',$tradeNo);

        $data['total'] = $chargeList['total'];
        foreach ($chargeList['records'] as $chargeInfo) {
            //由于大家乐接口没有提供玩家信息，需要自己从redis拉取用户信息
            $username = '-';
            //如果是承接商下载充值是没有userId的
            if($chargeInfo['userId']) {
                $username = DbPlayerInfo::hGet( $chargeInfo['userId'], "username");
                if (!$username) {
                    //同步游戏服务端和自身服务器的玩家数据，并推送到redis
                    $checkResult = toolForGame::checkPlayer2Redis($chargeInfo['userId']);
                    if ($checkResult === true)
                        $username = DbPlayerInfo::hGet( $chargeInfo['userId'], "username");
                    else {
                        $username = $checkResult;
                    }
                }
            }
            $temp = array();
            $temp['tradeNo'] = $chargeInfo['tradeNo'];
            $temp['createTime'] = date('Y-m-d H:i',$chargeInfo['createTime']/1000);
            $temp['userId'] = $chargeInfo['userId'];;
            $temp['username'] = $username;
            $temp['amount'] = $chargeInfo['amount'];
            $temp['subject'] = $chargeInfo['subject'];
            $temp['mode'] = DajialeService::$modeType[$chargeInfo['mode']];
            $temp['status'] = $chargeInfo['remark'];

            //1待支付 2充值处理中 3充值成功 4充值失败
            if($chargeInfo['status'] === 3)
                $data['charge_total'] += $chargeInfo['amount'];
            $data['list'][] = $temp;
        }

        $data['total'] = (int)$data['total'];
        return $data;
    }
}