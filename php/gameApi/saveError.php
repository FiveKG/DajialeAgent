<?php
include_once dirname(__FILE__) . "/../util/postJson.php";
include_once dirname(__FILE__) . "/../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../util/statusCode.php";
include_once dirname(__FILE__) . "/../service/game.php";
include_once dirname(__FILE__) . "/../util/logger.php";
try {
    $str = file_get_contents("php://input");
    $errorString = str_replace("'","\"",$str);

    ToolMySql::conn();
    $result = Game::saveError($errorString);
    ToolMySql::close();
    if ($result)
        StatusCode::responseSuccess(StatusCode::SUCCESS, '添加成功');
    else
        StatusCode::responseError(StatusCode::SYS_MYSQL_QUERY_ERROR, '添加出错');

} catch (Exception $e) {
    echo $e->getMessage();
}