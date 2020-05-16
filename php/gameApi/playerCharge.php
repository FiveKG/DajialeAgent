<?php
include_once dirname(__FILE__) . "/../util/postJson.php";
include_once dirname(__FILE__) . "/../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../util/statusCode.php";
include_once dirname(__FILE__) . "/../service/game.php";
include_once dirname(__FILE__) . "/../util/logger.php";
try {
    Logger::debug("this is charge",($_SERVER["HTTP_VIA"]) ? $_SERVER["HTTP_X_FORWARDED_FOR"] : $_SERVER["REMOTE_ADDR"]);
    $jsonPost = new GamePostOrGetJson();
    $userId = $jsonPost->getStr("userId");
    $orderNo = $jsonPost->getStr("orderNo");
    $gameId = $jsonPost->getStr("gameId");
    $amount =  $jsonPost->getStr("amount");
    $subject =  $jsonPost->getStr("subject");
    $mode = $jsonPost->getStr("mode");

    $obj = array("userId"=>$userId, "orderNo"=>$orderNo,"gameId"=>$gameId, "amount"=>$amount,"subject"=>$subject,"mode"=>$mode);
    Logger::debug('the params:',$obj);
    ToolMySql::conn();
        $data = Game::playerCharge($userId, $orderNo, $gameId, $amount, $subject, $mode);
    ToolMySql::close();
    if($data!==true)
        Logger::debug('sql error',$data);
    StatusCode::responseSuccess(StatusCode::SUCCESS, $data);
} catch (Exception $e) {
    echo $e->getMessage();
}
