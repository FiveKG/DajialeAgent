<?php
/**
 * 分页返回我的代理商信息
 */
include_once dirname(__FILE__) . "/../../util/postJson.php";
include_once dirname(__FILE__) . "/../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../util/statusCode.php";
include_once dirname(__FILE__) . "/../../service/agent/getAgentApplyList.php";
include_once dirname(__FILE__) . "/../../util/jwt.php";;

try {
    $jsonPost = new GamePostOrGetJson();

    if (!$TokenResult = Jwt::verifyToken()) {
        StatusCode::responseError(StatusCode::SYS_TOKEN_VERIFY_FAIL, "token验证失败");
    }
    $agentId = $TokenResult['agentId'];
    ToolMySql::conn();
    $data = GetAgentApplyList::getApplyList($agentId);
    StatusCode::responseSuccess(StatusCode::SUCCESS, $data);
    ToolMySql::close();

} catch (Exception $e) {
    echo $e->getMessage();
}