<?php
/**
 * Created by YX.
 * Date: 2019/5/17
 * Time: 17:45
 */
include_once dirname(__FILE__) . "/../config.php";
class ToolMySql
{
    /** @var  mysqli */
    static $_conn;
    static $_conn_gameServer;
    static function conn($host=Config::SQL_HOST, $username=Config::SQL_USER, $passwd=Config::SQL_PASSWORD, $dbname=Config::SQL_DB, $port=Config::SQL_PORT)
    {
        if (null == ToolMySql::$_conn) {
            ToolMySql::$_conn = new mysqli($host, $username, $passwd, $dbname, $port);
        }
    }

    static function  conn_gameServer($host=Config::HB_SQL_HOST, $username=Config::HB_SQL_USER, $passwd=Config::HB_SQL_PASSWORD, $dbname=Config::HB_SQL_DB, $port=Config::HB_SQL_PORT) {
        if (null == ToolMySql::$_conn_gameServer) {
            ToolMySql::$_conn_gameServer = new mysqli($host, $username, $passwd, $dbname, $port);
        }
    }
    /**
     * @param $query
     * @return bool|mysqli_result
     */
    static function query($query)
    {
        ToolMySql::$_conn->query("SET NAMES 'utf8'");
        return ToolMySql::$_conn->query($query);
    }

    static function query_gameServer($query) {
        ToolMySql::$_conn_gameServer->query("SET NAMES 'utf8'");
        return ToolMySql::$_conn_gameServer->query($query);
    }

    /**
     * 设置手动/自动事务
     * @param bool $result
     */
    static function setAutocommit(bool $result) {
        ToolMySql::$_conn->autocommit($result);
    }

    /**
     * commit
     */
    static function commit() {
        ToolMySql::$_conn->commit();
    }

    /**
     * rollback
     */
    static function rollback() {
        ToolMySql::$_conn->rollback();
    }

    static function close()
    {
        ToolMySql::$_conn->close();
        ToolMySql::$_conn = null;
    }

    static function close_gameServer()
    {
        ToolMySql::$_conn_gameServer->close();
        ToolMySql::$_conn_gameServer = null;
    }

    static function sqlfilter($string){
        $string = str_ireplace("<","",$string);
        $string = str_ireplace(">","",$string);
        $string = str_ireplace("&","",$string);
        $string = str_ireplace(" ","",$string);
        $string = str_ireplace("\\","",$string);
        $string = str_ireplace("\"","",$string);
        $string = str_ireplace("'","",$string);
        $string = str_ireplace("*","",$string);
        $string = str_ireplace("%5C","",$string);
        $string = str_ireplace("%22","",$string);
        $string = str_ireplace("%27","",$string);
        $string = str_ireplace("%2A","",$string);
        $string = str_ireplace("~","",$string);
        $string = str_ireplace("select", "", $string);
        $string = str_ireplace("insert", "", $string);
        $string = str_ireplace("update", "", $string);
        $string = str_ireplace("delete", "", $string);
        $string = str_ireplace("union", "", $string);
        $string = str_ireplace("into", "", $string);
        $string = str_ireplace("load_file", "", $string);
        $string = str_ireplace("outfile", "", $string);
        $string = str_ireplace("sleep", "", $string);
        $string = strip_tags($string);
        $string = trim($string);
        return $string;
    }
}
