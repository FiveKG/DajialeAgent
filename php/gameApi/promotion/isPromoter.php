<?php
include_once dirname(__FILE__) . "/../../util/postJson.php";
include_once dirname(__FILE__) . "/../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../util/statusCode.php";
include_once dirname(__FILE__) . "/../../util/logger.php";
include_once dirname(__FILE__) . "/../../service/promotion.php";
try {
    Logger::debug("this is isPromoter",($_SERVER["HTTP_VIA"]) ? $_SERVER["HTTP_X_FORWARDED_FOR"] : $_SERVER["REMOTE_ADDR"]);
    $jsonPost = new GamePostOrGetJson();
    $userId = $jsonPost->getStr("userId");

    $obj = array("userId"=>$userId);
    Logger::debug('the params:',$obj);

    ToolMySql::conn();
    $data = Promotion::isPromoter($userId);
    if (is_string($data))
        StatusCode::responseError(StatusCode::SYS_MYSQL_QUERY_ERROR,$data);
    StatusCode::responseSuccess(StatusCode::SUCCESS, $data);

    Logger::debug('the result:',$data);
} catch (Exception $e) {
    echo $e->getMessage();
}
