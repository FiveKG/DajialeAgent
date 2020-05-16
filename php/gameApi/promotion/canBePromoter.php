<?php
include_once dirname(__FILE__) . "/../../util/postJson.php";
include_once dirname(__FILE__) . "/../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../util/statusCode.php";
include_once dirname(__FILE__) . "/../../util/logger.php";
include_once dirname(__FILE__) . "/../../service/promotion.php";
try {
    Logger::debug("this is can be promoter",($_SERVER["HTTP_VIA"]) ? $_SERVER["HTTP_X_FORWARDED_FOR"] : $_SERVER["REMOTE_ADDR"]);

    $jsonPost = new GamePostOrGetJson();
    $userId = $jsonPost->getStr("userId");

    $obj = array("userId"=>$userId);
    Logger::debug('the params:',$obj);

    if(!$userId)
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, 'userId不能为空');

    ToolMySql::conn();
    $data = Promotion::bePromoter($userId);
    StatusCode::responseSuccess(StatusCode::SUCCESS, $data);
    ToolMySql::close();


    Logger::debug('the result:',$data);
} catch (Exception $e) {
    echo $e->getMessage();
}
