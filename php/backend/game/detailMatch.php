<?php
include_once dirname(__FILE__) . "/../../util/postJson.php";
include_once dirname(__FILE__) . "/../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../util/statusCode.php";
include_once dirname(__FILE__) . "/../../service/game.php";
include_once dirname(__FILE__) . "/../../util/jwt.php";
try {
    $jsonPost = new GamePostOrGetJson();
    if (!$TokenResult = Jwt::verifyToken())
        StatusCode::responseError(StatusCode::SYS_TOKEN_VERIFY_FAIL, "token验证失败");

    $localeId =  $jsonPost->getStr("localeId");
    $type = $jsonPost->getStr("type");
    $types = array("casino", "race", "award", "matchValidator");

    if(!$localeId || ! $type)
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, "缺少参数");
    if(!is_numeric($localeId))
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, "localeId参数错误");
    if (!in_array($type, $types))
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, "type参数错误");

    ToolMySql::conn();
    $result = Game::detailMatch($localeId, $type);
    StatusCode::responseSuccess(StatusCode::SUCCESS, $result);

    ToolMySql::close();
} catch (Exception $e) {
    echo $e->getMessage();
}