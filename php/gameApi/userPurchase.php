<?php
/**
 * Created by YX.
 * Date: 2020-02-05
 * Time: 11:00
 * 玩家消费
 */

include_once dirname(__FILE__) . "/../util/postJson.php";


try {
    $jsonPost = new GamePostOrGetJson();
    $userId = $jsonPost->getStr("userId");
    $item = $jsonPost->getStr("item");
    $money = $jsonPost->getStr("money");
    // 更多必要数据

    // todo: 数据库记录消费记录

    echo "success";
} catch (Exception $e) {
    echo $e->getMessage();
}

