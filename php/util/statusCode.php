<?php
class StatusCode {
    //状态码常量
    const SUCCESS = 20000;
    const SYS_MYSQL_QUERY_ERROR = 40001;
    const SYS_QUERY_PARAMS_ERROR = 40002;
    const SYS_SAVE_FILE_ERROR = 40003;
    const SYS_NEED_LOGIN_ERROR = 40004;
    const SYS_HAS_NOT_AUTHORITY = 40005;
    const SYS_TOKEN_VERIFY_FAIL = 40006;
    const SYS_REDIS_QUREY_ERROR = 40007;
    const SYS_MYSQL_SELECT_NOTHING = 40008;
    const SYS_USER_NAME_IS_EXIST = 40009;
    const SYS_USER_REGISTER_ERROR = 40010;

    const COMMON_NETWORK_ERROR = 100000;

    static function responseSuccess($statusCode, $data = null) {
        $result["code"] = $statusCode;
        $result['data'] = $data;
        header('Content-Type:application/json; charset=utf-8');
        exit(json_encode($result));
    }
    static function  responseError($statusCode, $message='') {
        $result["code"] = $statusCode;
        $result['message'] = $message;
        header('Content-Type:application/json; charset=utf-8');
        exit(json_encode($result));
    }
}

