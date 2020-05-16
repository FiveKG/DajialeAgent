<?php
include_once dirname(__FILE__) . "/../../../util/postJson.php";
include_once dirname(__FILE__) . "/../../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../../util/statusCode.php";
include_once dirname(__FILE__) . "/../../../service/promotion.php";
include_once dirname(__FILE__) . "/../../../util/jwt.php";
include_once dirname(__FILE__) . "/../../../util/checkParam.php";

try {
    $jsonPost = new GamePostOrGetJson();
    $page = $jsonPost->getStr("page");
    $limit = $jsonPost->getStr("limit");
    $type = $jsonPost->getStr("type");
    $rid = $jsonPost->getStr("rid");

    if (!$TokenResult = Jwt::verifyToken()) {
        StatusCode::responseError(StatusCode::SYS_TOKEN_VERIFY_FAIL, "token验证失败");
    }

    $typeArray = array('all','yes','no','await');
    if (!in_array($type, $typeArray))
        $type = 'all';

    $pageLimitResult = CheckParam::checkPageLimit($page, $limit);
    if ($pageLimitResult === false)
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, "page/limit错误");

    ToolMySql::conn();
    $data = Promotion::promoterList($page, $limit, $type, $rid);
    StatusCode::responseSuccess(StatusCode::SUCCESS, $data);
    ToolMySql::close();

} catch (exception $e) {
    echo $e->getMessage();
}