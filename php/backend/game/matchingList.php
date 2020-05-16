<?php
include_once dirname(__FILE__) . "/../../util/postJson.php";
include_once dirname(__FILE__) . "/../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../util/statusCode.php";
include_once dirname(__FILE__) . "/../../util/checkParam.php";
include_once dirname(__FILE__) . "/../../service/game.php";
include_once dirname(__FILE__) . "/../../util/jwt.php";
try {
    $jsonPost = new GamePostOrGetJson();
    $page = $jsonPost->getStr("page");
    $limit = $jsonPost->getStr("limit");
    $type = $jsonPost->getStr('type');

    $propType = array("doing", "waiting");
    if (!$TokenResult = Jwt::verifyToken()) {
        StatusCode::responseError(StatusCode::SYS_TOKEN_VERIFY_FAIL, "token验证失败");
    }

    $pageLimitResult = CheckParam::checkPageLimit($page, $limit);
    if ($pageLimitResult === false)
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, "page/limit错误");

    if( !in_array($type, $propType ))
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, "type错误");

    ToolMySql::conn_gameServer();
    $data = Game::getMatchingList($pageLimitResult['page'], $pageLimitResult['limit'], $type);
    ToolMySql::close_gameServer();

    StatusCode::responseSuccess(StatusCode::SUCCESS, $data);

} catch (Exception $e) {
    echo $e->getMessage();
}