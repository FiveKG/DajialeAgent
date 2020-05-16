<?php
/**
 * 分页返回我的代理商信息
 */
include_once dirname(__FILE__) . "/../../util/postJson.php";
include_once dirname(__FILE__) . "/../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../util/statusCode.php";
include_once dirname(__FILE__) . "/../../service/agent/setSubProfit.php";
include_once dirname(__FILE__) . "/../../util/jwt.php";;
include_once dirname(__FILE__) . "/../../util/checkParam.php";

try {
    $jsonPost = new GamePostOrGetJson();
    $agentId = $jsonPost->getStr("agentId");
    $profit =  $jsonPost->getStr("profit");
    if (!$TokenResult = Jwt::verifyToken()) {
        StatusCode::responseError(StatusCode::SYS_TOKEN_VERIFY_FAIL, "token验证失败");
    }
    $MyAgentId = $TokenResult['agentId'];
    if (!$agentId || !$profit)
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, "缺少参数");

    if (!is_numeric($profit) || (int)$profit>100 || (int)$profit<0)
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, "profit参数错误");

    ToolMySql::conn();
    $data = SetSubProfit::setProfit($MyAgentId,$agentId, $profit);
    if($data !==true)
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, $data);
    StatusCode::responseSuccess(StatusCode::SUCCESS, $data);
    ToolMySql::close();

} catch (Exception $e) {
    echo $e->getMessage();
}