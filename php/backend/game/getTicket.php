<?php
include_once dirname(__FILE__) . "/../../util/postJson.php";
include_once dirname(__FILE__) . "/../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../util/statusCode.php";
include_once dirname(__FILE__) . "/../../service/game.php";
include_once dirname(__FILE__) . "/../../util/checkParam.php";
include_once dirname(__FILE__) . "/../../util/jwt.php";
try {
    $jsonPost = new GamePostOrGetJson();
    $id = $jsonPost->getStr("id");

    if (!$TokenResult = Jwt::verifyToken()) {
        StatusCode::responseError(StatusCode::SYS_TOKEN_VERIFY_FAIL, "token验证失败");
    }

    if(!$id)
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, '缺少参数ID');

    ToolMySql::conn();
    $data = Game::getTicket($id);
    ToolMySql::close();

    StatusCode::responseSuccess(StatusCode::SUCCESS, $data);
} catch (Exception $e) {
    echo $e->getMessage();
}