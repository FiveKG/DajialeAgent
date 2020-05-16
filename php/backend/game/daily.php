<?php
include_once dirname(__FILE__) . "/../../util/postJson.php";
include_once dirname(__FILE__) . "/../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../util/statusCode.php";
include_once dirname(__FILE__) . "/../../util/checkParam.php";
include_once dirname(__FILE__) . "/../../service/game.php";
include_once dirname(__FILE__) . "/../../util/jwt.php";
try {
    $jsonPost = new GamePostOrGetJson();
    $startDate = $jsonPost->getStr("startDate");
    $endDate = $jsonPost->getStr("endDate");
    $requestType = $jsonPost->getStr("type");
    $page = $jsonPost->getStr("page");
    $limit = $jsonPost->getStr("limit");

        $type = array("new_players", "active_players", "charge_players", "game_total");

    if (!$TokenResult = Jwt::verifyToken()) {
        StatusCode::responseError(StatusCode::SYS_TOKEN_VERIFY_FAIL, "token验证失败");
    }

    if (!in_array($requestType, $type)) {
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, "参数type错误");
    }

    $dateResult = CheckParam::checkSEDate($startDate, $endDate);
    if ($dateResult === false )
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, "日期错误");

    $pageLimitResult = CheckParam::checkPageLimit($page, $limit);
    if ($pageLimitResult === false)
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, "page/limit错误");


    ToolMySql::conn_gameServer();
    ToolMySql::conn();
    $data = Game::getDaily($dateResult['startDate'], $dateResult['endDate'], $requestType, $pageLimitResult['page'], $pageLimitResult['limit']);
    ToolMySql::close();
    ToolMySql::close_gameServer();

    StatusCode::responseSuccess(StatusCode::SUCCESS, $data);

} catch (Exception $e) {
    echo $e->getMessage();
}