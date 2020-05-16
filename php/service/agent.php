<?php
include_once dirname(__FILE__) . "/../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../util/toolTime.php";
include_once dirname(__FILE__) . "/../util/toolForAgent.php";
include_once dirname(__FILE__) . "/../util/toolRunTime.php";
include_once dirname(__FILE__) . "/../util/logger.php";
include_once dirname(__FILE__) . "/promotion.php";


class Agent {
    static $data = array("charge_total"=>0, "consume_total"=>0, "fast_consume_total"=>0, "other_consume_total"=>0);
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

        $data = self::getPlayerTotal2($agentId,$type, $match, $date);
        if ($date !== $dayOrMonth) {
            self::_insertCalendar($agentId, $data, $date, $role, $type);
        }
        return $data;
    }

    /**
     * 返回某个时间段的消耗/充值总额
     * @param $agentId
     * @param $startDate
     * @param $endDate
     * @param $type
     * @return array
     */
    static function getPlayerByRange($agentId, $startDate, $endDate, $type) {
        $data = array("charge_total"=>0, "consume_total"=>0);
        $dateRange = ToolTime::getDateFromRange($startDate, $endDate);
        for ($i=0; $i < sizeof($dateRange); $i++) {
            $result = self::getPlayerCCByDate($agentId, $dateRange[$i], $type);
            $data['charge_total'] += $result['charge_total'];
            $data['consume_total'] += $result['consume_total'];
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

        //筛选出哪些赛事,目前仅有快速赛
        $matchString = "";
        switch ($match)
        {
            case "race":
                //获取快速赛
                $fastMatchList = ToolForAgent::getAllFastMatchLocaleIdList();
                foreach ($fastMatchList as $matchInfo){
                    $localeId = $matchInfo['localeId'];
                    $matchString = "'$localeId',".$matchString;
                }
                $matchString = substr($matchString, 0, -1);
                break;
            default:
                //默认快速赛
                $fastMatchList = ToolForAgent::getAllFastMatchLocaleIdList();
                foreach ($fastMatchList as $matchInfo){
                    $localeId = $matchInfo['localeId'];
                    $matchString = "'$localeId',".$matchString;
                }
                $matchString = substr($matchString, 0, -1);
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

        //所有玩家的快速赛消耗
        $matchConsumeSql = "select ifnull(sum(consume_amount),0) as fast_consume_total from ".Config::SQL_DB.".consume as consume inner join 
                       ($typeSql)as players on consume.user_id = players.id where consume.status = '1' and localeId in ($matchString) $consumeDateSql";
        //代理下所有玩家的所有赛事消耗
        $allCCSql ="select ifnull(sum(charge_total),0) as charge_total,ifnull(sum(consume_total),0) as consume_total from(
                    select sum(consume_amount) as consume_total, 0 as charge_total from ".Config::SQL_DB.".consume as consume inner join ($typeSql)as player on consume.user_id = player.id where consume.status = '1' $consumeDateSql
                    Union all
                    select 0 as consume_total, sum(charge_amount) as charge_total from ".Config::SQL_DB.".charge as charge inner join ($typeSql)as player on charge.user_id = player.id $chargeDateSql
                    )as PlayerTotal;";

        $fastMatchConsumeTotalResult = ToolMySql::query($matchConsumeSql);
        $fastMatchConsumeTotal = $fastMatchConsumeTotalResult->fetch_assoc()['fast_consume_total'];
        $PlayerTotal = ToolMySql::query($allCCSql);
        $total = $PlayerTotal->fetch_assoc();
        $data["charge_total"] = $total['charge_total'];
        $data["consume_total"] = $total["consume_total"];
        $data["fast_consume_total"] = $fastMatchConsumeTotal;
        $data["other_consume_total"] = $total["consume_total"]-$fastMatchConsumeTotal;
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
        $myAgentList = self::getMySubList($agentId);
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
     * 返回代理商的玩家/代理商总量(agent_total,player_total)
     * @param $agentId
     * @return array|null
     */
    static function getPlayerAgentTotal($agentId) {
        $totalSql = "
        select sum(player_total) as player_total , sum(agent_total) as agent_total from (
        select 
            ifnull(sum(case when status=1 then 1 else 0 end ),0) as agent_total,0 as player_total
        from ".Config::SQL_DB.".agentusers 
        where parent_id = '$agentId'
        union all
        select 
        0 as agent_total,ifnull(sum(case when parent_id = '$agentId' then 1 else 0 end ),0) as player_total
        from  ".Config::SQL_DB.".playerusers) as temp";
        $totalResult = ToolMySql::query($totalSql);
        return $totalResult->fetch_assoc();
    }

    /**
     * 返回代理商个人信息
     * @param $agentId 代理商id
     * @return array|null
     */
    static function myInfo($agentId) {
        $myInfoSql = "
        select
		myinfo.id,
		myinfo.level,
		myinfo.username,
		myinfo.profit,
		pInfo.tel as parent_tel
	    from ".Config::SQL_DB.".agentusers as myinfo left join ".Config::SQL_DB.".agentusers as pInfo on myinfo.parent_id = pInfo.id where myinfo.id = '$agentId' ";

        $myInfoResult = ToolMySql::query($myInfoSql);
        $totalResult = self::getPlayerAgentTotal($agentId);

        $data = array_merge($myInfoResult->fetch_assoc(), $totalResult);
        return $data;
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
     * 返回
     * @param $agentId
     * @return float|int
     */
    static function getMyProfit($agentId) {
        //先查找出我对所属所有玩家的利益
        $myInfo= self::getMyPercentageAndLevel($agentId)[0];
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
        $allProfit = self::getPlayerTotal2($agentId);

        //快速赛利润
        $fastTotal = $allProfit['fast_consume_total'] * Config::ServerFee * Config::LadderAgentMax ;
        //其他赛事利润
        $otherTotal = $allProfit['other_consume_total'] * Config::ServerFee * Config::OtherAgentMax ;

        //判断我的玩家下面是否有推广员,没有无需分推广费，有则扣去推广员的利润
        $promoterProfit = self::getMyPromoterProfit($agentId);
        $fastTotal = $fastTotal - $promoterProfit['fastTotal'];
        $otherTotal = $otherTotal - $promoterProfit['otherTotal'];

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
            $SubAgentConsumeTotal = self::getPlayerTotal2($SubAgentId);
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
     * 修改代理商的收益比例
     * @param $MyAgentId
     * @param $agentId
     * @param $num
     * @return bool|mysqli_result
     */
    static function setProfit($MyAgentId,$agentId, $num) {
        $getProfit = "select profit from ".Config::SQL_DB.".agentusers where id = '$MyAgentId'";
        $result = ToolMySql::query($getProfit);
        $myProfit = (int)$result->fetch_assoc()['profit'];
        if ((int)$num  > $myProfit)
            return '分成比例不能大于自身';
        $sql = "update ".Config::SQL_DB.".agentusers  set profit = '$num' where id = '$agentId'";
        return $result = ToolMySql::query($sql);
    }

    /**
     * 按类型返回代理的所有玩家总额信息
     * @param $agentId
     * @param $type
     * @return array|null
     */
    static function getPlayerListTotal($agentId, $type) {
        $typeSql1 = '';
        $typeSql2= '';
        if ($type === "direct") {
            $typeSql1 = " where from_uid =''";
            $typeSql2 = " and from_uid = ''";
        }
        else if($type === "fission") {
            $typeSql1 = " where from_uid !=''";
            $typeSql2 = " and from_uid !=''";
        }

        //保留月充值查询的语法
        $sql = "
          select sum(total) as total,sum(today) as today,sum(yesterday) as yesterday,sum(charge_total) as charge_total,sum(consume_total) as consume_total,sum(month_charge) as month_charge,sum(month_consume) as month_consume from(
          select ifnull(sum(case when parent_id = '$agentId' then 1 else 0 end ),0) as total,0 as today,0 as yesterday,0 as charge_total,0 as consume_total,0 as month_charge,0 as month_consume from  ".Config::SQL_DB.".playerusers $typeSql1
          Union all
          select 0 as total,ifnull(sum(case when parent_id = '$agentId' then 1 else 0 end ),0) as today,0 as yesterday,0 as charge_total,0 as consume_total,0 as month_charge,0 as month_consume from".Config::SQL_DB.". playerusers where DATEDIFF(create_at,curdate())=0 $typeSql2
          union all
          select 0 as total ,0 as today,ifnull(sum(case when parent_id = '$agentId' then 1 else 0 end ),0) as yesterday,0 as charge_total,0 as consume_total,0 as month_charge,0 as month_consume from".Config::SQL_DB.". playerusers where DATEDIFF(create_at,curdate())=-1 $typeSql2
          union all
          select 0 as total,0 as today,0 as yesterday,ifnull(sum(case when parent_id = '$agentId'  then charge_amount else 0 end),0) as charge_total,0 as consume_total,0 as month_charge,0 as month_consume from".Config::SQL_DB.". player_charge $typeSql1 
          Union all
          select 0 as total,0 as today,0 as yesterday,0 as charge_total,ifnull(sum(case when parent_id = '$agentId' and status ='1' then consume_amount else 0 end),0) as consume_total,0 as month_charge,0 as month_consume  from ".Config::SQL_DB.".player_consume $typeSql1 
          union all
          select 0 as total ,0 as today,0 as yesterday,0 as charge_total,0 as consume_total,ifnull(sum(case when parent_id = '$agentId' then charge_amount else 0 end),0) as month_charge,0 as month_consume  from ".Config::SQL_DB.".player_charge where month(charge_time) = month(curdate()) and year(curdate()) $typeSql2
          union all 
          select 0 as total,0 as today,0 as yesterday,0 as charge_total,0 as consume_total,0 as month_charge,ifnull(sum(case when parent_id = '$agentId' and status ='1' then consume_amount else 0 end),0) as month_consume from ".Config::SQL_DB.".player_consume where month(consume_time) = month(curdate()) and year(curdate()) $typeSql2
        ) as my_player_total;
        ";
        $sql2 = "
          select sum(total) as total,sum(today) as today,sum(yesterday) as yesterday,sum(charge_total) as charge_total,sum(consume_total) as consume_total from(
          select ifnull(sum(case when parent_id = '$agentId' then 1 else 0 end ),0) as total,0 as today,0 as yesterday,0 as charge_total,0 as consume_total from  ".Config::SQL_DB.".playerusers $typeSql1
          Union all
          select 0 as total,ifnull(sum(case when parent_id = '$agentId' then 1 else 0 end ),0) as today,0 as yesterday,0 as charge_total,0 as consume_total from ".Config::SQL_DB.".playerusers where DATEDIFF(create_at,curdate())=0 $typeSql2
          union all
          select 0 as total ,0 as today,ifnull(sum(case when parent_id = '$agentId' then 1 else 0 end ),0) as yesterday,0 as charge_total,0 as consume_total from ".Config::SQL_DB.".playerusers where DATEDIFF(create_at,curdate())=-1 $typeSql2
          union all
          select 0 as total,0 as today,0 as yesterday,ifnull(sum(case when parent_id = '$agentId'  then charge_amount else 0 end),0) as charge_total,0 as consume_total from ".Config::SQL_DB.".player_charge $typeSql1 
          Union all
          select 0 as total,0 as today,0 as yesterday,0 as charge_total,ifnull(sum(case when parent_id = '$agentId' and status ='1' then consume_amount else 0 end),0) as consume_total from ".Config::SQL_DB.".player_consume $typeSql1 
          union all
          select 0 as total ,0 as today,0 as yesterday,0 as charge_total,0 as consume_total from ".Config::SQL_DB.".player_charge where month(charge_time) = month(curdate()) and year(curdate()) $typeSql2
          union all 
          select 0 as total,0 as today,0 as yesterday,0 as charge_total,0 as consume_total from ".Config::SQL_DB.".player_consume where month(consume_time) = month(curdate()) and year(curdate()) $typeSql2
        ) as my_player_total;
        ";
        // var_dump($sql2);
        $myPlayerInfo = ToolMySql::query($sql2);
        $row = $myPlayerInfo->fetch_assoc();
//        unset($row["month_charge"]);
//        unset($row["month_consume"]);

        $row['charge_total'] = round($row['charge_total'], 2);
        $row['consume_total'] = round($row['consume_total'], 2);

        $myPlayerInfo->close();
        return $row;
    }

    /**
     * 选手列表
     * @param $agentId
     * @param $type
     * @param $page
     * @param $limit
     * @return array
     */
    static function getPlayerList($agentId, $type, $page, $limit) {
        $typeSql = '';
        if ($type === "direct") {
            $typeSql = " and from_uid =''";
        }
        else if($type === "fission") {
            $typeSql = " and from_uid !=''";
        }
        $start = ($page-1)*$limit;

        $routineSql = "
             select id,username,sum(charge_total) as charge_total ,sum(consume_total) as consume_total, tel,create_at from(
             select id,username,ifnull(sum(player_charge.charge_amount),0) as charge_total,0 as consume_total,tel,create_at from player_charge where parent_id = '$agentId'$typeSql group by id 
             union all
             select id,username,0 as charge_total,ifnull(sum(player_consume.consume_amount),0) as consume_total,tel,create_at from ".Config::SQL_DB.".player_consume where status ='1' and parent_id = '$agentId' $typeSql group by id
             ) as total group by id limit $start,$limit
        ";

        $data = array();
        $myPlayerList = ToolMySql::query($routineSql);
        while ($row = $myPlayerList->fetch_assoc()) {
            $row['charge_total'] = round($row['charge_total'],2);
            $row['consume_total'] = round($row['consume_total'],2);
            $data[] = $row;
        }
        return $data;
    }


    /**
     * 分页返回我的代理商数据
     * @param $agentId 代理商id
     * @param $page 页数
     * @param $limit 偏移量
     * @return array|null
     */
    static function getAgentList($agentId, $page, $limit) {
        $start = ($page-1)*$limit;
        $data = array();
        //我的代理商总量
        $myAgentTotalSql = "select ifnull(sum(case when status = 1 then 1 else 0 end),0) as agent_total from ".Config::SQL_DB.".agentusers where parent_id = '$agentId';";
        $myAgentTotalResult = ToolMySql::query($myAgentTotalSql);
        $data["agent_total"] = $myAgentTotalResult->fetch_assoc()['agent_total'];
        $data["my_agent_list"] = array();
        $SubAgentInfoSql = "select id,username,tel,level,status,apply_at,note from ".Config::SQL_DB.". agentusers where parent_id = '$agentId' and status = 1 order by create_at asc limit $start,$limit";
        $SubAgentInfoResult = ToolMySql::query($SubAgentInfoSql);

        //我的下级代理
        while ($subAgentRow = $SubAgentInfoResult->fetch_assoc()) {
            $subAgent = array();
            $mySubAgentId = $subAgentRow["id"];
            $playerCCTotal = self::getPlayerTotal2($mySubAgentId);
            $playerAgentTotal = self::getPlayerAgentTotal($mySubAgentId);
            $subSubAgentCCTotal = self::getAgentTotal($mySubAgentId);

            //获取代理的分成
            $sql = "select profit from ".Config::SQL_DB.".agentusers where id = '$mySubAgentId'";
            $result = ToolMySql::query($sql);

            $profit =  $result->fetch_assoc()['profit'];
            $subAgent["id"] = $subAgentRow["id"];
            $subAgent["username"] = $subAgentRow["username"];
            $subAgent["tel"] = $subAgentRow["tel"];
            $subAgent["level"] = $subAgentRow["level"];
            $subAgent["status"] = $subAgentRow["status"];
            $subAgent["apply_at"] = $subAgentRow["apply_at"];
            $subAgent["note"] = $subAgentRow["note"];
            $subAgent["player_total"] = $playerAgentTotal["player_total"];
            $subAgent["agent_total"] = $playerAgentTotal["agent_total"];
            $subAgent["charge_total"] = round($playerCCTotal["charge_total"],2);
            $subAgent["consume_total"] = round($playerCCTotal["consume_total"],2);
            $subAgent["sub_charge_total"] = $subSubAgentCCTotal["charge_total"];
            $subAgent["sub_consume_total"] = $subSubAgentCCTotal["consume_total"];
            $subAgent["sub_player_total"] = 0;
            $subAgent["profit"] = $profit;

            $subSubAgentIdSql = "select id from ".Config::SQL_DB.". agentusers where parent_id ='$mySubAgentId' and status = 1";
            $subSubAgentIdResult = ToolMySql::query($subSubAgentIdSql);
            if($subSubAgentIdResult->num_rows > 1) {
                while ($row = $subSubAgentIdResult->fetch_assoc()) {
                    $subSubId = $row['id'];
                    $playerAmountSql = "select ifnull(sum(case when parent_id = '$subSubId' then 1 else 0 end ),0) as player_total from ".Config::SQL_DB.". playerusers";
                    $playerAmountResult = ToolMySql::query($playerAmountSql);
                    $subAgent["sub_player_total"] += (int)$playerAmountResult->fetch_assoc()['player_total'];
                }
            }
            array_push($data["my_agent_list"], $subAgent);
        }
        return $data;
    }

    /**
     * 获取承办方概况
     * @param $startDate
     * @param $endDate
     * @param $page
     * @param $limit
     * @return array
     */
    static function getAgentSummary($startDate, $endDate, $page, $limit) {
        $dateRange = ToolTime::getDateFromRange($startDate, $endDate);
        $start = ($page-1)*$limit;
        $dateList = array_slice($dateRange,$start,$limit);
        $data = array();
        //方法1 通过php计算 实验证明方法二更快
//        for ($i = 0; $i < sizeof($dateList); $i++) {
//            $temp = array();
//            $allPlayerChargeSql = "select ifnull(sum(charge_amount),0) as charge_total from player_charge where DATEDIFF(charge_time, '$dateList[$i]')=0";
//            $allPlayerConsumeSql = "select ifnull(sum(consume_amount),0) as consume_total from player_consume where DATEDIFF(consume_time, '$dateList[$i]')=0";
//            $agentPlayerChargeSql = "select ifnull(sum(case when parent_id != '' then charge_amount else 0 end),0) as charge_total from player_charge where DATEDIFF(charge_time,'$dateList[$i]')=0";
//            $agentPlayerConsumeSql = "select ifnull(sum(case when parent_id != '' then consume_amount else 0 end),0) as consume_total from player_consume where DATEDIFF(consume_time,'$dateList[$i]')=0";
//
//            $allPlayerChargeResult = (ToolMySql::query($allPlayerChargeSql))->fetch_assoc()["charge_total"];
//            $allPlayerConsumeResult = (ToolMySql::query($allPlayerConsumeSql))->fetch_assoc()["consume_total"];
//            $agentPlayerChargeResult = (ToolMySql::query($agentPlayerChargeSql))->fetch_assoc()["charge_total"];
//            $agentPlayerConsumeResult = (ToolMySql::query($agentPlayerConsumeSql))->fetch_assoc()["consume_total"];
//            $temp["date"] = $dateList[$i];
//            $temp["all_player_charge"] = $allPlayerChargeResult;
//            $temp["all_player_consume"] = $allPlayerConsumeResult;
//            $temp["agent_player_consume"] = $agentPlayerConsumeResult;
//            $temp["agent_player_charge"] = $agentPlayerChargeResult;
//            $data[] = $temp;
//        }


//        //方法二，数据库做排序
        $startDate = $startDate.' 00:00:00';
        $endDate = $endDate.' 23:59:59';
        $sql = "select sum(charge_total) as all_charge_total, sum(consume_total) as all_consume_total, sum(agent_charge_total) as agent_charge_total, sum(agent_consume_total) as agent_consume_total, user_time from (
                select ifnull(sum(charge_amount),0) as charge_total,0 as consume_total , 0 as agent_charge_total, 0 as agent_consume_total, DATE_FORMAT( charge_time, \"%Y-%m-%d\" ) as user_time from ".Config::SQL_DB.". player_charge
                where charge_time > '$startDate' AND charge_time< '$endDate' group by user_time
                union all
                select 0 as charge_total, ifnull(sum(consume_amount),0) as consume_total,0 as agent_charge_total, 0 as agent_consume_total, DATE_FORMAT( consume_time, \"%Y-%m-%d\" ) as user_time from ".Config::SQL_DB.".player_consume
                where consume_time > '$startDate' AND consume_time< '$endDate' and status='1' group by user_time
                union all
                select 0 as charge_total,0 as consume_total , sum(ifnull(charge_amount,0)) as agent_charge_total, 0 as agent_consume_total, DATE_FORMAT( charge_time, \"%Y-%m-%d\" ) as user_time from ".Config::SQL_DB.".player_charge
                where charge_time > '$startDate' AND charge_time< '$endDate' and parent_id != '' group by user_time
                union all
                select 0 as charge_total,0 as consume_total,0 as agent_charge_total,sum(ifnull(consume_amount,0)) as agent_consume_total,DATE_FORMAT( consume_time, \"%Y-%m-%d\" ) as user_time from ".Config::SQL_DB.".player_consume
                where consume_time > '$startDate' AND consume_time< '$endDate' and parent_id != '' and status='1' group by user_time
                ) as test group by user_time limit $start,$limit";

        $result = ToolMySql::query($sql);
        while ($row = $result->fetch_assoc()) {
            $temp = array();
            $temp["date"] = $row['user_time'];
            $temp["all_player_charge"] = round($row['all_charge_total'],2);
            $temp["all_player_consume"] = round($row['all_consume_total'],2);
            $temp["agent_player_consume"] = round($row['agent_charge_total'],2);
            $temp["agent_player_charge"] = round($row['agent_consume_total'],2);
            $data[] = $temp;
        }

        return $data;
    }

    /**
     * 注册代理商
     * @param $pid
     * @param $username
     * @param $password
     * @param $tel
     * @return bool|int|string
     */
    static function register($pid, $username, $password, $tel) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $findPidSql = "select id,level,status,parent_id from ".Config::SQL_DB.".agentusers where id = '$pid'";
        $findPidResult = ToolMySql::query($findPidSql);
        $findPid = $findPidResult->fetch_assoc();
        $pLevel = $findPid['level'];
        $id =  $findPid['id'];
        $pStatus = $findPid['status'];
        $pParen_id = $findPid['parent_id'];
        $level3 = '';
        if (!$id)
            return '此代理不存在';
        if ((int)$pLevel ===3) {
            return '此代理不可再生成下级代理';
        }

        $level = ((int)$pLevel)+1;
        if ((int)$pStatus !== 1)
            return '此代理状态不正常';

        $findUsernameSql = "select id from ".Config::SQL_DB.".agentusers where username = '$username'";
        $findUsernameResult = ToolMySql::query($findUsernameSql);
        $row = $findUsernameResult->num_rows;
        if ($row)
            return '用户名已存在';

        $findTelSql = "select id from ".Config::SQL_DB.". agentusers where tel = '$tel'";
        $findTelResult = ToolMySql::query($findTelSql);
        $row = $findTelResult->num_rows;
        if ($row)
            return '电话号码已存在';
        if ((int)$pLevel === 2) {
            $level3 = $pParen_id;
        }
        $registerSql = "INSERT INTO `".Config::SQL_DB."`.`agentusers`
                        (`username`,`password`,`parent_id`,`level`,`level3`,`status`,`tel`)
                    VALUES
                        ('$username','$passwordHash','$pid','$level','$level3','0','$tel')";
        if (ToolMySql::query($registerSql)  !== true) {
            return false;
        }
        return true;
    }

    /**
     * 返回代理商审核列表
     * @param $agentId
     * @return array
     */
    static function getApplyList($agentId) {
        $data = array();
        $findApplySql = "select id, username, level, tel, create_at from ".Config::SQL_DB.".agentusers where parent_id ='$agentId' and status = '0'";

        $findApplyResult = ToolMySql::query($findApplySql);
        while ($row = $findApplyResult->fetch_assoc()) {
            $temp = array();
            $temp['id'] = $row['id'];
            $temp['username'] = $row['username'];
            $temp['level'] = $row['level'];
            $temp['tel'] = $row['tel'];
            $temp['create_at'] = $row['create_at'];
            $data[] = $temp;
        }
        return $data;
    }

    /**
     * 审核代理商
     * @param $agentId
     * @param $type
     * @param $note
     * @return bool
     */
    static function operateApply($cid, $type, $note) {
        //开启事务
        ToolMySql::setAutocommit(false);

        //先更新代理状态，才能把玩家代理到该代理下
        $operateSql = "update ".Config::SQL_DB.".agentusers set status = 1, note ='$note', apply_at =now() where id = '$cid'";
        if ($type === "refuse")
            $operateSql = "update ".Config::SQL_DB.".agentusers set status = -1, note ='$note', apply_at =now() where id = '$cid'";

        //更新状态
        if (ToolMySql::query($operateSql) !== true) {
            ToolMySql::rollback();
            return "更新代理商状态出错";
        }

        //查询该代理下是否已经存在游戏账号,默认归属到自己代理下
        if ($type === "agree") {
            $findAgentSql = "select tel from ".Config::SQL_DB.".agentusers where id = '$cid'";
            $findAgentResult = ToolMySql::query($findAgentSql);
            if (!$findAgentResult) {
                ToolMySql::rollback();
                return "数据库查找出错";
            }
            $agentTel = $findAgentResult->fetch_assoc()['tel'];

            //查找玩家信息
            $findPlayerSql = "select id from ".Config::SQL_DB.".playerusers where tel = '$agentTel'";
            $findPlayerResult = ToolMySql::query($findPlayerSql);

            if ($findPlayerResult->num_rows === 1) {
                $userId = $findPlayerResult->fetch_assoc()['id'];
                $updatePlayerSql = "update ".Config::SQL_DB.".playerusers set parent_id = '$cid' where id ='$userId'";
                $updatePlayerResult = ToolMySql::query($updatePlayerSql);
                if (!$updatePlayerResult) {
                    ToolMySql::rollback();
                    return "更新玩家代理出错";
                }
            }
        }
        ToolMySql::commit();
        ToolMySql::setAutocommit(true);
        return true;
    }
    /**
     * 更改密码
     * @param $agentId
     * @param $old
     * @param $new
     * @return bool|string */
    static function  changePwd($agentId, $old, $new) {
        // $oldPasswordHash = password_hash($old, PASSWORD_DEFAULT);
        $getAgentSql = "select password from ".Config::SQL_DB.".agentusers where id = '$agentId'";
        $oldPwdResult = ToolMySql::query($getAgentSql);
        $oldPwd = $oldPwdResult->fetch_assoc()['password'];

        $isRight = password_verify ( $old, $oldPwd);
        if ($isRight) {
            $newPasswordHash = password_hash($new, PASSWORD_DEFAULT);
            $updateSql = "update ".Config::SQL_DB.".agentusers set password = '$newPasswordHash' where id = '$agentId'";
            $result = ToolMySql::query($updateSql);
            if (!$result)
                return '更新失败';
        }
        else
            return '密码错误';
        return true;
    }

    /**
     * 上级重置下级密码
     * @param $myAgentId
     * @param $subAgentId
     * @param $password
     * @return bool|mysqli_result|string
     */
    static function  resetPwd($myAgentId, $subAgentId, $password) {
        $subAgentInfo = ToolForAgent::findSupAgent($subAgentId);
        if($myAgentId!== $subAgentInfo['parent_id'] && $myAgentId!==$subAgentInfo['level3']) {
            return "该代理不属于你的下级,无法重置密码";
        }
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $updateSql = "update ".Config::SQL_DB.".agentusers set password = '$passwordHash' where id = '$subAgentId'; ";
        return ToolMySql::query($updateSql);
    }

    /**
     * 上级给下级设置备注
     * @param $myAgentId
     * @param $subAgentId
     * @param $note
     * @return bool|mysqli_result|string
     */
    static function setNote2SubAgent($myAgentId, $subAgentId, $note) {
        $subAgentInfo = ToolForAgent::findSupAgent($subAgentId);
        if($myAgentId!== $subAgentInfo['parent_id'] && $myAgentId!==$subAgentInfo['level3']) {
            return "该代理不属于你的下级,无法修改备注";
        }

        $update = "update ".Config::SQL_DB.".agentusers set note = '$note' where id = '$subAgentId';";
        return ToolMySql::query($update);
    }

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

            $currentTotalDetail = self::getMyProfit($agentId);
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

//getPlayerCCByDate($agentId, $date, $type, $match = "race")
//ToolRunTime::AvgRunTime(1,"Agent::getPlayerCCByDate",array("10033","2020-04-19","all","race") ,true);
//ToolRunTime::AvgRunTime(1,"Agent::getPlayerTotal2",array("10033","all","race","2020-04-19") ,true);//10133
//ToolRunTime::AvgRunTime(1,"Agent::getMyProfit",array("10134") ,true);
//ToolRunTime::AvgRunTime(1,"Agent::getMyProfit",array("10133") ,true);
//ToolRunTime::AvgRunTime(1,"Agent::getMyProfit",array("10135") ,true);
//ToolRunTime::AvgRunTime(10,"Agent::getMyProfit",array("10033") ,true);
//ToolMySql::conn();
//Agent::changePwd('10030','shenghe123','123');
////var_dump(Agent::getPlayerCCByMonth('agent-test1','2019-12'));
//var_dump(Agent::getPlayerCCByDate('agent-test1','2020-01-07'));
//var_dump(Agent::getPlayerTotal('10033'));
//var_dump(Agent::getAgentCCByDate('agent-test1','2020-01','fission'));
//var_dump(Agent::getAgentTotal('agent-test1'));
//var_dump(Agent::myInfo('agent-test2'));
//var_dump(Agent::playerListTotal('agent-test1', 2));
//var_dump(Agent::getPlayerList('agent-test1', 'all', 1, 5));
//var_dump(Agent::_getDataFromCalendar('agent-test2','2020-01',2,'all'));
//$userId, $chargeTotal, $consumeTotal, $date, $role, $type
//var_dump(Agent::_insertCalendar('agent-test2',1001,123, '2020-07', 1,'all_total'));
//var_dump(Agent::getAgentList('agent-test2',1,10));
////var_dump(Agent::getAgentSummary("2020-01-01","2020-02-20",1,9));
//$num = 1000;
//$total = 0;
//for($i = 0; $i<$num; $i++) {
//    $total += Agent::getAgentSummary("2020-01-01","2020-02-20",1,9);
//}
//var_dump($total);
//var_dump($total/$num);

//var_dump(Agent::getPlayerByRange('agent-test1','2020-01-01','2020-02-01','all'));
//var_dump(Agent::getPlayerCCByDate('agent-test1','2020-01-17','all'));
//var_dump(Agent::register('agent-test1','test','123456','18814140188'));
//var_dump(Agent::getApplyList('agent-test1'));
//var_dump(Agent::operateApply('5e68ba2a30da4','refuse'));
//var_dump(Agent::getAgentList2('agent-test1',1,3));
//var_dump(Agent::myProfit(10016));
//ToolMySql::close();
//$endTime = microtime(true);
//$runTime = ($endTime-$startTime)*1000 . ' ms';
//var_dump($runTime);


