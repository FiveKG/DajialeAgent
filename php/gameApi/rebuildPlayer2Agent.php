<?php
/**
 * 考虑
 * 把玩家重新连接给代理商
 */
include_once dirname(__FILE__) . "/../util/postJson.php";
include_once dirname(__FILE__) . "/../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../util/statusCode.php";
include_once dirname(__FILE__) . "/../util/toolForAgent.php";
include_once dirname(__FILE__) . "/../util/logger.php";
try {
    $jsonPost = new GamePostOrGetJson();
    $rid = $jsonPost->getStr("rid");
    $tel = $jsonPost->getStr("tel");
    $agentId = $jsonPost->getStr("agentId");
    date_default_timezone_set("Asia/Shanghai");

    ToolMySql::conn();
    ToolMySql::conn_gameServer();
    $data = ToolForAgent::rebuildPlayer2Agent($rid,$tel, $agentId);
    ToolMySql::close();
    ToolMySql::close_gameServer();

    if($data!==true)
        Logger::debug('sql error',$data);
    StatusCode::responseSuccess(StatusCode::SUCCESS, $data);
} catch (Exception $e) {
    echo $e->getMessage();
}
