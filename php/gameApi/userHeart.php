<?php
/**
 * Created by YX.
 * Date: 2020-02-05
 * Time: 11:00
 * 用户心跳包，统计在线量
 */

include_once dirname(__FILE__) . "/../util/postJson.php";
include_once dirname(__FILE__) . "/../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../backend/backend.php";

try {
    $jsonPost = new GamePostOrGetJson();
    $userId = $jsonPost->getStr("userId");
    // 更多必要数据

    Backend::refreshOnline($userId);

    echo "success";
} catch (Exception $e) {
    echo $e->getMessage();
}

