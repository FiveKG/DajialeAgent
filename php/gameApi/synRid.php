<?php
/**
* 把数据库里没有rid的用户和游戏服务端的同步起来
 */
include_once dirname(__FILE__) . "/../util/postJson.php";
include_once dirname(__FILE__) . "/../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../util/statusCode.php";
include_once dirname(__FILE__) . "/../service/game.php";
include_once dirname(__FILE__) . "/../util/logger.php";

try {
    $jsonPost = new GamePostOrGetJson();
    $limit = $jsonPost->getStr("limit");
    date_default_timezone_set("Asia/Shanghai");

    if (!$limit)
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR,'缺少参数limit');
    if (!is_numeric($limit))
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, "limit必须为数字");
    ToolMySql::conn();
    ToolMySql::conn_gameServer();
    $data = Game::synRid($limit);
    ToolMySql::close();
    ToolMySql::close_gameServer();
    StatusCode::responseSuccess(StatusCode::SUCCESS, $data);
} catch (Exception $e) {
    echo $e->getMessage();
}
