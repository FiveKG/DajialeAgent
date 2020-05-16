<?php
include_once dirname(__FILE__) . "/../../util/postJson.php";
include_once dirname(__FILE__) . "/../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../util/statusCode.php";
include_once dirname(__FILE__) . "/../../service/game.php";
include_once dirname(__FILE__) . "/../../util/jwt.php";

try {
    $jsonPost = new GamePostOrGetJson();
    $requireDate = array();
    $requireDate["username"] = $jsonPost->getStr("username");
    $requireDate["password"] = $jsonPost->getStr("password");
    $requireDate["weight"] = $jsonPost->getStr("weight");
    $requireDate["authority"] = $jsonPost->getStr("authority");
    $requireDate["remark"] = $jsonPost->getStr("remark");

    if (!$TokenResult = Jwt::verifyToken()) {
        StatusCode::responseError(StatusCode::SYS_TOKEN_VERIFY_FAIL, "token验证失败");
    }

    foreach ($requireDate as $keys => $values) {
        if ($keys == 'remark')
            continue;
        if (!$values  && $values!=0)
            StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, $keys."不能为空");
    }

    ToolMySql::conn();
    $data = Game::addAdmin($requireDate);
    ToolMySql::close();

    StatusCode::responseSuccess(StatusCode::SUCCESS, $data);
} catch (Exception $e) {
    echo $e->getMessage();
}