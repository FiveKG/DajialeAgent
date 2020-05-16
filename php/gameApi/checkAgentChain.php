<?php
/**
 * 考虑
 * 查找代理链
 */
include_once dirname(__FILE__) . "/../util/postJson.php";
include_once dirname(__FILE__) . "/../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../util/statusCode.php";
include_once dirname(__FILE__) . "/../util/toolForAgent.php";
include_once dirname(__FILE__) . "/../util/logger.php";
try {
    $jsonPost = new GamePostOrGetJson();


    ToolMySql::conn();
    $data = ToolForAgent::checkAgentChain();
    ToolMySql::close();
    if($data===true)
        StatusCode::responseSuccess(StatusCode::SUCCESS, $data);
    else
        StatusCode::responseSuccess(StatusCode::SYS_QUERY_PARAMS_ERROR, false);
} catch (Exception $e) {
    echo $e->getMessage();
}
