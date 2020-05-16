<?php
/**
 * 分页返回我的代理商信息
 */
include_once dirname(__FILE__) . "/../../util/postJson.php";
include_once dirname(__FILE__) . "/../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../util/statusCode.php";
include_once dirname(__FILE__) . "/../../service/agent/getAgentList.php";
include_once dirname(__FILE__) . "/../../util/jwt.php";;
include_once dirname(__FILE__) . "/../../util/checkParam.php";

try {
    $jsonPost = new GamePostOrGetJson();
    $page = $jsonPost->getStr("page");
    $limit = $jsonPost->getStr("limit");

    if (!$TokenResult = Jwt::verifyToken()) {
        StatusCode::responseError(StatusCode::SYS_TOKEN_VERIFY_FAIL, "token验证失败");
    }

    $pageLimitResult = CheckParam::checkPageLimit($page, $limit);
    if ($pageLimitResult === false)
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, "page/limit错误");

    $agentId = $TokenResult['agentId'];
    ToolMySql::conn();
    $data = GetAgentList::getMyAgentList($agentId, $pageLimitResult['page'], $pageLimitResult['limit']);
    StatusCode::responseSuccess(StatusCode::SUCCESS, $data);
    ToolMySql::close();

} catch (Exception $e) {
    echo $e->getMessage();
}