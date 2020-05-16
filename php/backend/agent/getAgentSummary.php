<?php
/**
 * 按时间区间获取我的承办方总览
 */
include_once dirname(__FILE__) . "/../../util/postJson.php";
include_once dirname(__FILE__) . "/../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../util/statusCode.php";
include_once dirname(__FILE__) . "/../../service/agent/getAgentSummary.php";
include_once dirname(__FILE__) . "/../../util/jwt.php";
include_once dirname(__FILE__) . "/../../util/checkParam.php";

try {
    $jsonPost = new GamePostOrGetJson();
    $startDate = $jsonPost->getStr("startDate");
    $endDate = $jsonPost->getStr("endDate");
    $page = $jsonPost->getStr("page");
    $limit = $jsonPost->getStr("limit");

    if (!$TokenResult = Jwt::verifyToken()) {
        StatusCode::responseError(StatusCode::SYS_TOKEN_VERIFY_FAIL, "token验证失败");
    }
    $dateResult = CheckParam::checkSEDate($startDate, $endDate);
    if (!$dateResult['startDate'] || !$dateResult )
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, "日期错误");

    $pageLimitResult = CheckParam::checkPageLimit($page, $limit);
    if ($pageLimitResult === false)
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, "page/limit错误");

    ToolMySql::conn();
    $data = GetAgentSummary::getSummary($dateResult['startDate'], $dateResult['endDate'], $pageLimitResult['page'], $pageLimitResult['limit']);
    StatusCode::responseSuccess(StatusCode::SUCCESS, $data);
    ToolMySql::close();

} catch (exception $e) {
    echo $e->getMessage();
}