<?php
include_once dirname(__FILE__) . "/../config.php";
include_once dirname(__FILE__) . "/../util/toolTime.php";
class CheckParam {
    /**
     * 检测开始`结束时间
     * @param $startDate
     * @param $endDate
     * @return array|bool
     */
    static function checkSEDate($startDate, $endDate) {
        $result = array();
        if (!$startDate || !$endDate) {
            $result["startDate"] = "";
            $result["endDate"] = "";
            return $result;
        }

        if ($endDate && $startDate) {
            if (ToolTime::isDate($startDate) && ToolTime::isDate($endDate) ) {
                $result["startDate"] = $startDate;
                $result["endDate"] = $endDate;
                return $result;
            }
        }
        return  false;
    }

    static function checkPageLimit($page, $limit) {
        $result = array();
        if (!$page || !$limit) {
            $result['page'] = Config::page;
            $result['limit'] = Config::limit;
            return $result;
        }

        if (is_numeric($page) && is_numeric($limit)) {
            $page = (int)$page;
            $limit = (int)$limit;
            if ($page >= 1 && $limit >= 1) {
                $result['page'] = $page;
                $result['limit'] = $limit;
                return $result;
            }
        }
        return false;
    }

}