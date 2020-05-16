<?php
/**
 * 代理商登出功能
 */
include_once dirname(__FILE__) . "/../../util/postJson.php";
include_once dirname(__FILE__) . "/../../util/toolRedis.php";
include_once dirname(__FILE__) . "/../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../util/statusCode.php";
include_once dirname(__FILE__) . "/../../util/jwt.php";

try {
    if (!$TokenResult = Jwt::verifyToken()) {
        StatusCode::responseError(StatusCode::SYS_TOKEN_VERIFY_FAIL, "token验证失败");
    }
    $id = $TokenResult['id'];
    ToolRedis::get()->hDel("backend:admintoken:h", $id);
    StatusCode::responseError(StatusCode::SUCCESS, true);
} catch (exception $e) {
    echo $e->getMessage();
}