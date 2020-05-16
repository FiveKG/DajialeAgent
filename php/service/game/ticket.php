<?php
include_once dirname(__FILE__) . "/../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../config.php";

class Ticket {
    /**
     * 生成门票
     * @param $count
     * @param $type
     * @return bool|mysqli_result
     */
    static function generateTicket($count,$type) {
        $insert = '';
        for ($i =0; $i < (int)$count; $i++) {
            $hash = strtoupper(md5(uniqid()));
            $ticket = substr($hash, 0, 8) .
                '-' .
                substr($hash, 8, 6) .
                '-' .
                substr($hash, 14, 6) .
                '-' .
                substr($hash, 20, 6) .
                '-' .
                substr($hash, 26, 12) ;

            $insert = $insert."('$ticket','0','$type'),";
        }
        $insert = substr($insert, 0, -1);

        $insertSql = "insert into ".Config::SQL_DB.".ticket values".$insert;
        return ToolMySql::query($insertSql);
    }

    /**
     * 返回门票列表
     * @param $page
     * @param $limit
     * @param $statue
     * @param $type
     * @return array|bool
     */
    static function getAllTicket($page, $limit, $statue, $type) {
        $data = array('total'=>0, "list"=>array());
        $start = ($page-1)*$limit;
        $where = '';
        if (is_numeric($statue))
            $where = $where."where status = '$statue'";

        if (is_numeric($type))
            $where = $where."and type='$type'";
        $sql = "select * from ".Config::SQL_DB.".ticket $where order by id limit $start,$limit";
        $totalSql = "select count(*) as total from ".Config::SQL_DB.".ticket ".$where;

        $result = ToolMySql::query($sql);
        if(!$result)
            return  false;

        $totalResult = ToolMySql::query($totalSql);
        $data['total'] = $totalResult->fetch_assoc()['total'];
        $data['list'] = $result->fetch_all(MYSQLI_ASSOC);
        $data['total'] = (int)$data['total'];
        return $data;
    }

    /**
     * 获取一张门票
     * @param $id
     * @return array
     */
    static function  getTicket($id) {
        $data = array("total"=>0, "list"=> array());
        $sql = "select * from ".Config::SQL_DB.".ticket where id = '$id'";

        $result = ToolMySql::query($sql);
        $data['list']= $result->fetch_all(MYSQLI_ASSOC);
        $data['total'] = $result->num_rows;
        return $data;
    }
}