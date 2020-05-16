<?php
include_once dirname(__FILE__) . "/../../../util/postJson.php";
include_once dirname(__FILE__) . "/../../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../../util/statusCode.php";
include_once dirname(__FILE__) . "/../../../service/promotion.php";
include_once dirname(__FILE__) . "/../../../util/jwt.php";
include_once dirname(__FILE__) . "/../../../util/checkParam.php";

try {
    $jsonPost = new GamePostOrGetJson();

    if (!$TokenResult = Jwt::verifyToken()) {
        StatusCode::responseError(StatusCode::SYS_TOKEN_VERIFY_FAIL, "token验证失败");
    }

    ToolMySql::conn();
    $data = Promotion::getConfig();
    StatusCode::responseSuccess(StatusCode::SUCCESS, $data);
    ToolMySql::close();

} catch (exception $e) {
    echo $e->getMessage();
}