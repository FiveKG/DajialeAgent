<?php
include_once dirname(__FILE__) . "/../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../util/toolTime.php";
include_once dirname(__FILE__) . "/../../util/toolForAgent.php";
include_once dirname(__FILE__) . "/getAgentProfit.php";
include_once dirname(__FILE__) . "/../../util/toolRunTime.php";
include_once dirname(__FILE__) . "/../../util/logger.php";

class GenerateAgentBill {
    /**
     * 在bill下生成账单
     * @return string
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Reader_Exception
     * @throws PHPExcel_Writer_Exception
     */
    static function generateBill() {
        $allAgentSql = "select id,username,tel,profit, `level`, parent_id, level3 from ".Config::SQL_DB.".agentusers where status = 1 and level >0 and profit >0;";
        $allAgentResult = ToolMySql::query($allAgentSql);
        $allAgentList = $allAgentResult->fetch_all(MYSQLI_ASSOC);

        $data = array("total"=>sizeof($allAgentList),"amount"=>0,"list"=>array());
        $filename = date('YmdHis',ToolTime::getLocalSec()).'.xlsx';
        foreach ($allAgentList as $agentInfo) {
            $agentId = $agentInfo['id'];
            $username = $agentInfo['username'];
            $tel = $agentInfo['tel'];
            $profit = $agentInfo['profit'];
            $level = $agentInfo['level'];
            $parent_id = $agentInfo['parent_id'];
            $level3 = $agentInfo['level3'];

            $currentTotalDetail = GetAgentProfit::getMyProfit($agentId);
            $currentTotalString = json_encode($currentTotalDetail);
            $currentTotal = $currentTotalDetail['ProfitFromPlayer']['fast_total'] + $currentTotalDetail['ProfitFromPlayer']['other_total'] +$currentTotalDetail['getProfitFromAgent']['fast_total'] + $currentTotalDetail['getProfitFromAgent']['other_total'];

            //获取该代理商获取过的数额
            $givenSql = "select ifnull(sum(amount),0) as given from ".Config::SQL_DB.".bill where agentId = '$agentId';";
            $givenResult = ToolMySql::query($givenSql);
            $given = round($givenResult->fetch_assoc()['given'],2);
            $canGet = round($currentTotal - $given,2);
            if($canGet<=0)//限制多少可以领取
                continue;

            //插入记录
            $insertSql = "INSERT INTO `".Config::SQL_DB."`.`bill`(`agentId`,`current_total`,`amount`)	VALUES ('$agentId','$currentTotalString','$canGet');";
            $insertResult = ToolMySql::query($insertSql);
            if(!$insertResult)
                continue;

            $data['amount'] += $canGet;
            $data['list'][] = array('id'=>$agentId, 'username'=>$username, 'tel'=>' '.$tel, 'profit'=>$profit, 'level'=>$level, 'parent_id'=>$parent_id,'level3'=>$level3,'currentTotal'=>$currentTotal,'canGet'=>$canGet);
        }
        ToolForAgent::generateBill2Excel($data,$filename);
        $PHP_SELF=$_SERVER['PHP_SELF'];
        $url='http://'.$_SERVER['HTTP_HOST'].substr($PHP_SELF,0,strrpos($PHP_SELF,'/')+1)."bill/$filename";
        return $url;
    }
}