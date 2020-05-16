<?php
include_once dirname(__FILE__) . "/../util/postJson.php";
include_once dirname(__FILE__) . "/../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../util/statusCode.php";
include_once dirname(__FILE__) . "/../service/game.php";
include_once dirname(__FILE__) . "/../util/logger.php";


try {
    $jsonPost = new GamePostOrGetJson();
    $localeId = $jsonPost->getStr("localeId");
    $matchId = $jsonPost->getStr("matchId");
    $list = $jsonPost->getStr("list");

    $obj = array("localeId"=>$localeId, "matchId"=>$matchId,"list"=>$list);
    Logger::debug('this is playerPrize,',$obj);
    if(!$localeId ||!$matchId || !$list) {
        Logger::debug('this is playerPrize, need param',$obj);
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR,$obj);
    }

    ToolMySql::conn();
    $data = Game::playerPrize($localeId, $matchId, $list);
    ToolMySql::close();
    if(!$data)
        Logger::debug('this is playerPrize,sql error',$data);
    StatusCode::responseSuccess(StatusCode::SUCCESS, $data);
} catch (Exception $e) {
    echo $e->getMessage();
}
