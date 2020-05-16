<?php
include_once dirname(__FILE__) . "/../../util/postJson.php";
include_once dirname(__FILE__) . "/../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../util/statusCode.php";
include_once dirname(__FILE__) . "/../../service/game.php";
include_once dirname(__FILE__) . "/../../util/jwt.php";
try {
    $jsonPost = new GamePostOrGetJson();

    $rankMax = $jsonPost->getStr("rankMax");
    $weekGiveback = $jsonPost->getStr("weekGiveback");
    $weekTicketNumb = $jsonPost->getStr("weekTicketNumb");
    $weekTicketId = $jsonPost->getStr("weekTicketId");
    $monthGiveback = $jsonPost->getStr("monthGiveback");
    $monthTicketNumb = $jsonPost->getStr("monthTicketNumb");
    $monthTicketId = $jsonPost->getStr("monthTicketId");

    if (!$TokenResult = Jwt::verifyToken()) {
        StatusCode::responseError(StatusCode::SYS_TOKEN_VERIFY_FAIL, "token验证失败");
    }

    if(!is_numeric($rankMax))
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR,'rankMax类型错误');
    if(!is_numeric($weekGiveback))
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR,'weekGiveback类型错误');
    if(!is_numeric($weekTicketNumb))
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR,'weekTicketNumb类型错误');
    if(!is_numeric($weekTicketId))
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR,'weekTicketId类型错误');
    if(!is_numeric($monthGiveback))
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR,'monthGiveback类型错误');
    if(!is_numeric($monthTicketNumb))
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR,'monthTicketNumb类型错误');
    if(!is_numeric($monthTicketId))
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR,'monthTicketId类型错误');


    ToolMySql::conn();
    $result = Game::createLadderAward($rankMax, $weekGiveback, $weekTicketNumb, $weekTicketId, $monthGiveback, $monthTicketNumb, $monthTicketId);
    if ($result !== true)
        StatusCode::responseError(StatusCode::SYS_MYSQL_QUERY_ERROR, $result);
    StatusCode::responseSuccess(StatusCode::SUCCESS, $result);

    ToolMySql::close();
} catch (Exception $e) {
    echo $e->getMessage();
}