<?php
/**
 * 按时间区间获取我的承办方总览
 */
include_once dirname(__FILE__) . "/../../util/postJson.php";
include_once dirname(__FILE__) . "/../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../util/statusCode.php";
include_once dirname(__FILE__) . "/../../service/agent/changePassword.php";
include_once dirname(__FILE__) . "/../../util/jwt.php";
include_once dirname(__FILE__) . "/../../util/checkParam.php";

try {
    $jsonPost = new GamePostOrGetJson();
    $old = $jsonPost->getStr("old");
    $new = $jsonPost->getStr("new");

    if (!$TokenResult = Jwt::verifyToken()) {
        StatusCode::responseError(StatusCode::SYS_TOKEN_VERIFY_FAIL, "token验证失败");
    }

    if (!$old)
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, "原密码不能为空");
    if (!$new)
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, "新密码不能为空");

    $agentId = $TokenResult['agentId'];
    ToolMySql::conn();
    $data = ChangePassword::changePwd($agentId,$old, $new);
    if ($data === true)
        StatusCode::responseError(StatusCode::SUCCESS, '更新成功');
    else
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, $data);

    ToolMySql::close();

} catch (exception $e) {
    echo $e->getMessage();
}