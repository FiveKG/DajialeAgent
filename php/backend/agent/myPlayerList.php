<?php
/**
 * 按不同玩家信息分页返回玩家信息。
 * 类型为("fission", "direct", "all")。
 *返回eg：{
            "code": 20000,
            "data": [
                        {
                        "id": "9VT3-28o5v1KKojs",
                        "username": "玩家10",
                        "charge_total": "2200",
                        "consume_total": "315",
                        "tel": "18814140180",
                        "create_at": "2020-01-17 13:06:07"
                        }
                    ]
          }
 */
include_once dirname(__FILE__) . "/../../util/postJson.php";
include_once dirname(__FILE__) . "/../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../util/toolTime.php";
include_once dirname(__FILE__) . "/../../util/statusCode.php";
include_once dirname(__FILE__) . "/../../service/agent/getMyPlayerList.php";
include_once dirname(__FILE__) . "/../../util/jwt.php";
include_once dirname(__FILE__) . "/../../util/logger.php";
include_once dirname(__FILE__) . "/../../util/checkParam.php";

try {
    $jsonPost = new GamePostOrGetJson();
    $page = $jsonPost->getStr("page");
    $limit = $jsonPost->getStr("limit");
    $requestType = $jsonPost->getStr("type");
    $type = array("fission", "direct", "all");

    if (!$TokenResult = Jwt::verifyToken()) {
        StatusCode::responseError(StatusCode::SYS_TOKEN_VERIFY_FAIL, "token验证失败");
    }
    if (!in_array($requestType, $type)) {
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, "参数type错误");
    }
    $pageLimitResult = CheckParam::checkPageLimit($page, $limit);
    if ($pageLimitResult === false)
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, "page/limit错误");

    $agentId = $TokenResult['agentId'];

    ToolMySql::conn();
    $data = GetMyPlayerList::getPlayerList($agentId, $requestType, $pageLimitResult['page'], $pageLimitResult['limit']);
    StatusCode::responseSuccess(StatusCode::SUCCESS, $data);
    ToolMySql::close();

} catch (Exception $e) {
    echo $e->getMessage();

}
