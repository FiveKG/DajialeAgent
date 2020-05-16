<?php
/**
 * 考虑
 */
include_once dirname(__FILE__) . "/../util/postJson.php";
include_once dirname(__FILE__) . "/../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../util/statusCode.php";
include_once dirname(__FILE__) . "/../service/game.php";
include_once dirname(__FILE__) . "/../util/logger.php";
try {

    ToolMySql::conn();
    ToolMySql::conn_gameServer();
    $data = Game::sendToCS();
    ToolMySql::close();
    ToolMySql::close_gameServer();
    StatusCode::responseSuccess(StatusCode::SUCCESS, $data);
} catch (Exception $e) {
    echo $e->getMessage();
}
