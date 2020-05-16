<?php

include_once dirname(__FILE__) . "/../../util/postJson.php";
include_once dirname(__FILE__) . "/../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../util/toolToken.php";
include_once dirname(__FILE__) . "/../../util/toolTime.php";
include_once dirname(__FILE__) . "/../../util/statusCode.php";
include_once dirname(__FILE__) . "/../../util/toolForAgent.php";
include_once dirname(__FILE__) . "/../../service/agent/agentInfo.php";
include_once dirname(__FILE__) . "/../../util/jwt.php";
try {
    if (!$TokenResult = Jwt::verifyToken()) {
        StatusCode::responseError(StatusCode::SYS_TOKEN_VERIFY_FAIL, "token验证失败");
    }
    $agentId = $TokenResult['agentId'];
    $data = array();
    ToolMySql::conn();

//    $startTime = microtime(true);
    // 我的个人信息
    $data["myInfo"] = AgentInfo::myInfo($agentId);
    // 我的玩家信息
    $myPlayerInfo = array();
    $total = ToolForAgent::getPlayerTotal2($agentId);
    $thisMonthTotal = toolForAgent::getPlayerCCByDate($agentId, ToolTime::getMonth(),"all");
    $lastMonthTotal = toolForAgent::getPlayerCCByDate($agentId, ToolTime::getLastMonth(), "all");
    $todayTotal =  toolForAgent::getPlayerCCByDate($agentId, ToolTime::getToday(),"all");
    $yesterdayTotal = toolForAgent::getPlayerCCByDate($agentId, ToolTime::getYesterday(), "all");

    //玩家总额
    $myPlayerInfo["playerTotal"]['charge_total'] = round($total['charge_total'],2);
    $myPlayerInfo["playerTotal"]['consume_total'] = round($total['consume_total'],2);
    $myPlayerInfo["playerTotal"]['fast_consume_total'] = round($total['fast_consume_total'],2);
    $myPlayerInfo["playerTotal"]['other_consume_total'] = round($total['other_consume_total'],2);
    //玩家今日
    $myPlayerInfo["playerTodayTotal"]['charge_total'] = round($todayTotal['charge_total'],2);
    $myPlayerInfo["playerTodayTotal"]['consume_total'] = round($todayTotal['consume_total'],2);
    $myPlayerInfo["playerTodayTotal"]['fast_consume_total'] = round($todayTotal['fast_consume_total'],2);
    $myPlayerInfo["playerTodayTotal"]['other_consume_total'] = round($todayTotal['other_consume_total'],2);
    //玩家昨天
    $myPlayerInfo["playerYesterdayTotal"]['charge_total'] = round($yesterdayTotal['charge_total'],2);
    $myPlayerInfo["playerYesterdayTotal"]['consume_total'] = round($yesterdayTotal['consume_total'],2);
    $myPlayerInfo["playerYesterdayTotal"]['fast_consume_total'] = round($yesterdayTotal['fast_consume_total'],2);
    $myPlayerInfo["playerYesterdayTotal"]['other_consume_total'] = round($yesterdayTotal['other_consume_total'],2);
    //玩家这个月
    $myPlayerInfo["playerThisMonthTotal"]['charge_total'] = round($thisMonthTotal['charge_total'] , 2);
    $myPlayerInfo["playerThisMonthTotal"]['consume_total'] = round($thisMonthTotal['consume_total'] , 2);
    $myPlayerInfo["playerThisMonthTotal"]['fast_consume_total'] = round($thisMonthTotal['fast_consume_total'] , 2);
    $myPlayerInfo["playerThisMonthTotal"]['other_consume_total'] = round($thisMonthTotal['other_consume_total'] , 2);
    //玩家上个月
    $myPlayerInfo["playerLastMonthTotal"]['charge_total'] = round($lastMonthTotal['charge_total'],2);
    $myPlayerInfo["playerLastMonthTotal"]['consume_total'] =round($lastMonthTotal['consume_total'],2);
    $myPlayerInfo["playerLastMonthTotal"]['fast_consume_total'] =round($lastMonthTotal['fast_consume_total'],2);
    $myPlayerInfo["playerLastMonthTotal"]['other_consume_total'] =round($lastMonthTotal['other_consume_total'],2);

    $data['myPlayerInfo'] = $myPlayerInfo;
    // 下级代理
    $myAgentsInfo = array();
    $total = toolForAgent::getAgentTotal($agentId);
    $agentThisMonthTotal = toolForAgent::getAgentCCByDate($agentId, ToolTime::getMonth(), "all");
    $agentLastMonthTotal = toolForAgent::getAgentCCByDate($agentId, ToolTime::getLastMonth(), "all");
    $agentTodayTotal =  toolForAgent::getAgentCCByDate($agentId, ToolTime::getToday(), "all");
    $agentYesterdayTotal = toolForAgent::getAgentCCByDate($agentId, ToolTime::getYesterday(), "all");

    //代理总额
    $myAgentsInfo["AgentTotal"]['charge_total'] = round($total['charge_total'],2);
    $myAgentsInfo["AgentTotal"]['consume_total'] = round($total['consume_total'],2);
    $myAgentsInfo["AgentTotal"]['fast_consume_total'] = round($total['fast_consume_total'],2);
    $myAgentsInfo["AgentTotal"]['other_consume_total'] = round($total['other_consume_total'],2);
    //代理今日
    $myAgentsInfo["AgentTodayTotal"]['charge_total'] = round($agentTodayTotal['charge_total'],2);
    $myAgentsInfo["AgentTodayTotal"]['consume_total'] = round($agentTodayTotal['consume_total'],2);
    $myAgentsInfo["AgentTodayTotal"]['fast_consume_total'] = round($agentTodayTotal['fast_consume_total'],2);
    $myAgentsInfo["AgentTodayTotal"]['other_consume_total'] = round($agentTodayTotal['other_consume_total'],2);
    //代理昨日
    $myAgentsInfo["AgentYesterdayTotal"]['charge_total'] = round($agentYesterdayTotal['charge_total'],2);
    $myAgentsInfo["AgentYesterdayTotal"]['consume_total'] = round($agentYesterdayTotal['consume_total'],2);
    $myAgentsInfo["AgentYesterdayTotal"]['fast_consume_total'] = round($agentYesterdayTotal['fast_consume_total'],2);
    $myAgentsInfo["AgentYesterdayTotal"]['other_consume_total'] = round($agentYesterdayTotal['other_consume_total'],2);
    //代理这个月
    $myAgentsInfo["AgentThisMonthTotal"]['charge_total'] = round($agentThisMonthTotal['charge_total'],2);
    $myAgentsInfo["AgentThisMonthTotal"]['consume_total'] = round($agentThisMonthTotal['consume_total'],2);
    $myAgentsInfo["AgentThisMonthTotal"]['fast_consume_total'] = round($agentThisMonthTotal['fast_consume_total'],2);
    $myAgentsInfo["AgentThisMonthTotal"]['other_consume_total'] = round($agentThisMonthTotal['other_consume_total'],2);
    //代理上个月
    $myAgentsInfo["AgentLastMonthTotal"]['charge_total'] = round($agentLastMonthTotal['charge_total'],2);
    $myAgentsInfo["AgentLastMonthTotal"]['consume_total'] = round($agentLastMonthTotal['consume_total'],2);
    $myAgentsInfo["AgentLastMonthTotal"]['fast_consume_total'] = round($agentLastMonthTotal['fast_consume_total'],2);
    $myAgentsInfo["AgentLastMonthTotal"]['other_consume_total'] = round($agentLastMonthTotal['other_consume_total'],2);

    $data['myAgentsInfo'] = $myAgentsInfo;

    StatusCode::responseSuccess(StatusCode::SUCCESS, $data);
    ToolMySql::close();

} catch (Exception $e) {
    echo $e->getMessage();
}

