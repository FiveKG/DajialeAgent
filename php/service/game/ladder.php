<?php
include_once dirname(__FILE__) . "/../../util/toolTime.php";
include_once dirname(__FILE__) . "/../../util/toolRedis.php";
include_once dirname(__FILE__) . "/../../util/toolNet.php";
include_once dirname(__FILE__) . "/../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../player/player.php";
include_once dirname(__FILE__) . "/../../config.php";
include_once dirname(__FILE__) . "/../../util/logger.php";

class Ladder {
    /**
     * 创建天梯奖励进数据库
     * @param $rankMax
     * @param $weekGiveback
     * @param $weekTicketNumb
     * @param $weekTicketId
     * @param $monthGiveback
     * @param $monthTicketNumb
     * @param $monthTicketId
     * @return bool|string
     */
    static function createLadderAward($rankMax, $weekGiveback, $weekTicketNumb, $weekTicketId, $monthGiveback, $monthTicketNumb, $monthTicketId) {
        //开启事务
        ToolMySql::setAutocommit(false);
        $insertSql = "insert into ".Config::SQL_DB.".ladderaward (rankMax, weekGiveback, weekTicketNumb, weekTicketId, monthGiveback, monthTicketNumb, monthTicketId) 
                        values ('$rankMax','$weekGiveback','$weekTicketNumb','$weekTicketId','$monthGiveback','$monthTicketNumb', '$monthTicketId');";
        $result = ToolMySql::query($insertSql);
        if(!$result) {
            ToolMySql::rollback();
            return "创建奖励配置失败";
        }

        ToolMySql::commit();
        ToolMySql::setAutocommit(true);
        return true;
    }

    /**
     * 创建天梯配置
     * @param $id
     * @param $win
     * @param $lose
     * @param $grad
     * @return bool
     */
    static function  createLadderScore($id, $win, $lose) {
        //开启事务
        ToolMySql::setAutocommit(false);
        $insertSql = "insert into ".Config::SQL_DB.".ladderscore (id, win, lose) values ('$id','$win', '$lose');";
        $result = ToolMySql::query($insertSql);
        if(!$result) {
            ToolMySql::rollback();
            return "创建天梯配置失败";
        }
        ToolMySql::commit();
        ToolMySql::setAutocommit(true);
        return true;
    }

    /**
     * 创建称号
     * @param $scoreMax
     * @param $title
     * @return bool|string
     */
    static function createLadderTitle($scoreMax, $title) {
        //开启事务
        ToolMySql::setAutocommit(false);
        $insertSql = "insert into ".Config::SQL_DB.".laddertitle (scoreMax, title) values ('$scoreMax', '$title')";
        $result = ToolMySql::query($insertSql);
        if(!$result) {
            ToolMySql::rollback();
            return "创建称号配置失败";
        }
        ToolMySql::commit();
        ToolMySql::setAutocommit(true);
        return true;
    }
}