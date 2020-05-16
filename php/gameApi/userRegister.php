<?php
/**
 * Created by YX.
 * Date: 2020-02-05
 * Time: 11:00
 * 用户注册
 */

include_once dirname(__FILE__) . "/../util/postJson.php";


try {
    $jsonPost = new GamePostOrGetJson();
    $userId = $jsonPost->getStr("userId");
    $channelId = $jsonPost->getStr("channelId");
    // 更多必要数据

    // todo: 数据库记录用户注册

    echo "success";
} catch (Exception $e) {
    echo $e->getMessage();
}
