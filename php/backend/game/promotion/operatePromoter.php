<?php
include_once dirname(__FILE__) . "/../../../util/postJson.php";
include_once dirname(__FILE__) . "/../../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../../util/statusCode.php";
include_once dirname(__FILE__) . "/../../../util/checkParam.php";
include_once dirname(__FILE__) . "/../../../service/promotion.php";
include_once dirname(__FILE__) . "/../../../util/jwt.php";
try {
    $jsonPost = new GamePostOrGetJson();
    $rid = $jsonPost->getStr("rid");
    $type = $jsonPost->getStr("type");

    if (!$TokenResult = Jwt::verifyToken()) {
        StatusCode::responseError(StatusCode::SYS_TOKEN_VERIFY_FAIL, "token验证失败");
    }
    $typeRequest = array('true', 'false');
    if(!in_array($type, $typeRequest))
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, '操作类型出错');

    ToolMySql::conn();
    $data = Promotion::operatePromoter($rid, $type);
    if(!$data)
        StatusCode::responseError(StatusCode::SYS_MYSQL_QUERY_ERROR, '操作失败');
    StatusCode::responseSuccess(StatusCode::SUCCESS, '操作成功');
    ToolMySql::close();
} catch (Exception $e) {
    echo $e->getMessage();
}