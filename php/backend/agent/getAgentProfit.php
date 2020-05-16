<?php
/**
 * Created by YX.
 * Date: 2019/7/16
 * Time: 12:05
 * home页面信息
 */

include_once dirname(__FILE__) . "/../../util/postJson.php";
include_once dirname(__FILE__) . "/../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../util/toolToken.php";
include_once dirname(__FILE__) . "/../../util/statusCode.php";
include_once dirname(__FILE__) . "/../../service/agent/getAgentProfit.php";
include_once dirname(__FILE__) . "/../../util/jwt.php";
try {
    if (!$TokenResult = Jwt::verifyToken()) {
        StatusCode::responseError(StatusCode::SYS_TOKEN_VERIFY_FAIL, "token验证失败");
    }
    $agentId = $TokenResult['agentId'];
    $data = array();
    ToolMySql::conn();
        $data = GetAgentProfit::getMyProfit($agentId);
        $data['ProfitFromPlayer']['fast_total'] = round($data['ProfitFromPlayer']['fast_total'],2);
        $data['ProfitFromPlayer']['other_total'] = round($data['ProfitFromPlayer']['other_total'],2);
        $data['getProfitFromAgent']['fast_total'] = round($data['getProfitFromAgent']['fast_total'],2);
        $data['getProfitFromAgent']['other_total'] = round($data['getProfitFromAgent']['other_total'],2);
        StatusCode::responseSuccess(StatusCode::SUCCESS, $data);
    ToolMySql::close();

} catch (Exception $e) {
    echo $e->getMessage();
}

