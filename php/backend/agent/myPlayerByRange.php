<?php
include_once dirname(__FILE__) . "/../../util/postJson.php";
include_once dirname(__FILE__) . "/../../util/toolTime.php";
include_once dirname(__FILE__) . "/../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../util/statusCode.php";
include_once dirname(__FILE__) . "/../../service/agent/getPlayerByRange.php";
include_once dirname(__FILE__) . "/../../util/jwt.php";
include_once dirname(__FILE__) . "/../../util/checkParam.php";

try {
    $jsonPost = new GamePostOrGetJson();
    $startDate = $jsonPost->getStr("startDate");
    $endDate = $jsonPost->getStr("endDate");
    $requestType = $jsonPost->getStr("type");
    $type = array("fission", "direct", "all");

    if (!$TokenResult = Jwt::verifyToken()) {
        StatusCode::responseError(StatusCode::SYS_TOKEN_VERIFY_FAIL, "token验证失败");
    }

    if (!in_array($requestType, $type)) {
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, "参数type错误");
    }

    $dateResult = CheckParam::checkSEDate($startDate, $endDate);
    if (!$dateResult['startDate'])
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, "日期错误");

    $agentId = $TokenResult['agentId'];
    ToolMySql::conn();
    $data = GetPlayerByRange::getMyPlayerByRange($agentId,  $dateResult['startDate'], $dateResult['endDate'], $requestType);
    ToolMySql::close();
    StatusCode::responseSuccess(StatusCode::SUCCESS, $data);

} catch (Exception $e) {
    echo $e->getMessage();
}