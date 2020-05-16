<?php
/**
 * 分页返回我的代理商信息
 */
include_once dirname(__FILE__) . "/../../util/postJson.php";
include_once dirname(__FILE__) . "/../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../util/statusCode.php";
include_once dirname(__FILE__) . "/../../service/agent/operateMyApply.php";
include_once dirname(__FILE__) . "/../../util/jwt.php";;

try {
    $jsonPost = new GamePostOrGetJson();
    $agentId = $jsonPost->getStr("agentId");
    $requestType = $jsonPost->getStr("type");
    $note = $jsonPost->getStr("note");
    $type = array('agree', 'refuse');

    if (!$TokenResult = Jwt::verifyToken()) {
        StatusCode::responseError(StatusCode::SYS_TOKEN_VERIFY_FAIL, "token验证失败");
    }

    if (!$agentId || !$type) {
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, "缺少参数");
    }

    if (!in_array($requestType, $type)) {
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, "参数type错误");
    }

    ToolMySql::conn();
    $data = OperateMyApply::operateApply($agentId, $requestType, $note);
    if (!$data)
        StatusCode::responseError(StatusCode::SYS_MYSQL_QUERY_ERROR, $data);
    StatusCode::responseSuccess(StatusCode::SUCCESS, $data);
    ToolMySql::close();

} catch (Exception $e) {
    echo $e->getMessage();
}