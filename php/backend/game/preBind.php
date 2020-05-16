<?php
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:POST,GET,OPTIONS');
header('Access-Control-Allow-Headers:Authorization,lpy');
header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With,X-PINGOTHER,Content-Type: text/html; charset=utf-8');

include_once dirname(__FILE__) . "/../../util/postJson.php";
include_once dirname(__FILE__) . "/../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../util/statusCode.php";
include_once dirname(__FILE__) . "/../../util/checkParam.php";
include_once dirname(__FILE__) . "/../../service/game.php";
include_once dirname(__FILE__) . "/../../util/jwt.php";
include_once dirname(__FILE__) . "/../../util/logger.php";
try {
    Logger::debug("this is preBind",($_SERVER["HTTP_VIA"]) ? $_SERVER["HTTP_X_FORWARDED_FOR"] : $_SERVER["REMOTE_ADDR"]);
    $jsonPost = new GamePostOrGetJson();
    $unionid = $jsonPost->getStr("unionid");
    $iv_code = $jsonPost->getStr("iv_code");
    $role = $jsonPost->getStr("role");

    $obj = array("unionid"=>$unionid, "iv_code"=>$iv_code,"role"=>$role);
    Logger::debug('the params:',$obj);

    if(!$unionid || !$iv_code || !$role)
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR,'no param');


    if($role==="player")
        $role = 2;
    if($role==="agent")
        $role = 1;
    ToolMySql::conn();
    $data = Game::preBind($unionid, $iv_code, $role);
    if($data !== true) {
        StatusCode::responseError(StatusCode::SYS_MYSQL_QUERY_ERROR, $data);
        Logger::debug('sql error',$data);
    }
    StatusCode::responseSuccess(StatusCode::SUCCESS, $data);
    ToolMySql::close();
} catch (Exception $e) {
    echo $e->getMessage();
}