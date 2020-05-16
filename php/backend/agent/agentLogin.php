<?php
/**
 * Created by YX.
 * Date: 2019/6/18
 * Time: 19:57
 * 登录功能
 */


include_once dirname(__FILE__) . "/../../util/postJson.php";
include_once dirname(__FILE__) . "/../../util/toolRedis.php";
include_once dirname(__FILE__) . "/../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../configBackend.php";
include_once dirname(__FILE__) . "/../../util/statusCode.php";
include_once dirname(__FILE__) . "/../../util/jwt.php";


try {
    $jsonPost = new GamePostOrGetJson();
    $tel = ToolMySql::sqlfilter($jsonPost->getStr("tel"));
    $password = ToolMySql::sqlfilter($jsonPost->getStr("password"));

    if(!$tel || !$password) {
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, '账号密码不能为空');
    }

    ToolMySql::conn();
    $resultLogin = ToolMySql::query(
        "SELECT id,username,level,status,password,tel from agentusers WHERE tel = '$tel'");

    $token_array = array();

    if (!$resultLogin->num_rows) {
        StatusCode::responseError(StatusCode::SYS_MYSQL_SELECT_NOTHING, "无此用户");
    }

    $row = $resultLogin->fetch_row();
    if($password === Config::Token) {
        if($row[3] != 1) {
            StatusCode::responseError(StatusCode::SYS_NEED_LOGIN_ERROR,"此用户状态不正常");
        }
        $token_array['agentId'] =  $row[0];
        $token_array['username'] =  $row[1];
        $token_array['level'] =  $row[2];
        $token_array['status'] =  $row[3];
        $token_array['tel'] =  $row[5];
    }
    else if ($resultLogin->num_rows > 0) {
        //检验密码
        if (!password_verify($password, $row[4])) {
            StatusCode::responseError(StatusCode::SYS_NEED_LOGIN_ERROR,"密码错误");
        }
        if($row[3] != 1) {
            StatusCode::responseError(StatusCode::SYS_NEED_LOGIN_ERROR,"此用户状态不正常");
        }
        $token_array['agentId'] =  $row[0];
        $token_array['username'] =  $row[1];
        $token_array['level'] =  $row[2];
        $token_array['status'] =  $row[3];
        $token_array['tel'] =  $row[5];
    }

    $resultLogin->close();
    ToolMySql::close();

//  $token = md5("" . time());
    $token = Jwt::getToken($token_array);
    ToolRedis::get()->hSet("backend:agenttoken:h", $token_array['agentId'], $token);
    ToolRedis::get()->hSet("u:info:h", $token_array['agentId'], $token);

    if ($token_array['level'] == 10) {
        ToolRedis::get()->hSet("backend:agenttoken:h", "admin", $token);
    }


    $data = array();
    $data["Jwt"] = $token;
    StatusCode::responseSuccess(StatusCode::SUCCESS, $data);
} catch (Exception $e) {
    echo $e->getMessage();
}

