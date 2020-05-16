<?php
include_once dirname(__FILE__) . "/../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../util/toolTime.php";
include_once dirname(__FILE__) . "/../../util/toolForAgent.php";
include_once dirname(__FILE__) . "/../../util/toolRunTime.php";
include_once dirname(__FILE__) . "/../../util/logger.php";
include_once dirname(__FILE__) . "/../promotion.php";

class GetAgentProfit {
    /**
     * 返回
     * @param $agentId
     * @return float|int
     */
    static function getMyProfit($agentId) {
        //先查找出我对所属所有玩家的利益
        $myInfo= ToolForAgent::getMyPercentageAndLevel($agentId)[0];
        $getProfitFromPlayer = self::getProfitFromPlayer($agentId, $myInfo['profit']);
        $getProfitFromAgent = self::getProfitFromAgent($agentId, $myInfo['profit'],$myInfo['level']);
        return array("ProfitFromPlayer"=>$getProfitFromPlayer, "getProfitFromAgent"=>$getProfitFromAgent);
    }

    /**
     * 收取我的直属玩家利润
     * @param $agentId
     * @param $myProfit
     * @return array
     */
    static function getProfitFromPlayer($agentId, $myProfit) {
        $allProfit = ToolForAgent::getPlayerTotal2($agentId);

        //快速赛利润
        $fastTotal = $allProfit['fast_consume_total'] * Config::ServerFee * Config::LadderAgentMax ;
        //其他赛事利润
        $otherTotal = $allProfit['other_consume_total'] * Config::ServerFee * Config::OtherAgentMax ;

//        //判断我的玩家下面是否有推广员,没有无需分推广费，有则扣去推广员的利润
//        $promoterProfit = self::getMyPromoterProfit($agentId);
//        $fastTotal = $fastTotal - $promoterProfit['fastTotal'];
//        $otherTotal = $otherTotal - $promoterProfit['otherTotal'];

        $fastTotal = $fastTotal *  ($myProfit/Config::AgentBase);
        $otherTotal = $otherTotal * ($myProfit/Config::AgentBase);
        return array("fast_total"=>$fastTotal, "other_total"=>$otherTotal);
    }


    /**
     * 收取下级代理的费用
     * @param $agentId
     * @param $myProfit
     * @param $level
     * @return array
     */
    static function getProfitFromAgent($agentId, $myProfit, $level) {
        $mySubAgentList = self::getMySubList($agentId);

        $allAgentFastTotal= 0 ;
        $allAgentOtherTotal= 0 ;
        $level = (int) $level;

        foreach ($mySubAgentList as $agentInfo) {
            $SubAgentId = $agentInfo['id'];

            $agentList[] = $SubAgentId;
            $subAgentLevel = (int)$agentInfo['level'];
            $SubAgentParent_id = $agentInfo['parent_id'];
            $SubAgentProfit = $agentInfo['profit'];
            $SubAgentConsumeTotal = ToolForAgent::getPlayerTotal2($SubAgentId);
            $SubAgentFastTotal = $SubAgentConsumeTotal['fast_consume_total'];
            $SubAgentOtherTotal = $SubAgentConsumeTotal['other_consume_total'];
            $SubAgentPromoterProfit = self::getMyPromoterProfit($SubAgentId);
            //服务费
            $serverFee = self::getServerFee($SubAgentFastTotal,$SubAgentOtherTotal);
            //如果是下级
            if($subAgentLevel - $level === 1) {
                //收益=(服务费- 推广费) * (上级分成 - 自身分成)/50
                $allAgentFastTotal += (($serverFee['fastServerFee'] - $SubAgentPromoterProfit['fastTotal']) *  (($myProfit - $SubAgentProfit)/Config::AgentBase));
                $allAgentOtherTotal += (($serverFee['otherServerFee'] - $SubAgentPromoterProfit['otherTotal']) * (($myProfit - $SubAgentProfit)/Config::AgentBase));
            }

            //如果是下下级
            if($subAgentLevel - $level === 2 ) {
                //查找他的上级分成
                $agent2Profit = 0;
                foreach ($mySubAgentList as $agentInfo2) {
                    if ($agentInfo2['id'] === $SubAgentParent_id)
                        $agent2Profit = $agentInfo2['profit'];
                }
                //收益=(服务费- 推广费) * (一级总分成 - 自身分成 - (二级分成 - 自身分成 ))/一级总分成
                $allAgentFastTotal += (($serverFee['fastServerFee'] - $SubAgentPromoterProfit['fastTotal']) *  (($myProfit - $SubAgentProfit - ($agent2Profit- $SubAgentProfit))/$myProfit));
                $allAgentOtherTotal += (($serverFee['otherServerFee'] - $SubAgentPromoterProfit['otherTotal']) * (($myProfit - $SubAgentProfit - ($agent2Profit- $SubAgentProfit))/$myProfit));
            }

        }
        return array("fast_total"=>$allAgentFastTotal, "other_total"=>$allAgentOtherTotal);
    }
    /**
     * 返回代理下下推广员的费用
     * @param $agentId
     * @return array
     */
    static function getMyPromoterProfit($agentId) {
        $profit = array("fastTotal"=>0, "otherTotal"=>0);
        //判断我的玩家下面是否有推广员,没有无需分推广费，有则扣去推广员的利润
        $userList = self::getMyPromoterList($agentId);
        if (sizeof($userList) > 0) {
            foreach ($userList as $user) {
                $promoterTotal = Promotion::getProfit($user['id'],false);
                $profit['fastTotal'] = $profit['fastTotal'] + $promoterTotal['fast_total'];
                $profit['otherTotal'] = $profit['otherTotal']  + $promoterTotal['other_total'];
            }
        }
        return $profit;
    }

    /**
     * 通过总消耗获得服务费
     * @param $FastTotal
     * @param $OtherTotal
     * @return array ["fastServerFee"=>int , otherServerFee=>int]
     */
    static function getServerFee($FastTotal, $OtherTotal) {
        $fastServerFee = ($FastTotal* Config::ServerFee) * Config::LadderAgentMax;
        $otherServerFee = ($OtherTotal* Config::ServerFee) * Config::OtherAgentMax;
        return array("fastServerFee"=>$fastServerFee, "otherServerFee"=>$otherServerFee);
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
     * 返回我的推广员ID列表
     * @param $agent
     * @return mixed
     */
    static function getMyPromoterList($agentId) {
        $isPromoter = "select id from ".Config::SQL_DB.".promoter where parent_id = '$agentId' and status =1";
        $result = ToolMySql::query($isPromoter);
        return $result->fetch_all(MYSQLI_ASSOC);
    }


}