<?php
/**
 * 分页返回我的代理商信息
 */
include_once dirname(__FILE__) . "/../../util/postJson.php";
include_once dirname(__FILE__) . "/../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../util/statusCode.php";
include_once dirname(__FILE__) . "/../../service/agent/registerAgent.php";

try {
    $jsonPost = new GamePostOrGetJson();
    $pid = $jsonPost->getStr("pid");
    $username = $jsonPost->getStr("username");
    $password = $jsonPost->getStr("password");
    $tel = $jsonPost->getStr("tel");

    if (!$pid || !$username || !$password || !$tel)
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, "缺少参数");

    if(strlen($password) < 6)
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, "密码至少6位数");

    if(strlen($tel) < 11 || !is_numeric($tel))
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, "手机号码格式错误");

    ToolMySql::conn();
    $data = RegisterAgent::register($pid, $username, $password, $tel);
    if ($data !== true)
        StatusCode::responseError(StatusCode::SYS_USER_REGISTER_ERROR, $data);
    StatusCode::responseSuccess(StatusCode::SUCCESS, $data);
    ToolMySql::close();

} catch (Exception $e) {
    echo $e->getMessage();
}