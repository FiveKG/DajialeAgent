<?php
include_once dirname(__FILE__) . "/../../util/postJson.php";
include_once dirname(__FILE__) . "/../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../util/statusCode.php";
include_once dirname(__FILE__) . "/../../service/game.php";
include_once dirname(__FILE__) . "/../../util/checkParam.php";
include_once dirname(__FILE__) . "/../../util/jwt.php";
try {
    $jsonPost = new GamePostOrGetJson();
    $count = $jsonPost->getStr("count");
    $type = $jsonPost->getStr("type");

    if (!$TokenResult = Jwt::verifyToken()) {
        StatusCode::responseError(StatusCode::SYS_TOKEN_VERIFY_FAIL, "token验证失败");
    }

    if (!$count || !$type)
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, '缺少参数');
    if (!is_numeric($count))
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, 'count格式不对');
    if (!is_numeric($type))
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, 'type格式不对');

    ToolMySql::conn();
    $data = Game::generateTicket($count, $type);
    if(!$data)
        StatusCode::responseError(StatusCode::SYS_MYSQL_QUERY_ERROR, $data);
    ToolMySql::close();
    StatusCode::responseSuccess(StatusCode::SUCCESS, $data);
} catch (Exception $e) {
    echo $e->getMessage();
}