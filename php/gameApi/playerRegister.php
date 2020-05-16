<?php

include_once dirname(__FILE__) . "/../util/postJson.php";
include_once dirname(__FILE__) . "/../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../util/statusCode.php";
include_once dirname(__FILE__) . "/../service/game.php";
include_once dirname(__FILE__) . "/../util/logger.php";


try {
    Logger::debug("this is register",($_SERVER["HTTP_VIA"]) ? $_SERVER["HTTP_X_FORWARDED_FOR"] : $_SERVER["REMOTE_ADDR"]);
    $jsonPost = new GamePostOrGetJson();
    $type = $jsonPost->getStr("type");
    $obj = $jsonPost->getStr("obj");

    date_default_timezone_set("Asia/Shanghai");

    $object = array("type"=>$type, "obj"=>$obj);
    Logger::debug('the params:',$object);

    ToolMySql::conn();
    ToolMySql::conn_gameServer();
    $data = Game::playerRegister($type, $obj);
    ToolMySql::close();
    ToolMySql::close_gameServer();

    if($data!==true)
        Logger::debug('sql error',$data);
    StatusCode::responseSuccess(StatusCode::SUCCESS, $data);
} catch (Exception $e) {
    echo $e->getMessage();
}
