<?php
/**
 * 上级代理重置下级密码
 */
include_once dirname(__FILE__) . "/../../util/postJson.php";
include_once dirname(__FILE__) . "/../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../util/statusCode.php";
include_once dirname(__FILE__) . "/../../service/agent/resetPwdBySup.php";
include_once dirname(__FILE__) . "/../../util/jwt.php";

try {
    if (!$TokenResult = Jwt::verifyToken()) {
        StatusCode::responseError(StatusCode::SYS_TOKEN_VERIFY_FAIL, "token验证失败");
    }
    $jsonPost = new GamePostOrGetJson();
    $agentId = $jsonPost->getStr("agentId");
    $password = $jsonPost->getStr("password");

    if (!$agentId || !$password )
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, "缺少参数");

    if(strlen($password) < 6)
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, "密码至少6位数");

    $myAgentId = $TokenResult['agentId'];
    ToolMySql::conn();
    $data = ResetPwdBySup::resetPwd($myAgentId, $agentId, $password);
    if ($data !== true)
        StatusCode::responseError(StatusCode::SYS_USER_REGISTER_ERROR, $data);
    StatusCode::responseSuccess(StatusCode::SUCCESS, $data);
    ToolMySql::close();

} catch (Exception $e) {
    echo $e->getMessage();
}