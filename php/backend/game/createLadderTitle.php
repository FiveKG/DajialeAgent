<?php
include_once dirname(__FILE__) . "/../../util/postJson.php";
include_once dirname(__FILE__) . "/../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../util/statusCode.php";
include_once dirname(__FILE__) . "/../../service/game.php";
include_once dirname(__FILE__) . "/../../util/jwt.php";
try {
    $jsonPost = new GamePostOrGetJson();
    $scoreMax = $jsonPost->getStr("scoreMax");
    $title = $jsonPost->getStr("title");

    if (!$TokenResult = Jwt::verifyToken()) {
        StatusCode::responseError(StatusCode::SYS_TOKEN_VERIFY_FAIL, "token验证失败");
    }

    if (!$scoreMax || !$title)
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR,'缺少参数');

    if(!is_numeric($scoreMax))
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR,'scoreMax类型错误');

    ToolMySql::conn();
    $result = Game::createLadderTitle($scoreMax, $title);
    if ($result !== true)
        StatusCode::responseError(StatusCode::SYS_MYSQL_QUERY_ERROR, $result);
    StatusCode::responseSuccess(StatusCode::SUCCESS, $result);

    ToolMySql::close();
} catch (Exception $e) {
    echo $e->getMessage();
}