<?php
include_once dirname(__FILE__) . "/../../util/postJson.php";
include_once dirname(__FILE__) . "/../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../util/statusCode.php";
include_once dirname(__FILE__) . "/../../service/game.php";
include_once dirname(__FILE__) . "/../../util/jwt.php";
try {
    $jsonPost = new GamePostOrGetJson();
    $rid = $jsonPost->getStr("rid");
    $fromRid = $jsonPost->getStr("fromRid");
    $parentId = $jsonPost->getStr("parentId");
    $type = $jsonPost->getStr("type");

    date_default_timezone_set("Asia/Shanghai");
    if (!$rid)
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR,"缺少参数rid");

    if (!$TokenResult = Jwt::verifyToken()) {
        StatusCode::responseError(StatusCode::SYS_TOKEN_VERIFY_FAIL, "token验证失败");
    }

    $fromUid = '';
    $agentId = '';
    ToolMySql::conn();
    ToolMySql::conn_gameServer();
    //被添加人必须存在游戏服务器且不存在后台服务器
    $addRid = Game::isExistInGame($rid);
    if (!$addRid)
        StatusCode::responseError(StatusCode::SYS_USER_REGISTER_ERROR, "被添加玩家'$rid'不存在游戏服务器中,无法注册");
    $isExist = Game::isExistInOperation($addRid['open_id']);
    if ($isExist)
        StatusCode::responseError(StatusCode::SYS_USER_REGISTER_ERROR, "被添加玩家'$rid'已经存在后台服务器中,无法注册");

    //分享玩家id必须同时存在游戏服务器和后台服务器
    if ($fromRid) {
        $isFromRid = Game::isExistInGame($fromRid);
        if (!$isFromRid)
            StatusCode::responseError(StatusCode::SYS_USER_REGISTER_ERROR, "分享玩家'$fromRid'不存在游戏服务器中,无法注册");
        $isExistFromRid = Game::isExistInOperation($isFromRid['open_id']);
        if (!$isExistFromRid)
            StatusCode::responseError(StatusCode::SYS_USER_REGISTER_ERROR, "分享玩家'$fromRid'不存在后台服务器中,无法注册");
        $fromUid = $isFromRid['open_id'];
        $agentId = $isFromRid['parent_id'];
    }

    if ($parentId) {
        //查看代理是否存在
        $isExistAgent = Game::isExistInAgent($parentId);
        if (!$isExistAgent)
            StatusCode::responseError(StatusCode::SYS_USER_REGISTER_ERROR, "代理'$parentId'不存在后台服务器中,无法注册");
        $agentId = $isExistAgent['id'];
    }

    $result = Game::reRegister($addRid, $agentId, $fromUid,$type);
    if ($result !== true)
        StatusCode::responseError(StatusCode::SYS_MYSQL_QUERY_ERROR, $result);
    StatusCode::responseSuccess(StatusCode::SUCCESS, $result);
    ToolMySql::close_gameServer();
    ToolMySql::close();
} catch (Exception $e) {
    echo $e->getMessage();
}