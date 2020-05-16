<?php
include_once dirname(__FILE__) . "/../../util/postJson.php";

try {
    $jsonPost = new GamePostOrGetJson();
    $verFile = $jsonPost->getStr("verFile");
    var_dump('游戏服务端接受到的接口:',$verFile);
} catch (Exception $e) {
    echo $e->getMessage();
}