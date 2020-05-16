<?php
include_once dirname(__FILE__) . "/../../util/toolTime.php";
include_once dirname(__FILE__) . "/../../util/toolRedis.php";
include_once dirname(__FILE__) . "/../../util/toolNet.php";
include_once dirname(__FILE__) . "/../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../player/player.php";
include_once dirname(__FILE__) . "/../../config.php";
include_once dirname(__FILE__) . "/../../util/logger.php";
include_once dirname(__FILE__) . "/../../util/dajialeService.php";
include_once dirname(__FILE__) . "/../../util/register2Agent.php";

class Admin {
    /**
     * 创建管理员
     * @param $requireDate
     * @return bool|mysqli_result
     */
    static function addAdmin($requireDate) {
        $id = uniqid();
        $key = "id";
        $value = "'$id'";
        foreach ($requireDate as $keys => $values) {
            if($keys ==='password') {
                $values = password_hash($values, PASSWORD_DEFAULT);
            }
            $key =$key.' ,'.$keys;
            $value = $value .' ,'."'$values'";
        }

        $addAdminSql = "insert into adminusers ($key) values ($value)";
        $addAdminResult = ToolMySql::query($addAdminSql);
        return $addAdminResult;
    }

    /**
     * 获取管理员列表
     * @return array
     */
    static function getAdminList() {
        $data = array("list"=>array(),'total'=>0);
        $getAdminListSql = "select id,username,weight,remark  from adminusers";
        $totalSql = "select count(id) as total from adminusers;";
        $getAdminListResult = ToolMySql::query($getAdminListSql);
        $totalSqlResult = ToolMySql::query($totalSql);

        if ($row =$totalSqlResult->fetch_assoc())
            $data['total'] = $row['total'];
        if ($row = $getAdminListResult->fetch_all(MYSQLI_ASSOC))
            $data['list'] = $row;
        $data['total'] = (int)$data['total'];
        return $data;
    }

    /**
     * 删除管理员
     * @param $userId
     * @return bool|mysqli_result
     */
    static function deleteAdmin($userId) {
        $sql = "delete from adminusers where id = '$userId'";
        $result = ToolMySql::query($sql);
        return $result;
    }
}