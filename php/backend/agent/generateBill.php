<?php
include_once dirname(__FILE__) . "/../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../util/toolToken.php";
include_once dirname(__FILE__) . "/../../util/toolTime.php";
include_once dirname(__FILE__) . "/../../util/statusCode.php";
include_once dirname(__FILE__) . "/../../service/agent/generateAgentBill.php";
include_once dirname(__FILE__) . "/../../util/jwt.php";
try {
    if (!$TokenResult = Jwt::verifyToken()) {
        StatusCode::responseError(StatusCode::SYS_TOKEN_VERIFY_FAIL, "token验证失败");
    }
    $level = $TokenResult['level'];
    if ($level !== '0')
        StatusCode::responseError(StatusCode::SYS_HAS_NOT_AUTHORITY,'无权限');

    $data = array();
    ToolMySql::conn();
    //限制一天只能生成一次账单
    $today = ToolTime::getToday();
    $sameDay = "select create_at from ".Config::SQL_DB.".bill where DATEDIFF(create_at,'$today')=0  limit 1;";
    $sameDayResult = ToolMySql::query($sameDay);
    if($sameDayResult->num_rows)
        StatusCode::responseError(StatusCode::SYS_SAVE_FILE_ERROR, '一天只能生成一次账单');
    $data = GenerateAgentBill::generateBill();
    StatusCode::responseSuccess(StatusCode::SUCCESS, $data);
    ToolMySql::close();

} catch (Exception $e) {
    echo $e->getMessage();
}

