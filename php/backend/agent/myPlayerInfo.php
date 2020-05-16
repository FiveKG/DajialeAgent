<?php
/**
 * 获取不同类型玩家的总览信息
 * 类型为("fission", "direct", "all")。
 * 返回eg：{
            "code": 20000,
            "data": {
                "total": "5",
                "today": "0",
                "yesterday": "0",
                "charge_total": "19100",
                "consume_total": "8976"
                }
            }
 */
include_once dirname(__FILE__) . "/../../util/postJson.php";
include_once dirname(__FILE__) . "/../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../util/toolTime.php";
include_once dirname(__FILE__) . "/../../util/statusCode.php";
include_once dirname(__FILE__) . "/../../service/agent/getMyPlayerInfo.php";
include_once dirname(__FILE__) . "/../../util/jwt.php";

try {
    $jsonPost = new GamePostOrGetJson();
    $requestType = $jsonPost->getStr("type");
    $type = array("fission", "direct", "all");

    if (!$TokenResult = Jwt::verifyToken()) {
        StatusCode::responseError(StatusCode::SYS_TOKEN_VERIFY_FAIL, "token验证失败");
    }
    if (!in_array($requestType, $type) || !$requestType) {
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, "参数type错误");
    }

    $agentId = $TokenResult['agentId'];
    ToolMySql::conn();
    $data = GetMyPlayerInfo::getPlayerListTotal($agentId, $requestType);
    StatusCode::responseSuccess(StatusCode::SUCCESS, $data);
    ToolMySql::close();
} catch (Exception $e) {
    echo $e->getMessage();
}