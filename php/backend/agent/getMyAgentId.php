<?php
/**
 * 分页返回我的代理商信息
 */
include_once dirname(__FILE__) . "/../../util/postJson.php";
include_once dirname(__FILE__) . "/../../util/statusCode.php";
include_once dirname(__FILE__) . "/../../util/jwt.php";;

try {
    $jsonPost = new GamePostOrGetJson();

    if (!$TokenResult = Jwt::verifyToken()) {
        StatusCode::responseError(StatusCode::SYS_TOKEN_VERIFY_FAIL, "token验证失败");
    }

    $agentId = $TokenResult['agentId'];
    StatusCode::responseSuccess(StatusCode::SUCCESS, $agentId);

} catch (Exception $e) {
    echo $e->getMessage();
}