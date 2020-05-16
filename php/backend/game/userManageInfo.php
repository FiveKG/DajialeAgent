<?php
include_once dirname(__FILE__) . "/../../util/postJson.php";
include_once dirname(__FILE__) . "/../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../util/statusCode.php";
include_once dirname(__FILE__) . "/../../service/game.php";
include_once dirname(__FILE__) . "/../../util/checkParam.php";
include_once dirname(__FILE__) . "/../../util/jwt.php";
try {
    $jsonPost = new GamePostOrGetJson();
    $userId = $jsonPost->getStr("userId");
    $page = $jsonPost->getStr("page");
    $limit = $jsonPost->getStr("limit");
    $startDate = $jsonPost->getStr("startDate");
    $endDate = $jsonPost->getStr("endDate");
    $requestType = $jsonPost->getStr("type");
    $type = array('chargeInfo', 'cashInfo', 'joinInfo', 'exchangeInfo');

    date_default_timezone_set("Asia/Shanghai");
    if (!$TokenResult = Jwt::verifyToken()) {
        StatusCode::responseError(StatusCode::SYS_TOKEN_VERIFY_FAIL, "token验证失败");
    }
    if(!$userId)
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, "userId不能为空");

    $pageLimitResult = CheckParam::checkPageLimit($page, $limit);
    if ($pageLimitResult === false)
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, "page/limit错误");

    $dateResult = CheckParam::checkSEDate($startDate, $endDate);
    if ($dateResult === false )
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, "日期错误");

    if (!in_array($requestType, $type))
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, "type错误");

    //从大家乐拉取
    if($requestType === 'chargeInfo')
        $data = Game::getChargeInfo($userId,$dateResult['startDate'], $dateResult['endDate'],$pageLimitResult['page'], $pageLimitResult['limit']);

    //从大家乐拉取
    if($requestType === "cashInfo")
        $data = Game::getCaseInfo($userId,$dateResult['startDate'], $dateResult['endDate'],$pageLimitResult['page'], $pageLimitResult['limit']);

    if($requestType === "joinInfo") {
        ToolMySql::conn();
        $data = Game::getJoinInfo($userId,$dateResult['startDate'], $dateResult['endDate'], $pageLimitResult['page'], $pageLimitResult['limit']);
        ToolMySql::close();
    }

    if ($data === false)
        StatusCode::responseSuccess(StatusCode::COMMON_NETWORK_ERROR,'查询出错');
    StatusCode::responseSuccess(StatusCode::SUCCESS, $data);

} catch (Exception $e) {
    echo $e->getMessage();
}