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
    $page = $jsonPost->getStr("page");
    $limit = $jsonPost->getStr("limit");


    if (!$TokenResult = Jwt::verifyToken()) {
        StatusCode::responseError(StatusCode::SYS_TOKEN_VERIFY_FAIL, "token验证失败");
    }

    $dateResult = CheckParam::checkSEDate($startDate, $endDate);
    if ($dateResult === false )
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, "日期错误");

    $pageLimitResult = CheckParam::checkPageLimit($page, $limit);
    if ($pageLimitResult === false)
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, "page/limit错误");


    ToolMySql::conn();
    $data = Game::financeSummary($dateResult['startDate'], $dateResult['endDate'], $pageLimitResult['page'], $pageLimitResult['limit']);
    ToolMySql::close();

    StatusCode::responseSuccess(StatusCode::SUCCESS, $data);

} catch (Exception $e) {
    echo $e->getMessage();
}