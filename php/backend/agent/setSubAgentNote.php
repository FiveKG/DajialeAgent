<?php
/**
 * 上级代理重置下级备注
 */
include_once dirname(__FILE__) . "/../../util/postJson.php";
include_once dirname(__FILE__) . "/../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../util/statusCode.php";
include_once dirname(__FILE__) . "/../../service/agent/setSubProfit.php";
include_once dirname(__FILE__) . "/../../util/jwt.php";

try {
    if (!$TokenResult = Jwt::verifyToken()) {
        StatusCode::responseError(StatusCode::SYS_TOKEN_VERIFY_FAIL, "token验证失败");
    }
    $jsonPost = new GamePostOrGetJson();
    $agentId = $jsonPost->getStr("agentId");
    $note = $jsonPost->getStr("note");

    if (!$agentId)
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, "缺少参数");

    $myAgentId = $TokenResult['agentId'];
    ToolMySql::conn();
    $data = SetSubAgentNote::setNote2SubAgent($myAgentId, $agentId, $note);
    if ($data !== true)
        StatusCode::responseError(StatusCode::SYS_USER_REGISTER_ERROR, $data);
    StatusCode::responseSuccess(StatusCode::SUCCESS, $data);
    ToolMySql::close();

} catch (Exception $e) {
    echo $e->getMessage();
}