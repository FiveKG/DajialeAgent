<?php
include_once dirname(__FILE__) . "/../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../util/toolTime.php";
include_once dirname(__FILE__) . "/../util/toolRedis.php";
include_once dirname(__FILE__) . "/../util/logger.php";

class ToolForSyn {
    /**
     * 返回账单表格
     * @param $data
     * @param $filename
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Reader_Exception
     * @throws PHPExcel_Writer_Exception
     * @return bool
     */
    static function generateSql2Excel($data,$filename) {
        require_once dirname(__FILE__) . '/phpexcel/PHPExcel/IOFactory.php';
        require_once dirname(__FILE__) . '/phpexcel/PHPExcel.php';
        require_once dirname(__FILE__) . '/phpexcel/PHPExcel/Writer/Excel2007.php';
        setlocale(LC_ALL, 'zh_CN');
        $header_arr = array('A','B','C','D','E','F','G','H','I','J','K','L','M', 'N','O','P','Q','R','S','T','U','V','W','X','Y','Z');

        $objPHPExcel = new PHPExcel();						//初始化PHPExcel(),不使用模板
//        $template = $filename.'.xls';			//使用模板
//        $objPHPExcel = PHPExcel_IOFactory::load($template);  	//加载excel文件,设置模板

        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);

        $dirname = dirname(dirname(__FILE__)) . '/'."$filename";

        //接下来就是写数据到表格里面去
        $objActSheet = $objPHPExcel->getActiveSheet();
        $objActSheet->setCellValue('A1',  "ID");
        $objActSheet->setCellValue('B1',  "username");
        $objActSheet->setCellValue('C1',  "charge_total");
        $objActSheet->setCellValue('D1',  "consume_total");
        $objActSheet->setCellValue('E1',  "tel");
        $objActSheet->setCellValue('F1',  "create_at");
        $objActSheet->setCellValue('G1',  "from_uid");


        $i = 2;//第3条开始
        foreach ($data as $agentInfo) {
            $rows = 0;
            foreach ($agentInfo as $key=>$value) {
                $objActSheet->setCellValue($header_arr[$rows].$i," ".$value);
                $rows ++;
            }
            $i++;
        }

        // 1.保存至本地Excel表格
        $objWriter->save($dirname);
        return true;
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
    static function generateSql2Excel2($data,$filename) {
        require_once dirname(__FILE__) . '/phpexcel/PHPExcel/IOFactory.php';
        require_once dirname(__FILE__) . '/phpexcel/PHPExcel.php';
        require_once dirname(__FILE__) . '/phpexcel/PHPExcel/Writer/Excel2007.php';
        setlocale(LC_ALL, 'zh_CN');
        $header_arr = array('A','B','C','D','E','F','G','H','I','J','K','L','M', 'N','O','P','Q','R','S','T','U','V','W','X','Y','Z');

        $objPHPExcel = new PHPExcel();						//初始化PHPExcel(),不使用模板
//        $template = $filename.'.xls';			//使用模板
//        $objPHPExcel = PHPExcel_IOFactory::load($template);  	//加载excel文件,设置模板

        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);

        $dirname = dirname(dirname(__FILE__)) . '/'."$filename";

        //接下来就是写数据到表格里面去id,rid,username,parent_id,create_at, tel, from_uid
        $objActSheet = $objPHPExcel->getActiveSheet();
        $objActSheet->setCellValue('A1',  "id");
        $objActSheet->setCellValue('B1',  "rid");
        $objActSheet->setCellValue('C1',  "username");
        $objActSheet->setCellValue('D1',  "parent_id");
        $objActSheet->setCellValue('E1',  "create_at");
        $objActSheet->setCellValue('F1',  "tel");
        $objActSheet->setCellValue('G1',  "from_uid");
        $objActSheet->setCellValue('H1',  "ivcode");


        $i = 2;//第3条开始
        foreach ($data as $agentInfo) {
            $rows = 0;
            foreach ($agentInfo as $key=>$value) {
                $objActSheet->setCellValue($header_arr[$rows].$i," ".$value);
                $rows ++;
            }
            $i++;
        }

        // 1.保存至本地Excel表格
        $objWriter->save($dirname);
        return true;
    }

    /**
     * 同步玩家与邀请玩家的父级关系
     */
    static function toSyn() {
        $findFromSql = "select id,from_uid from ".Config::SQL_DB.".playerusers where parent_id = '' and from_uid <>'' ";
        $result = ToolMySql::query($findFromSql);
        $findFromList = $result->fetch_all(MYSQLI_ASSOC);
        foreach($findFromList as $playerInfo) {
            $myid = $playerInfo['id'];
            $myFromId = $playerInfo['from_uid'];

            $findParentSql = "select parent_id from ".Config::SQL_DB.".playerusers where ivcode = '$myFromId'";
            $findParentResult = ToolMySql::query($findParentSql);
            $findParent = $findParentResult->fetch_assoc()['parent_id'];

            if($findParent)
            {
                $updateSql = "update ".Config::SQL_DB.".playerusers set parent_id = '$findParent' where id = '$myid'";
                $result  = ToolMySql::query($updateSql);
            }
        }
    }

    /**
     * 返回玩家的受邀玩家列表
     * @param $userId
     * @return array
     */
    static function findFromUid($userId) {
        $list = array();
        $findFromUidSql = "select id from ".Config::SQL_DB.".playerusers WHERE from_uid = '$userId'";
        $findResult = ToolMySql::query($findFromUidSql);
        $playerList = $findResult->fetch_all(MYSQLI_ASSOC);
        foreach ($playerList as $playerInfo ) {
            $list[] = $playerInfo['id'];
        }
        return $list;
    }

    static function  findParent($userId) {
        $findParentSql = "select parent_id from ".Config::SQL_DB.".playerusers where id = '$userId'";
        $result = ToolMySql::query($findParentSql);
        return $result->fetch_assoc()['parent_id'];
    }

    static function beYourUser($userId, $parent_id) {
        $updateSql = "update ".Config::SQL_DB.".playerusers set parent_id = '$parent_id' where id='$userId'";
        $result = ToolMySql::query($updateSql);
        if(!$result)
            return false;
        return true;
    }

    static function  getPlayerCC($userId) {
        $routineSql = "
             select id,username,sum(charge_total) as charge_total ,sum(consume_total) as consume_total, tel,create_at as from_uid from(
             select id,username,ifnull(sum(player_charge.charge_amount),0) as charge_total,0 as consume_total,tel,create_at from player_charge where id='$userId'
             union all
             select id,username,0 as charge_total,ifnull(sum(player_consume.consume_amount),0) as consume_total,tel,create_at from ".Config::SQL_DB.".player_consume where status ='1' and id='$userId'
             ) as total";
        $result = ToolMySql::query($routineSql);
        $getCC = $result->fetch_all(MYSQLI_ASSOC);
        return $getCC;
    }

    static function  serverSynP2A($playerId,$parent_id) {
        $fissionList1 = self::findFromUid($playerId);
        $flat = 1;
        while($flat) {
            if(sizeof($fissionList1) ===0)
                break;
            foreach ($fissionList1 as $Id) {
                $fissionList = self::findFromUid($Id);
                if (sizeof($fissionList)>0) {
                    $flat = 1;
                    $fissionList1 =  array_merge_recursive($fissionList1,$fissionList);
                }else
                    $flat = 0;
            }
            $fissionList1 = array_unique($fissionList1);
        }
        $fissionList1[]=$playerId;
        foreach ($fissionList1 as $userId) {
            if($isParent_id = self::findParent($userId))
                return "玩家'$userId'已经有代理:'$isParent_id'";
        }

        foreach ($fissionList1 as $Id) {
            if(!self::beYourUser($Id,$parent_id))
                return false;
        }

        return true;
    }

    static function findBig() {
        $findSql = "select id,from_uid from ".Config::SQL_DB.".playerusers where parent_id = ''  and create_at > '2020-03-27 00:00:00' ";
        $findResult = ToolMySql::query($findSql);

        $findList = $findResult->fetch_all(MYSQLI_ASSOC);

        $data =array();
        $from_data = array();
        foreach ($findList as $findInfo) {
            $userId = $findInfo['id'];
            $from_uid = $findInfo['from_uid'];
            $routineSql = "
             select id,username,sum(charge_total) as charge_total ,sum(consume_total) as consume_total, tel,create_at,'$from_uid' as from_uid from(
             select id,username,ifnull(sum(player_charge.charge_amount),0) as charge_total,0 as consume_total,tel,create_at from player_charge where id='$userId'
             union all
             select id,username,0 as charge_total,ifnull(sum(player_consume.consume_amount),0) as consume_total,tel,create_at from ".Config::SQL_DB.".player_consume where status ='1' and id='$userId'
             ) as total";
            $result = ToolMySql::query($routineSql);
            $getCC = $result->fetch_all(MYSQLI_ASSOC);
            if($getCC[0]['consume_total'] >500)
                $data[] = $getCC[0];

//            //查找from_uid
//            $findFromSql = "select id,rid,username,parent_id,create_at, tel, from_uid from ".Config::SQL_DB.".playerusers where from_uid = '$from_uid'";
//            $findFromResult = ToolMySql::query($findFromSql);
//            $fromList = $findFromResult->fetch_all(MYSQLI_ASSOC);
//            foreach ($fromList as $from) {
//                $from_data[] = $from;
//            }
//            var_dump($from_uid);
        }
        self::generateSql2Excel($data,'玩家CC表22.xlsx');
        //self::generateSql2Excel2($from_data,'邀请信息表22.xlsx');
    }
}

//
ToolMySql::conn();
var_dump(ToolForSyn::serverSynP2A('2020051011000687217','10283'));
var_dump(ToolForSyn::serverSynP2A('2020042016000656060','10283'));

var_dump(ToolForSyn::serverSynP2A('2020050500000693075','10290'));
var_dump(ToolForSyn::serverSynP2A('2020041814000650008','10290'));
var_dump(ToolForSyn::serverSynP2A('2020050913000693193','10290'));
var_dump(ToolForSyn::serverSynP2A('2020042416000660028','10290'));
var_dump(ToolForSyn::serverSynP2A('2020031716000555341','10290'));

var_dump(ToolForSyn::serverSynP2A('2020050112000690005','10289'));
var_dump(ToolForSyn::serverSynP2A('2020050710000690151','10289'));

var_dump(ToolForSyn::serverSynP2A('2020042012000658049','10287'));
var_dump(ToolForSyn::serverSynP2A('2020042623000667077','10287'));
var_dump(ToolForSyn::serverSynP2A('2020050115000693006','10287'));
var_dump(ToolForSyn::serverSynP2A('2020041918000650032','10287'));
var_dump(ToolForSyn::serverSynP2A('2020021310000531204','10287'));
var_dump(ToolForSyn::serverSynP2A('2020042822000669315','10287'));

var_dump(ToolForSyn::serverSynP2A('2020042810000669291','10282'));
var_dump(ToolForSyn::serverSynP2A('2020031618000558341','10282'));
var_dump(ToolForSyn::serverSynP2A('2020042800000669286','10282'));
var_dump(ToolForSyn::serverSynP2A('2020050219000687033','10282'));

var_dump(ToolForSyn::serverSynP2A('2020041818000656015','10285'));
var_dump(ToolForSyn::serverSynP2A('2020050515000693085','10285'));
var_dump(ToolForSyn::serverSynP2A('2020022116000543014','10285'));
var_dump(ToolForSyn::serverSynP2A('2020042618000661769','10285'));
var_dump(ToolForSyn::serverSynP2A('2020042216000656110','10285'));
var_dump(ToolForSyn::serverSynP2A('2020043023000678001','10285'));
var_dump(ToolForSyn::serverSynP2A('2019122710000474100','10285'));

ToolMySql::close();