<?php
include_once dirname(__FILE__) . "/../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../util/toolTime.php";
include_once dirname(__FILE__) . "/../util/toolRedis.php";
include_once dirname(__FILE__) . "/../util/toolNet.php";
include_once dirname(__FILE__) . "/../util/logger.php";
include_once dirname(__FILE__) . "/../util/dajialeService.php";

class ToolForSyn2DJL {
    /**
     * 返回账单表格
     * @param $myData
     * @param $filename
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Reader_Exception
     * @throws PHPExcel_Writer_Exception
     * @return bool
     */
    static function agent2AgentExcel($data,$filename) {
        require_once dirname(__FILE__) . '/phpexcel/PHPExcel/IOFactory.php';
        require_once dirname(__FILE__) . '/phpexcel/PHPExcel.php';
        require_once dirname(__FILE__) . '/phpexcel/PHPExcel/Writer/Excel2007.php';
        setlocale(LC_ALL, 'zh_CN');
        $header_arr = array('A','B','C','D','E','F','G','H','I','J','K','L','M', 'N','O','P','Q','R','S','T','U','V','W','X','Y','Z');

        $objPHPExcel = new PHPExcel();						//初始化PHPExcel(),不使用模板
//        $template = $filename.'.xls';			//使用模板
//        $objPHPExcel = PHPExcel_IOFactory::load($template);  	//加载excel文件,设置模板

        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);

        $dirname = dirname(dirname(dirname(__FILE__))) . '/dajiale/A2A/'."$filename";

        //接下来就是写数据到表格里面去
        $objActSheet = $objPHPExcel->getActiveSheet();
        $objActSheet->setCellValue('A1',  "ID");
        $objActSheet->setCellValue('B1',  "username");
        $objActSheet->setCellValue('C1',  "level");
        $objActSheet->setCellValue('D1',  "profit");
        //id,username,level,profit
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
     * @param $myData
     * @param $filename
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Reader_Exception
     * @throws PHPExcel_Writer_Exception
     * @return bool
     */
    static function agent2PlayerExcel($data,$filename) {
        require_once dirname(__FILE__) . '/phpexcel/PHPExcel/IOFactory.php';
        require_once dirname(__FILE__) . '/phpexcel/PHPExcel.php';
        require_once dirname(__FILE__) . '/phpexcel/PHPExcel/Writer/Excel2007.php';
        setlocale(LC_ALL, 'zh_CN');
        $header_arr = array('A','B','C','D','E','F','G','H','I','J','K','L','M', 'N','O','P','Q','R','S','T','U','V','W','X','Y','Z');

        $objPHPExcel = new PHPExcel();						//初始化PHPExcel(),不使用模板
//        $template = $filename.'.xls';			//使用模板
//        $objPHPExcel = PHPExcel_IOFactory::load($template);  	//加载excel文件,设置模板

        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);

        $dirname = dirname(dirname(dirname(__FILE__))) . '/dajiale/A2P/'."$filename";

        //接下来就是写数据到表格里面去
        $objActSheet = $objPHPExcel->getActiveSheet();
        $objActSheet->setCellValue('A1',  "ID");
        //id,username,level,profit
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
     * @param $myData
     * @param $filename
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Reader_Exception
     * @throws PHPExcel_Writer_Exception
     * @return bool
     */
    static function player2PlayerExcel($data,$filename) {
        require_once dirname(__FILE__) . '/phpexcel/PHPExcel/IOFactory.php';
        require_once dirname(__FILE__) . '/phpexcel/PHPExcel.php';
        require_once dirname(__FILE__) . '/phpexcel/PHPExcel/Writer/Excel2007.php';
        setlocale(LC_ALL, 'zh_CN');
        $header_arr = array('A','B','C','D','E','F','G','H','I','J','K','L','M', 'N','O','P','Q','R','S','T','U','V','W','X','Y','Z');

        $objPHPExcel = new PHPExcel();						//初始化PHPExcel(),不使用模板
//        $template = $filename.'.xls';			//使用模板
//        $objPHPExcel = PHPExcel_IOFactory::load($template);  	//加载excel文件,设置模板

        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);

        $dirname = dirname(dirname(dirname(__FILE__))) . '/dajiale/P2P/'."$filename";

        //接下来就是写数据到表格里面去
        $objActSheet = $objPHPExcel->getActiveSheet();
        $objActSheet->setCellValue('A1',  "ID");
        //id,username,level,profit
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
     * 同步玩家的from_uid
     */
    static function toSynFromUid(){
        $findSql = "select id, from_uid from ".Config::SQL_DB.".playerusers where from_uid <>''";
        $findResult = ToolMySql::query($findSql);
        $findList = $findResult->fetch_all(MYSQLI_ASSOC);

        foreach ($findList as $findInfo) {
            $id = $findInfo['id'];
            $from_uid = $findInfo['from_uid'];

            $findFromSql = "select ivcode from ".Config::SQL_DB.".temp where id = '$from_uid'";
            $findFromResult = ToolMySql::query($findFromSql);
            $fromIvcode = $findFromResult->fetch_assoc()['ivcode'];

            $updateSql = "update ".Config::SQL_DB.".playerusers set from_uid ='$fromIvcode' where id = '$id'";
            $updateResult = ToolMySql::query($updateSql);
            if(!$updateResult)
                return false;
        }
        return true;
    }



    /**
     * 给大家乐发送玩家和玩家之间的关系,需要用到大家乐签名
     */
    static function toSyn() {
        $findSql = "select id,from_uid from ".Config::SQL_DB.".playerusers where from_uid <> '';";
        $findResult = ToolMySql::query($findSql);
        $findList = $findResult->fetch_all(MYSQLI_ASSOC);
        foreach ($findList as $findInfo) {
            $result = DajialeService::SynP2p($findInfo['id'],$findInfo['from_uid']);
            if($result !== true) {
                Logger::debug("同步玩家关系到大家乐失败:",$findList);
                Logger::debug("失败原因:",$result);

                $id = $findInfo['id'];
                var_dump("'$id':false");
                var_dump($result);

                continue;
            }
        }
        return true;
    }

    /**
     * 返回代理的信息
     * @param $agentId
     * @return mixed
     */
    static function checkAgentInfo($agentId) {
        $selectSql = "select id,username,level,profit from ".Config::SQL_DB.".agentusers where id = '$agentId'";
        $result = ToolMySql::query($selectSql);
        $subAgents = $result->fetch_all(MYSQLI_ASSOC);
        return $subAgents[0];
    }

    /**
     * 我的下级代理
     * @param $agentId
     * @return mixed
     */
    static function findSubAgent($agentId ) {
        $selectSql = "select id,username,level,profit from ".Config::SQL_DB.".agentusers where parent_id='$agentId' and status = 1 order by create_at";
        $result = ToolMySql::query($selectSql);
        return $subAgents = $result->fetch_all(MYSQLI_ASSOC);
    }

    static function getAllAgent() {
        //查找所有代理
        $selectSql = "select id,username,level,profit from ".Config::SQL_DB.".agentusers where status = 1 and level>0";
        $result = ToolMySql::query($selectSql);
        $allAgent = $result->fetch_all(MYSQLI_ASSOC);
        return $allAgent;
    }

    static function findChildren($agentId) {
        //查找所有代理
        $selectSql = "select id from ".Config::SQL_DB.".playerusers where parent_id = '$agentId' order by create_at";
        $result = ToolMySql::query($selectSql);
        $allPlayers = $result->fetch_all(MYSQLI_ASSOC);
        return $allPlayers;
    }

    static function playerSubPlayers() {
        //查找所有有邀请人的玩家
        $selectSql = "select from_uid from ".Config::SQL_DB.".playerusers where from_uid <>'' group by from_uid";
        $result = ToolMySql::query($selectSql);
        $allPlayers = $result->fetch_all(MYSQLI_ASSOC);
        return $allPlayers;
    }

    static function getSubPlayers($inviter) {
        //查找所有有邀请人的玩家
        $selectSql = "select id from ".Config::SQL_DB.".playerusers where from_uid ='$inviter' order by create_at";
        $result = ToolMySql::query($selectSql);
        $allPlayers = $result->fetch_all(MYSQLI_ASSOC);
        return $allPlayers;
    }

    static function playerSubPlayers2() {
        //查找所有有邀请人的玩家
        $selectSql = "select id,from_uid from ".Config::SQL_DB.".playerusers where from_uid <>'' group by from_uid";
        $result = ToolMySql::query($selectSql);
        $allPlayers = $result->fetch_all(MYSQLI_ASSOC);
        return $allPlayers;
    }

    static function getSubPlayers2($inviter) {
        //查找所有有邀请人的玩家
        $selectSql = "select id from ".Config::SQL_DB.".playerusers where from_uid ='$inviter' order by create_at";
        $result = ToolMySql::query($selectSql);
        $allPlayers = $result->fetch_all(MYSQLI_ASSOC);
        return $allPlayers;
    }

    /**
     * 检查代理和代理的关系
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Reader_Exception
     * @throws PHPExcel_Writer_Exception
     * @return  bool
     */
    static function checkAgentAndAgent() {
        //查找所有代理
        $allAgent = self::getAllAgent();
        foreach ($allAgent as $agentInfo) {
            $agentId =$agentInfo['id'];
            $subAgents =self::findSubAgent($agentId);
            //写进excel表
            self::agent2AgentExcel($subAgents, $agentId.".xlsx");
        }
        return true;
    }

    static function checkAgentAndPlayer() {
        //查找所有代理的玩家
        $allAgent = self::getAllAgent();

        foreach ($allAgent as $agentInfo) {
            $agentId =$agentInfo['id'];
            $subPlayers =self::findChildren($agentId);
            //写进excel表
            self::agent2PlayerExcel($subPlayers, $agentId.".xlsx");
        }
        return true;
    }

    static  function checkPlayerAndPlayer() {
        //所有有邀请人的玩家
        $allInviter = self::playerSubPlayers();
        foreach ($allInviter as $inviter) {
            $userId = $inviter['from_uid'];
            $subPlayers = self::getSubPlayers($userId);
            //写进excel表
            self::player2PlayerExcel($subPlayers, $userId.".xlsx");
        }
        return true;
    }

//----------------------------------------------更新后检验----------------


    static function checkAgentAndPlayer2() {
        //查找所有代理的玩家
        $allAgent = self::getAllAgent();
        foreach ($allAgent as $agentInfo) {
            $agentId =$agentInfo['id'];
            $subPlayers =self::findChildren($agentId);
            //写进excel表
            self::agent2PlayerExcel($subPlayers, $agentId.".xlsx");
        }
        return true;
    }

    static  function checkPlayerAndPlayer2() {
        //所有有邀请人的玩家
        $allInviter = self::playerSubPlayers2();
        foreach ($allInviter as $inviter) {
            $ivcode = $inviter['from_uid'];
            $userId = $inviter['id'];
            $subPlayers = self::getSubPlayers2($ivcode);
            //写进excel表
            self::player2PlayerExcel($subPlayers, $userId.".xlsx");
        }
        return true;
    }
}

ToolMySql::conn();

var_dump(ToolForSyn2DJL::checkAgentAndPlayer());
var_dump(ToolForSyn2DJL::checkPlayerAndPlayer());

//
////--------------------------------更新后数据,代理和代理的不用
//var_dump(ToolForSyn2DJL::checkAgentAndPlayer2());
//var_dump(ToolForSyn2DJL::checkPlayerAndPlayer2());

//----------------------发送数据给大家乐
//var_dump(ToolForSyn2DJL::toSyn());

ToolMySql::close();