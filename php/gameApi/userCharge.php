<?php
/**
 * Created by YX.
 * Date: 2020-02-05
 * Time: 11:00
 * 玩家充值
 */

include_once dirname(__FILE__) . "/../util/postJson.php";


try {
    $jsonPost = new GamePostOrGetJson();
    $userId = $jsonPost->getStr("userId");
    $amount = $jsonPost->getStr("amount");
    $currency = $jsonPost->getStr("currency");
    $status = $jsonPost->getStr("status");
    $mode = $jsonPost->getStr("mode");

    // 更多必要数据

    // todo: 数据库记录充值记录
    if (!$userId || !$amount || !$currency ||!$status ||!$mode) {
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, "缺少参数");
    }

    if (!is_numeric($amount)) {
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, "amount为非数字");
    }
    if (!is_numeric($currency)) {
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, "currency为非数字");
    }
    if (!is_numeric($status)) {
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, "status为非数字");
    }
    if (!is_numeric($mode)) {
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, "mode为非数字");
    }
    ToolMySql::conn();
    $result = Game::userCharge($userId, $amount, $currency, $status, $mode);

    if($result)
        StatusCode::responseSuccess(StatusCode::SUCCESS, '');
    else
        StatusCode::responseError(StatusCode::SYS_MYSQL_QUERY_ERROR, '添加出错');
    ToolMySql::close();
} catch (Exception $e) {
    echo $e->getMessage();
}

