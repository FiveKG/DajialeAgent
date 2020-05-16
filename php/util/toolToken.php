<?php
include_once dirname(__FILE__) . "/toolRedis.php";

class toolToken {
    /**
     * @param $agentId
     * @return bool
     */
    static  function  verifyToken($agentId) {
        $token = $_SERVER['HTTP_TOKEN'];
        $tokenDb = ToolRedis::get()->hGet("backend:agenttoken:h", $agentId);

        $tokenAdmin = ToolRedis::get()->hGet("backend:agenttoken:h", "admin");
        if (($tokenDb === false || $tokenDb != $token)
            && ($tokenAdmin === false || $tokenAdmin != $token)) {
            return false;
        }
        return true;
    }
}



