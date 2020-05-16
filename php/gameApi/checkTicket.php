<?php
include_once dirname(__FILE__) . "/../util/postJson.php";
include_once dirname(__FILE__) . "/../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../util/statusCode.php";
include_once dirname(__FILE__) . "/../service/game.php";
include_once dirname(__FILE__) . "/../util/logger.php";
try {
    Logger::debug("this is checkTicket",($_SERVER["HTTP_VIA"]) ? $_SERVER["HTTP_X_FORWARDED_FOR"] : $_SERVER["REMOTE_ADDR"]);
    $jsonPost = new GamePostOrGetJson();
    $id = $jsonPost->getStr("id");

    $obj = array("id"=>$id);
    Logger::debug('the params:',$obj);
    ToolMySql::conn();
    $data = Game::checkTicket($id);
    ToolMySql::close();
    if($data!==true)
        Logger::debug('sql error',$data);
    StatusCode::responseSuccess(StatusCode::SUCCESS, $data);
} catch (Exception $e) {
    echo $e->getMessage();
}
