<?php
/**
 * Created by YX.
 * Date: 2019/7/22
 * Time: 11:33
 */


include_once dirname(__FILE__) . "/../../util/postJson.php";
include_once dirname(__FILE__) . "/../../util/toolTime.php";
include_once dirname(__FILE__) . "/../../util/toolRedis.php";
include_once dirname(__FILE__) . "/../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../configBackend.php";


try {
    $jsonPost = new GamePostOrGetJson();
    $agentId = $jsonPost->getStr("agentId");
    $token = $jsonPost->getStr("token");
    $tel = ToolMySql::sqlfilter($jsonPost->getStr("tel"));
    $name = ToolMySql::sqlfilter($jsonPost->getStr("name"));
    $password = ToolMySql::sqlfilter($jsonPost->getStr("password"));

    $tokenDb = ToolRedis::get()->hGet("backend:agenttoken:h", $agentId);
    $tokenAdmin = ToolRedis::get()->hGet("backend:agenttoken:h", "admin");
    if (($tokenDb === false || $tokenDb != $token)
        && ($tokenAdmin === false || $tokenAdmin != $token)) {

        $data["result"] = "token错误";
        echo json_encode($data);
        return;
    }

    ToolMySql::conn(ConfigBackend::SQL_HOST,
        ConfigBackend::SQL_USER,
        ConfigBackend::SQL_PASSWORD,
        ConfigBackend::SQL_DB,
        ConfigBackend::SQL_PORT);

    $resTelCount = ToolMySql::query("SELECT COUNT(1) FROM t_agent WHERE f_tel='$tel'");
    if ($resTelCount->fetch_row()[0] > 0) {
        echo json_encode(array("result"=>"tel exist."), JSON_UNESCAPED_UNICODE);
        return;
    }

    ToolMySql::query("INSERT INTO t_agent (f_higherUid,f_password,f_name,f_tel,f_state,f_level,f_remark) VALUES (0,'$password','$name','$tel','0',0,'')");

    ToolMySql::close();

    echo json_encode(array("result"=>"success"), JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo $e->getMessage();
}

