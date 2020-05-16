<?php
include_once dirname(__FILE__) . "/../../util/postJson.php";
include_once dirname(__FILE__) . "/../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../util/statusCode.php";
include_once dirname(__FILE__) . "/../../service/game.php";
include_once dirname(__FILE__) . "/../../util/checkParam.php";
include_once dirname(__FILE__) . "/../../util/jwt.php";
try {
    $jsonPost = new GamePostOrGetJson();
    $rid = $jsonPost->getStr("rid");

    if (!$TokenResult = Jwt::verifyToken()) {
        StatusCode::responseError(StatusCode::SYS_TOKEN_VERIFY_FAIL, "token验证失败");
    }

    ToolMySql::conn_gameServer();
    ToolMySql::conn();
    $data = Game::getPlayer($rid);
    ToolMySql::close_gameServer();
    ToolMySql::close();

    StatusCode::responseSuccess(StatusCode::SUCCESS, $data);
} catch (Exception $e) {
    echo $e->getMessage();
}