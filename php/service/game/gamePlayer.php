<?php
include_once dirname(__FILE__) . "/../../util/toolForGame.php";
include_once dirname(__FILE__) . "/../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../config.php";



class GamePlayer {
    /**
     * 返回所有选手列表
     * @param $tel
     * @param $page
     * @param $limit
     * @return array
     */
    static function getAllPlayers( $page, $limit) {
        $data = array("total"=>0, 'list'=>array());

        $start = ($page-1)*$limit;
        $checkPlayerSql = "	SELECT `rid`,`open_id`,`nick`,`mobile_phone`
	                        FROM `".Config::HB_SQL_DB."`.`hb_role` where ai=0 && wxunionid <>''
	                        ORDER BY `rid` DESC LIMIT $start,$limit";
        $totalSql = "select count(rid) as total from `".Config::HB_SQL_DB."`.`hb_role` where ai=0 && wxunionid <>''";

        $checkPlayerResult = ToolMySql::query_gameServer($checkPlayerSql);
        $totalResult = ToolMySql::query_gameServer($totalSql);
        $data['total'] = $totalResult->fetch_assoc()['total'];
        $players = $checkPlayerResult->fetch_all(MYSQLI_ASSOC);

        foreach ($players as $player) {
            $data['list'][] = ToolForGame::playerInfo($player);
        }
        $data['total'] = (int)$data['total'];
        return $data;
    }

    /**
     * 查询一个玩家信息
     * @param $rid
     * @return array
     */
    static function  getPlayer($rid) {
        $data = array("total"=>0, 'list'=>array());
        $checkPlayerSql = "	SELECT `rid`,`open_id`,`nick`,`mobile_phone` FROM `".Config::HB_SQL_DB."`.`hb_role` where  rid = '$rid'";

        $checkPlayerResult = ToolMySql::query_gameServer($checkPlayerSql);
        if (!$checkPlayerResult->num_rows )
            return $data;
        $data['total'] = $checkPlayerResult->num_rows;
        if (!$data['total'])
            return $data;
        $player = $checkPlayerResult->fetch_all(MYSQLI_ASSOC);
        $data['list'][] = ToolForGame::playerInfo($player[0]);
        $data['total'] = (int)$data['total'];
        return $data;
    }
}