<?php
include_once dirname(__FILE__) . "/../../../util/postJson.php";
include_once dirname(__FILE__) . "/../../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../../util/statusCode.php";
include_once dirname(__FILE__) . "/../../../service/promotion.php";
include_once dirname(__FILE__) . "/../../../util/jwt.php";
include_once dirname(__FILE__) . "/../../../util/checkParam.php";

try {
    $jsonPost = new GamePostOrGetJson();
    $chargeTotal = $jsonPost->getStr("chargeTotal");
    $myChargeTotal = $jsonPost->getStr("myChargeTotal");
    $count = $jsonPost->getStr("count");
    $minGetPrize = $jsonPost->getStr("minGetPrize");

    if (!$TokenResult = Jwt::verifyToken()) {
        StatusCode::responseError(StatusCode::SYS_TOKEN_VERIFY_FAIL, "token验证失败");
    }

    if (!is_numeric($chargeTotal))
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, "chargeTotal格式错误");
    if (!is_numeric($myChargeTotal))
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, "myChargeTotal格式错误");
    if (!is_numeric($count))
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, "count格式错误");
    if (!is_numeric($minGetPrize))
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, "minGetPrize格式错误");

    ToolMySql::conn();
    $data = Promotion::setConfig($chargeTotal, $myChargeTotal, $count, $minGetPrize);
    StatusCode::responseSuccess(StatusCode::SUCCESS, $data);
    ToolMySql::close();

} catch (exception $e) {
    echo $e->getMessage();
}