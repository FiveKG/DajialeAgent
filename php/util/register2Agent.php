<?php
include_once dirname(__FILE__) . "/../config.php";
include_once dirname(__FILE__) . "/../util/toolTime.php";
include_once dirname(__FILE__) . "/../util/logger.php";
include_once dirname(__FILE__) . "/../util/toolNet.php";
include_once dirname(__FILE__) . "/../mysql/toolMySql.php";

class Register2Agent {
    /**
     * 记录注册信息并且发送给承接商
     * @param $request
     * @return bool|string
     */
    static function saveRecord($request) {
        $requestString = json_encode($request);
        $responseString = ToolNet::sendByPost(Config::Agent_Url_Real, $request);
        $response  = json_decode($responseString);
        /**
         * response:{
            "code": 0,
            "msg": "",
            "agentInviteCode": "123456",
            "agentId": 1111111
            }
         */
        //存入数据库
        $insertSql = "INSERT INTO `operation`.`register`(`request`,`response`) VALUES ('$requestString','$responseString')";
        //code非0即不成功
        if($response->code !== 0) {
            $insertSql = "INSERT INTO `operation`.`register` (`request`, `response`, `is_error`) VALUES ('$requestString', '$responseString', '0')";
        }

        $result = ToolMySql::query($insertSql);
        if (!$result)
            return "插入承接商记录失败，sql:".$insertSql;
        return true;
    }

    /**
     * 处理出错的信息
     */
    static function findError() {
        $Number = Config::Register_Error_Number;
        //每次选择五个错误次数最少的来发送
        $sql = "select id, request from operation.register where is_error <> 0  order by is_error  limit 0,$Number ;";
        $result = ToolMySql::query($sql);

        if (!$result->num_rows)
            return;
        $row = $result->fetch_all(MYSQLI_ASSOC);
        foreach ($row as $info) {
            $id = $info['id'];
            $request = json_decode($info['request']);
            $responseString = ToolNet::sendByPost(Config::Agent_Url, $request);
            $response  = json_decode($responseString);
            if(!is_object($response)) {
                $updateSql = "update operation.register set is_error = is_error+1,response='$responseString' where id = '$id'";
                $result = ToolMySql::query($updateSql);
                if (!$result)
                    Logger::debug('修改register出错,sql: '.$updateSql);
                return;
            }
            //如果出错增加错误次数
            if($response->code === 0) {
                $updateSql = "update operation.register set is_error = 0,response='$responseString' where id = '$id'";
            }
            else {
                $updateSql = "update operation.register set is_error = is_error+1 where id = '$id'";
            }
            $result = ToolMySql::query($updateSql);
            if (!$result)
                Logger::debug('修改register出错,sql: '.$updateSql);
        }
    }
}
//ToolMySql::conn();
//Register2Agent::findError();
//ToolMySql::close();

