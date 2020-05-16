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
include_once dirname(__FILE__) . "/../../util/statusCode.php";
include_once dirname(__FILE__) . "/../../util/jwt.php";


try {
    $jsonPost = new GamePostOrGetJson();
    $username = ToolMySql::sqlfilter($jsonPost->getStr("username"));
    $password = ToolMySql::sqlfilter($jsonPost->getStr("password"));

    if(!$username || !$password) {
        StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, '账号密码不能为空');
    }

    $sql = "SELECT * from adminusers WHERE username = '$username'";
    ToolMySql::conn();
    $resultLogin = ToolMySql::query($sql);

    $token_array = array();

    if (!$resultLogin->num_rows) {
        StatusCode::responseError(StatusCode::SYS_MYSQL_SELECT_NOTHING, "无此用户");
    }
    if ($resultLogin->num_rows > 0) {
        $row = $resultLogin->fetch_assoc();
        //检验密码
        if (!password_verify($password, $row['password'])) {
            StatusCode::responseError(StatusCode::SYS_NEED_LOGIN_ERROR,"密码错误");
        }
        $token_array['id'] =  $row['id'];
        $token_array['username'] =  $row['username'];
        $token_array['authority'] =  $row['authority'];
        $token_array['weight'] =  $row['weight'];
        $token_array['create_at'] =  $row['create_at'];
        $token_array['sub'] =  'admin';
    }

    $resultLogin->close();
    ToolMySql::close();

//  $token = md5("" . time());
    $token = Jwt::getToken($token_array);
    ToolRedis::get()->hSet("backend:admintoken:h", $token_array['id'], $token);
    ToolRedis::get()->hSet("u:info:h", $token_array['id'], $token);

//    if ($token_array['level'] == 10) {
//        ToolRedis::get()->hSet("backend:agenttoken:h", "admin", $token);
//    }

    $data = array();
    $data['Jwt'] = $token;
    StatusCode::responseSuccess(StatusCode::SUCCESS, $data);
} catch (Exception $e) {
    echo $e->getMessage();
}

