<?php
include_once dirname(__FILE__) . "/../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../util/toolTime.php";
include_once dirname(__FILE__) . "/../util/toolRedis.php";
class ToolForAgent {
    static $data = array("charge_total"=>0, "consume_total"=>0, "fast_consume_total"=>0, "other_consume_total"=>0);

    static function getAllFastList() {
        return ToolRedis::get()->hkeys(gameConfig::FastConfig);
    }
    /**
     * 找代理的上级和上上级
     * @param $agentId
     * @return mixed
     */
    static function  findSupAgent($agentId) {
        $findSql = "select parent_id,level3 from ".Config::SQL_DB.".agentusers where id = '$agentId';";
        $result = ToolMySql::query($findSql);
        $agentInfo= $result->fetch_all(MYSQLI_ASSOC)[0];
        return $agentInfo;
    }

    /**
     * 获取代理的玩家，分类型
     * @param $agentId 代理商id
     * @param $request  请求返回的玩家数据
     * @param $type {all:所有玩家,notPro:'出去推广员',justPro:'仅仅推广员'}
     * @return mixed
     */
        static function getPlayerOfAgent($agentId, $request, $type) {
               switch ($type)
               {
                   case "all":
                       //获取所有玩家
                       $sql = "select ".$request." from ".Config::SQL_DB.".playerusers where parent_id = '$agentId';";
                       break;
                   case "notPro":
                       //除去推广员玩家
                       $sql = "select ".$request." from ".Config::SQL_DB.".playerusers where parent_id = '$agentId' and level = 0;";
                       break;
                   case "justPro":
                       //只是推广员
                       $sql =  $sql = "select ".$request." from ".Config::SQL_DB.".playerusers where parent_id = '$agentId' and level <> 0;";
                       break;
                   default:
                       break;
               }

               $result = ToolMySql::query($sql);
               $playerList = $result->fetch_all(MYSQLI_ASSOC);
               return $playerList;
        }

    /**
     * 日历表里获取数据
     * @param $agentId 用户id
     * @param $date 查询日期
     * @param $role 查看角色，1为下级代理商，2为代理玩家
     * @param $type type = array("fission", "direct", "all")
     * @return array|null
     *
     */
    static function _getDataFromCalendar($agentId, $date, $role, $type) {
        $sql = "select all_total from ".Config::SQL_DB.".calendar  where agentId = '$agentId' and date_list='$date' and role='$role'";
        if($type === "fission") {
            $sql = "select fission_total from ".Config::SQL_DB.".calendar  where agentId = '$agentId' and date_list='$date' and role='$role'";
        }
        elseif ($type ==="direct" ) {
            $sql = "select direct_total from ".Config::SQL_DB.".calendar  where agentId = '$agentId' and date_list='$date' and role='$role'";
        }
        $result = ToolMySql::query($sql);
        $row = $result->fetch_assoc();
        $data = json_decode($row[$type.'_total'],true);
        return $data;
    }

    /**
     * 插入/更新日历表
     * @param $agentId 用户id
     * @param $chargeTotal 充值总额
     * @param $consumeTotal  消费总额
     * @param $date   日期
     * @param $role    查看角色，1为下级代理商，2为代理玩家
     * @param $type type = array("fission", "direct", "all")
     * @return bool
     */
    static function _insertCalendar($agentId, $data, $date, $role, $type) {
        $total = json_encode($data);
        //如果同样的日期已经存在，则更新，否则插入
        $searchSql = "select id from ".Config::SQL_DB.".calendar where agentId = '$agentId' and date_list= '$date' and role = '$role'";
        $searchResult = ToolMySql::query($searchSql);
        $searchId = $searchResult->fetch_assoc()['id'];

        $insertSql = "INSERT INTO ".Config::SQL_DB.".calendar(agentId,all_total,date_list,role)VALUES('$agentId', '$total' , '$date','$role')";
        $updateSql = "update ".Config::SQL_DB.".calendar set all_total = '$total' where id = '$searchId'";
        if($type === "fission") {
            $insertSql = "INSERT INTO ".Config::SQL_DB.".calendar(agentId,fission_total,date_list,role)VALUES('$agentId','$total','$date','$role')";
            $updateSql = "update ".Config::SQL_DB.".calendar set fission_total = '$total' where id = '$searchId'";
        } elseif ($type === "direct") {
            $insertSql = "INSERT INTO ".Config::SQL_DB.".calendar(agentId,direct_total,date_list,role)VALUES('$agentId','$total','$date','$role')";
            $updateSql = "update ".Config::SQL_DB.".calendar set direct_total = '$total' where id = '$searchId'";
        }

        if($searchId) {
            if (ToolMySql::query($updateSql) !== true) {
                return false;
            }
        }
        else {
            if (ToolMySql::query($insertSql) !== true) {
                return false;
            }
        }
        return true;
    }

    /**
     * 获取我的所有状态正常下属代理商的分成和id列表
     * @param $agentId
     * @return mixed
     */
    static function getMySubList($agentId) {
        $selectSubAgentSql = "select id, level, parent_id , profit from ".Config::SQL_DB.".agentusers where level3 = '$agentId' and status = 1 or parent_id = '$agentId' and status = 1 ";
        $result = ToolMySql::query($selectSubAgentSql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * 获得我的分成和等级
     * @param $agentId
     * @return float
     */
    static function getMyPercentageAndLevel($agentId) {
        $myAgentSql = "select level,profit from ".Config::SQL_DB.".agentusers where id = '$agentId' and status = 1";
        $myAgentProfitResult = ToolMySql::query($myAgentSql);
        return  $myAgentProfitResult->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * 获取一个代理商的所有下级代理商总额
     * @param $agentId
     * @return array
     */
    static function getAgentTotal($agentId) {
        $data = self::$data;
        $myAgentListSql = "select id from ".Config::SQL_DB.".agentusers where parent_id = '$agentId';";

        $myAgentList = ToolMySql::query($myAgentListSql);
        while ($row = $myAgentList->fetch_assoc()["id"]) {
            $result = self::getPlayerTotal2($row);
            $data["charge_total"] += $result["charge_total"];
            $data["consume_total"] += $result["consume_total"];
            $data["fast_consume_total"] += $result["fast_consume_total"];
            $data["other_consume_total"] += $result["other_consume_total"];
        }
        $data["charge_total"] = round( $data["charge_total"],2);
        $data["consume_total"] = round( $data["consume_total"],2);
        $data["fast_consume_total"] = round( $data["fast_consume_total"],2);
        $data["other_consume_total"] = round( $data["other_consume_total"],2);

        return $data;
    }

    /**
     * 按时间返回代理玩家总额,可精准到月和日
     * @param $agentId 代理商id
     * @param $date 查询年月:xxxx-xx 查询天:xxxx-xx-xx
     * @param $type 类型:["all", "direct", "fission"]
     * @param $match ['race']
     * @return array|null
     */
    static function getPlayerCCByDate($agentId, $date, $type, $match = "race") {
        $role = 2;//2代表代理查询玩家
        //如果日期格式是天/月
        if (preg_match('/\d{4}-\d{1,2}-\d{1,2}/', $date)) {
            $dayOrMonth = ToolTime::getToday();
        }
        else if (preg_match('/\d{4}-\d{1,2}/', $date)) {
            $dayOrMonth = ToolTime::getMonth();
        }
        //判断查询日期是否不是今天/这个月,如果不是，calendar又存在这天/月数据,可以避免全表查询
        if ($date !== $dayOrMonth) {
            $dataFromCalendar = self::_getDataFromCalendar($agentId, $date, $role, $type);
            if ($dataFromCalendar) {
                return $dataFromCalendar;
            }
        }

        $data = ToolForAgent::getPlayerTotal2($agentId,$type, $match, $date);
        if ($date !== $dayOrMonth) {
            self::_insertCalendar($agentId, $data, $date, $role, $type);
        }
        return $data;
    }

    /** 按时间返回所有下级代理商总额
     * @param $agentId 代理商id
     * @param $date 查询年月:xxxx-xx 查询天:xxxx-xx-xx
     * @param $type 类型，全部，直属，分裂
     * @return array|null
     */
    static function getAgentCCByDate($agentId, $date, $type) {
        $data = array("charge_total"=>0, "consume_total"=>0, "fast_consume_total"=>0, "other_consume_total"=>0);
        $role = 1;
        $dayOrMonth = "";

        //如果日期格式是天/月
        if (preg_match('/\d{4}-\d{1,2}-\d{1,2}/', $date)) {
            $dayOrMonth = ToolTime::getToday();
            //var_dump('查询代理的是天');
        }
        else if (preg_match('/\d{4}-\d{1,2}/', $date)) {
            $dayOrMonth = ToolTime::getMonth();
            //var_dump('查询代理的是月');
        }

        //先查一下日历表有没有数据
        if ($date !== $dayOrMonth) {
            $dataFromCalendar = self::_getDataFromCalendar($agentId, $date, $role, $type);
            if ($dataFromCalendar) {
                return $dataFromCalendar;
            }
        }

        //我的所有下级列表
        $myAgentList = ToolForAgent::getMySubList($agentId);
        if(sizeof($myAgentList) > 0) {
            foreach ($myAgentList as $agent) {
                $result = self::getPlayerCCByDate($agent['id'], $date, $type);
                $data["charge_total"] += $result["charge_total"];
                $data["consume_total"] += $result["consume_total"];
                $data["fast_consume_total"] += $result["fast_consume_total"];
                $data["other_consume_total"] += $result["other_consume_total"];
            }
            if ($date !== $dayOrMonth) {
                self::_insertCalendar($agentId, $data, $date, $role,$type);
            }
        }
        return $data;
    }

    /**
     * 获取所有玩家(直属和分裂)充值，消费，快速赛消费，其他赛消费总额
     * @param $agentId
     * @param  $type 玩家类型,默认全部['all', 'direct', 'fission']
     * @param $match 筛选赛事类型 ['race']
     * @param $date 赛选日期"xxxx-xx-xx/ xxxx-xx"
     * @return array {"charge_total":number, "consume_total":number, "fast_consume_total":number, "other_consume_total":number}
     */
    static function getPlayerTotal2($agentId, $type ="all", $match="race", $date="") {
        $data = self::$data;
        //筛选玩家类型
        switch ($type)
        {
            case "all":
                $typeSql = "select id from ".Config::SQL_DB.".playerusers where parent_id = '$agentId' ";
                break;
            case "direct" :
                $typeSql = " select id from ".Config::SQL_DB.".playerusers where parent_id = '$agentId' and from_uid =''";
                break;
            case "fission":
                $typeSql = "select id from ".Config::SQL_DB.".playerusers where parent_id = '$agentId' and from_uid <>''";
                break;
            default:
                break;
        }

        //筛选出哪些赛事,目前快速赛有快速赛和好友赛
        switch ($match)
        {
            case "race":
                $fastLocaleId = self::getAllFastList();
                break;
            default:
                //默认快速赛
                $fastLocaleId = self::getAllFastList();
                break;
        }

        if(!$date) {
            $chargeDateSql = "";
            $consumeDateSql = "";
        }
        else if (preg_match('/\d{4}-\d{1,2}-\d{1,2}/', $date)) {
            $chargeDateSql =  "where DATEDIFF(charge.create_at,'$date')=0";
            $consumeDateSql = "and DATEDIFF(consume.create_at,'$date')=0";
        }
        else if (preg_match('/\d{4}-\d{1,2}/', $date)) {
            $chargeDateSql = "where strcmp(date_format(charge.create_at,'%Y-%m'),'$date')=0";
            $consumeDateSql = "and strcmp(date_format(consume.create_at,'%Y-%m'),'$date')=0";
        }

        //玩家充值
        $chargeSql = "select ifnull(sum(charge_amount),0) as charge_total from ".Config::SQL_DB.".charge as charge inner join ($typeSql)as player on charge.user_id = player.id $chargeDateSql";
        $chargeResult = ToolMySql::query($chargeSql);
        $chargeTotal = $chargeResult->fetch_assoc()['charge_total'];

        //玩家消耗
        $consumeSql = "select consume_amount,localeId from ".Config::SQL_DB.".consume as consume inner join
                       ($typeSql)as players on consume.user_id = players.id where consume.status = '1'  $consumeDateSql";
        $consumeResult = ToolMySql::query($consumeSql);
        $consumeList = $consumeResult->fetch_all(MYSQLI_ASSOC);

        foreach ($consumeList as $consumeInfo) {
            //总
            $data['consume_total'] += $consumeInfo['consume_amount'];
            //快速
            if(in_array($consumeInfo['localeId'],$fastLocaleId )){
                $data["fast_consume_total"] += $consumeInfo['consume_amount'];
            }
        }

        $data["charge_total"] = $chargeTotal;
        $data["other_consume_total"] = $data['consume_total'] - $data["fast_consume_total"];
        return $data;
    }


    /**
     * 返回账单表格
     * @param $data
     * @param $filename
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Reader_Exception
     * @throws PHPExcel_Writer_Exception
     * @return bool
     */
    static function generateBill2Excel($data,$filename) {
        require_once dirname(__FILE__) . '/phpexcel/PHPExcel/IOFactory.php';
        require_once dirname(__FILE__) . '/phpexcel/PHPExcel.php';
        require_once dirname(__FILE__) . '/phpexcel/PHPExcel/Writer/Excel2007.php';
        setlocale(LC_ALL, 'zh_CN');
        $header_arr = array('A','B','C','D','E','F','G','H','I','J','K','L','M', 'N','O','P','Q','R','S','T','U','V','W','X','Y','Z');

        $objPHPExcel = new PHPExcel();						//初始化PHPExcel(),不使用模板
//        $template = $filename.'.xls';			//使用模板
//        $objPHPExcel = PHPExcel_IOFactory::load($template);  	//加载excel文件,设置模板

        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);

        $dirname = dirname(dirname(__FILE__)) . '/backend/agent/bill/'."$filename";

        //接下来就是写数据到表格里面去
        $objActSheet = $objPHPExcel->getActiveSheet();
        $total = $data['total'];
        $amount = $data['amount'];
        $objActSheet->setCellValue('A1',  "总额：$total");
        $objActSheet->setCellValue('C1',  "人数：$amount");
        $objActSheet->setCellValue('A2',  "ID：$amount");
        $objActSheet->setCellValue('B2',  "名字");
        $objActSheet->setCellValue('C2',  "电话");
        $objActSheet->setCellValue('D2',  "分成");
        $objActSheet->setCellValue('E2',  "等级");
        $objActSheet->setCellValue('F2',  "上级");
        $objActSheet->setCellValue('G2',  "上上级");
        $objActSheet->setCellValue('H2',  "当前总收益");
        $objActSheet->setCellValue('I2',  "可获取");

        $i = 3;//第3条开始
        $agentList = $data['list'];
        foreach ($agentList as $agentInfo) {
            $rows = 0;
            foreach ($agentInfo as $key=>$value) {
                $objActSheet->setCellValue($header_arr[$rows].$i,$value);
                $rows ++;
            }
            $i++;
        }

        // 1.保存至本地Excel表格
        $objWriter->save($dirname);
//        // 2.接下来当然是下载这个表格了，在浏览器输出就好了
//        header("Pragma: public");
//        header("Expires: 0");
//        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
//        header("Content-Type:application/force-download");
//        header("Content-Type:application/vnd.ms-execl");
//        header("Content-Type:application/octet-stream");
//        header("Content-Type:application/download");;
//        header('Content-Disposition:attachment;filename='.$filename.'');
//        header("Content-Transfer-Encoding:binary");
//        $objWriter->save('php://output');
        return true;
    }


    /**
     * 重建代理和玩家绑定
     * @param $rid
     * @param $tel
     * @param $agentId
     * @return bool|string
     */
    static function rebuildPlayer2Agent ($rid,$tel, $agentId){
        if($rid)
            $updateSql = "update ".Config::SQL_DB.".playerusers set parent_id = '$agentId', from_uid ='' where rid ='$rid'";
        if($tel)
            $updateSql =  "update ".Config::SQL_DB.".playerusers set parent_id = '$agentId', from_uid ='' where tel ='$tel'";
        $updateResult = ToolMySql::query($updateSql);
        if (!$updateResult)
            return '更新失败';
        return true;
    }

    /**
     * 重新连接level3
     * @return bool
     */
    static function checkAgentChain() {
        $findL3= "select id,parent_id from agentusers where level = 3;";
        $result = ToolMySql::query($findL3);
        $list = $result->fetch_all(MYSQLI_ASSOC);
        foreach ($list as $user) {
            $parent_id = $user['parent_id'];
            $id = $user['id'];

            $select = "select parent_id from agentusers where id = '$parent_id'";
            $result = ToolMySql::query($select);
            $parent_id2 = $result->fetch_assoc()['parent_id'];

            $update = "update agentusers set level3 = '$parent_id2' where id = '$id'";
            ToolMySql::query($update);
        }
        return true;
    }
}
//ToolMySql::conn();
////ToolForAgent::getAllFastMatchList();
////ToolForAgent::getPlayerOfAgent(10033, 'id', 'all');
//ToolForAgent::checkAgentChain();
//ToolForAgent::getPlayerTotal2('2019061418000006002');
//ToolMySql::close();