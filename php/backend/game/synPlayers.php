<?php
include_once dirname(__FILE__) . "/../../util/postJson.php";
include_once dirname(__FILE__) . "/../../util/toolTime.php";
include_once dirname(__FILE__) . "/../../util/statusCode.php";
include_once dirname(__FILE__) . "/../../service/game.php";
include_once dirname(__FILE__) . "/../../util/jwt.php";
try {
    $jsonPost = new GamePostOrGetJson();
    $date = $jsonPost->getStr("date");

    if (!$TokenResult = Jwt::verifyToken()) {
        StatusCode::responseError(StatusCode::SYS_TOKEN_VERIFY_FAIL, "token验证失败");
    }

    if (!ToolTime::isDate($date)) {
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, "参数date错误");
    }

    ToolMySql::conn_gameServer();
    $data = Game::getSummary($date);
    ToolMySql::close_gameServer();
    StatusCode::responseSuccess(StatusCode::SUCCESS, $data);
} catch (Exception $e) {
    echo $e->getMessage();
}


