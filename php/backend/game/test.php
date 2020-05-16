<?php
include_once dirname(__FILE__) . "/../../util/postJson.php";
include_once dirname(__FILE__) . "/../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../util/statusCode.php";
include_once dirname(__FILE__) . "/../../util/checkParam.php";
include_once dirname(__FILE__) . "/../../service/game.php";
include_once dirname(__FILE__) . "/../../util/jwt.php";
try {
    $jsonPost = new GamePostOrGetJson();
    $userId = $jsonPost->getStr("userId");


//    ToolMySql::conn();
//    $data = Game::checkPlayer2Redis($userId);
//    ToolMySql::close();

    StatusCode::responseSuccess(StatusCode::SUCCESS, $data);

} catch (Exception $e) {
    echo $e->getMessage();
}