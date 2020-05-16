<?php
/**
 * 把服务器的玩家同步到后台服务器，存在的玩家更新rid，不存在的插入。目前同步：2020-04-17
 */
include_once dirname(__FILE__) . "/../util/postJson.php";
include_once dirname(__FILE__) . "/../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../util/statusCode.php";
include_once dirname(__FILE__) . "/../service/game.php";
include_once dirname(__FILE__) . "/../util/logger.php";

try {
    $jsonPost = new GamePostOrGetJson();
    date_default_timezone_set("Asia/Shanghai");

    ToolMySql::conn();
    ToolMySql::conn_gameServer();
    $data = Game::synPlayers();
    ToolMySql::close();
    ToolMySql::close_gameServer();
    StatusCode::responseSuccess(StatusCode::SUCCESS, $data);
} catch (Exception $e) {
    echo $e->getMessage();
}
