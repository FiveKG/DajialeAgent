<?php
include_once dirname(__FILE__) . "/../util/postJson.php";
include_once dirname(__FILE__) . "/../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../util/statusCode.php";
include_once dirname(__FILE__) . "/../service/game.php";
include_once dirname(__FILE__) . "/../util/logger.php";
try {
    Logger::debug("this is consume",($_SERVER["HTTP_VIA"]) ? $_SERVER["HTTP_X_FORWARDED_FOR"] : $_SERVER["REMOTE_ADDR"]);
    $jsonPost = new GamePostOrGetJson();
    $userId = $jsonPost->getStr("userId");
    $matchId = $jsonPost->getStr("matchId");
    $amount =  $jsonPost->getStr("amount");
    $status =  $jsonPost->getStr("status");
    $localeId=  $jsonPost->getStr("localeId");


    $obj = array("userId"=>$userId, "orderNo"=>$orderNo,"gameId"=>$gameId, "amount"=>$amount,"subject"=>$subject,"mode"=>$mode);
    Logger::debug('the params:',$obj);

    ToolMySql::conn();
    $data = Game::playerConsume($userId, $matchId, $amount, $status, $localeId);
    ToolMySql::close();
    StatusCode::responseSuccess(StatusCode::SUCCESS, $data);
} catch (Exception $e) {
    echo $e->getMessage();
}
