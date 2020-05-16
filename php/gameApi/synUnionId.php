<?php
/**
 * 考虑
* 把数据库里没有wxuionid的用户和游戏服务端的同步起来
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
    $data = Game::synUnionId();
    ToolMySql::close();
    ToolMySql::close_gameServer();
    StatusCode::responseSuccess(StatusCode::SUCCESS, $data);
} catch (Exception $e) {
    echo $e->getMessage();
}
