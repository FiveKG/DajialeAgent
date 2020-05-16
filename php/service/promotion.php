<?php
/**
 * 关于游戏推广
 */
include_once dirname(__FILE__) . "/../util/toolTime.php";
include_once dirname(__FILE__) . "/../util/toolRedis.php";
include_once dirname(__FILE__) . "/../util/toolForAgent.php";
include_once dirname(__FILE__) . "/../util/toolRunTime.php";
include_once dirname(__FILE__) . "/../util/dajialeService.php";
include_once dirname(__FILE__) . "/../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../config.php";
include_once dirname(__FILE__) . "/../util/logger.php";
include_once dirname(__FILE__) . "/../util/register2Agent.php";

class Promotion {
    static $ChargeTotal = 36;
    static $MyChargeTotal = 36;
    static $Count = 5;
    static $data = array("fast_consume_total"=>0, "consume_total"=>0);
    static $profit = array("fast_total"=>0, "other_total"=>0);

    static $minGetPrize = 0.01;

    static function setConfig($chargeTotal, $myChargeTotal, $count, $minGetPrize = '') {
        self::$ChargeTotal = $chargeTotal;
        self::$MyChargeTotal = $myChargeTotal;
        self::$Count = $count;
        if($minGetPrize)
            self::$minGetPrize = $minGetPrize;
        return true;
    }
    static function getConfig() {
        return array("chargeTotal"=>self::$ChargeTotal, "myChargeTotal"=>self::$MyChargeTotal, "count"=>self::$Count);
    }
    /**
     * 通过userid返回rid
     * @param $userId
     * @return string|null
     */
    static function getRidByUserId($userId) {
        $isExistSql = "SELECT rid from ".Config::SQL_DB.".playerusers where id = '$userId'";
        $result = ToolMySql::query($isExistSql);
        if ($result->num_rows)
            return $result->fetch_assoc()['rid'];
        else
            return null;
    }

    /**
     * 已经领取的数额，游戏用
     * @param $userId
     * @return mixed
     */
    static function givenAwardForGame($userId) {
        $givenSql = "select ifnull(sum(amount),0) as total from ".Config::SQL_DB.".getprize where userId = '$userId' and status = 1";
        $result = ToolMySql::query($givenSql);
        return  $result->fetch_assoc()['total'];
    }

    static function  givenAwardList($page, $limit, $type ='all' , $rid = '') {
        $start = ($page-1)*$limit;
        $where = '';
        if ($rid)
            $where = "where rid = '$rid'";
        switch ($type)
        {
            case "success" :
                if($where)
                    $type = " and status = 1";
                else
                    $type = " where status = 1";
                break;
            case "fail":
                if($where)
                    $type = " and status = 0";
                else
                    $type = " where status = 01";
                break;
            default:
                if($where)
                    $type = "";
                break;
        }
        $sql = "select * from ".Config::SQL_DB.".getprize $where $type order by create_at DESC limit $start,$limit ";
        $totalSql = "select ifnull(count(id),0) as total from ".Config::SQL_DB.".getprize $where $type ";

        $result = ToolMySql::query($sql);
        $totalResult = ToolMySql::query($totalSql);
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $total = $totalResult->fetch_assoc()['total'];
        return array("total"=>(int)$total, "list"=>$data);
    }
    /**
     * 游戏服务端查询成为推广员的资格
     * @param $userId
     * @return array
     */
    static function isAward($userId) {
        $data = array("request"=>array("charge_total"=>self::$ChargeTotal, "my_charge_total"=>self::$MyChargeTotal, "count"=>self::$Count)
                     ,"response"=>array( "charge_total"=>0, "list"=>array()));
        $checkMyself = "select ifnull(sum(charge_amount),0) as charge_total from ".Config::SQL_DB.".charge where user_id = '$userId' ";
        $checkSql = "select *,sum(charge_amount) as charge_total from (
                        select 
                           charge.user_id,
                           charge.charge_amount,
                           playerusers.rid,
                           playerusers.from_uid 
                        from ".Config::SQL_DB.".playerusers as playerusers inner join  ".Config::SQL_DB.".charge as charge on playerusers.id = charge.user_id 
                        where from_uid = '$userId' order by playerusers.create_at
                    )as temp group by rid";

        $checkResult = ToolMySql::query($checkSql);
        $checkMyselfResult = ToolMySql::query($checkMyself);
        $data['response']['charge_total'] = $checkMyselfResult->fetch_assoc()['charge_total'];
        $checkList = $checkResult->fetch_all(MYSQLI_ASSOC);

        foreach ($checkList as $info) {
            if(sizeof($data['response']['list']) >= self::$Count)
                break;
            if ($info['charge_total'] >= self::$ChargeTotal) {
                $temp = array();
                $userId = $info['user_id'];
                $rid = $info['rid'];
                $charge_total = $info['charge_total'];

                $temp['userId'] = $userId;
                $temp['rid'] = $rid;
                $temp['charge_total'] = $charge_total;
                $data['response']['list'][] = $temp;
            }
        }
        return $data;
    }

    /**
     * 在promoter表里查找我的邀请人，有代表他是推广员，没有则不是
     * @param $userId
     * @return int|null
     */
    static function getProUid($userId) {
        $selectSql = "select `id` from ".Config::SQL_DB.".promoter where id = '$userId' and status = '1'";
        $result = ToolMySql::query($selectSql);
        $info = $result->fetch_assoc()['id'];
        return $info;
    }

    /**
     * 返回自己的上级推广员链
     * @param $userId
     * @return array
     */
    static function getProChain($userId) {
        $selectSql = "select pro_uid from ".Config::SQL_DB.".promoter where id = '$userId' and status = '1'" ;
        $result = ToolMySql::query($selectSql);
        $listString = $result->fetch_assoc()['pro_uid'];
        $list = json_decode($listString);
        return $list;
    }

    /**
     * 查找邀请者
     * @param $userId
     * @return string|null
     */
    static function getFromUid($userId) {
        $selectSql = "select from_uid from ".Config::SQL_DB.".playerusers where id = '$userId'";
        $result = ToolMySql::query($selectSql);
        $from_uid = $result->fetch_assoc()['from_uid'];
        return $from_uid;
    }

    /**
     * 审核成为推广员
     * @param $userId
     * @return bool|mysqli_result
     */
    static function bePromoter($userId ) {
        $promoterChain =array();
        $data = json_encode(self::isAward($userId));
        $playerSql = "select * from ".Config::SQL_DB.".playerusers where id = '$userId'";
        $playerResult = ToolMySql::query($playerSql);
        $player = $playerResult->fetch_all(MYSQLI_ASSOC)[0];

        $id = $player['id'];
        $rid = $player['rid'];
        $username = $player['username'];
        $parent_id = $player['parent_id'];
        $from_uid  = $player['from_uid'];
        $tel = $player['tel'];
        //寻找我的上级推广员
        //如果不是被邀请来的，即是第一级推广员
        //寻找我的下级,一共往上找2级

        $i = 0;
        $count = 20;//防止死循环 最多往上查找20次
        while($count) {
            //如果被邀请来的，查看邀请我的人是否是已审核通过的推广员，如果是，则成为他的推广员+1
            //如果我的上级已经找到，则找出我的上级的上级是谁
            if (!$from_uid)
                break;
            //判断邀请我的人是否是推广员
            $myProUid1 = self::getProUid($from_uid);
            if ($myProUid1) {
                //是推广员
                $promoterChain[$i] = $myProUid1;
                break;
            }else {
                //如果不是，则判断我的邀请者的邀请者是否存在,不存在则是一级推广员
                $myFromUid2 = self::getFromUid($from_uid);
                if ($myFromUid2) {
                    //如果有我的邀请者的邀请者存在，则判断是否是推广员，以此递归,直到找不到上级
                    $from_uid = $myFromUid2;
                }
            }
            $count --;
        }
        $level2 = '';
        if(sizeof($promoterChain) >0)
            $level2 = $promoterChain[0];

        $promoterChain = json_encode($promoterChain);

        $insertSql = "insert into ".Config::SQL_DB.".promoter (`id`,`rid`,`username`,`parent_id`,`from_uid`,`pro_uid`,`tel`,`status`,`level2`,`data`) values ('$id','$rid','$username','$parent_id','$from_uid','$promoterChain','$tel',0,'$level2','$data')";
        $result =  ToolMySql::query($insertSql);

        if (!$result)
            return false;
        return true;
    }

    /**
     * 审核推广员,成为推广员即与邀请的玩家断开联系
     * @param $rid
     * @param $operate
     * @return bool|mysqli_result
     */
    static function operatePromoter($rid,$operate) {
        if ($operate === true || $operate === 'true') {
            //查找是否存在有上级推广员
            $selectPromoterSql = "select level2,id from ".Config::SQL_DB.".promoter where rid = '$rid'";
            $promoterResult = ToolMySql::query($selectPromoterSql);
            $promoter = $promoterResult->fetch_all(MYSQLI_ASSOC)[0];
            $id = $promoter['id'];
            $level2 = $promoter['level2'];

            //如果成为推广员，与之前的分裂的所有玩家断开联系;
            $updateSql = 'update '.Config::SQL_DB.".playerusers set from_uid='' where from_uid = '$id'";
            if ($level2) {
                //如果上头有推广员的话则全部归属到上个推广员,否则直接归代理.目前改用射线结构，不再用链式
                $updateSql = 'update '.Config::SQL_DB.".playerusers set from_uid='$level2' where from_uid = '$id'";
            }

            //成为推广员以后,自身与前推广员断开关系
            $updateMyselfSql = "update ".Config::SQL_DB.".playerusers set from_uid='' where id = '$id'";

            //成为推广员
            $selectSql = "update ".Config::SQL_DB.".promoter set status = 1 where rid = '$rid'";
        }
        else
            $selectSql = "update ".Config::SQL_DB.".promoter set status = -1 where rid = '$rid'";
        //开启事务
        ToolMySql::setAutocommit(false);
            $updatePromoter = ToolMySql::query($selectSql);
            $updatePlayerusers = ToolMySql::query($updateSql);
            $updateMyself = ToolMySql::query($updateMyselfSql);

            if (!$updatePromoter || !$updatePlayerusers || !$updateMyself)
            {
                ToolMySql::rollback();
                return false;
            }
        ToolMySql::commit();
        ToolMySql::setAutocommit(true);
        return true;
    }

    /**
     * 推广员列表
     * @param $page
     * @param $limit
     * @param $type
     * @param $rid
     * @return array|string
     */
    static function promoterList($page, $limit, $type, $rid) {
        $data = array('total'=>0, 'list'=>array());
        $start = ($page-1)*$limit;
        $typeSql = '';
        if ($type === 'no') {
            $typeSql = 'where type = -1';
        }
        elseif ($type === 'yes') {
            $typeSql = 'where type = 1';
        }
        elseif ($type === 'await' ) {
            $typeSql = 'where type = 0';
        }
        if ($rid) {
            $typeSql = "where rid = '$rid'";
            $data['total'] = 1;
        }
        else{
            $totalSql = "select count(*) as total from ".Config::SQL_DB.".promoter $typeSql";
            $totalResult = ToolMySql::query($totalSql);
            $data['total'] = $totalResult->fetch_assoc()['total'];
        }

        $selectSql = "select * from ".Config::SQL_DB.".promoter  $typeSql order by create_at DESC limit $start,$limit  ";
        $promoterListResult = ToolMySql::query($selectSql);
        if (!$promoterListResult || !$promoterListResult) {
            return '查询推广员列表出错';
        }

        $promoterList = $promoterListResult->fetch_all(MYSQLI_ASSOC);
        foreach ($promoterList as $promoter) {
            $temp = array();
            $temp['rid'] = $promoter['rid'];
            $temp['username'] = $promoter['username'];
            $temp['parent_id'] = $promoter['parent_id'];
            if (!$promoter['parent_id'])
                $temp['parent_id'] = '-';
            $temp['from_uid'] = $promoter['from_uid'];
            if (!$promoter['from_uid'])
                $temp['from_uid'] = '-';
            $temp['pro_uid'] = $promoter['pro_uid'];
            if (!$promoter['pro_uid'])
                $temp['pro_uid'] = '-';
            if ($promoter['status'] == -1) {
                $temp['status'] = '不通过';
                $temp['operated'] = true;
            }
            if ($promoter['status'] == 1) {
                $temp['status'] = '通过';
                $temp['operated'] = true;
            }
            if ($promoter['status'] == 0) {
                $temp['status'] = '待审核';
                $temp['operated'] = false;
            }
            $temp['create_at'] = $promoter['create_at'];

            $content = json_decode($promoter['data'],true);
            $temp['request'] = $content['request'];
            $temp['response'] = $content['response'];
            $data['list'][] = $temp;
        }
        return $data;
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

    /**
     * 查找推广员邀请的所有玩家
     * @param $promoterId
     * @return array
     */
    static function findPromoterAllPlayers($promoterId) {
        $directList = self::findFromUid($promoterId);
        $flat = 1;
        while($flat) {
            foreach ($directList as $playerId) {
                $fissionList = self::findFromUid($playerId);
                if (sizeof($fissionList)>0) {
                    $flat = 1;
                    $directList =  array_merge_recursive($directList,$fissionList);
                }else
                    $flat = 0;
            }
            $directList = array_unique($directList);
        }
        return $directList;
    }

    /**
     * 判断是否是推广员
     * @param $userId
     * @return bool|string
     */
    static function isPromoter($userId) {
        $selectSql = "select status from ".Config::SQL_DB.".promoter where id= '$userId'";
        $result = ToolMySql::query($selectSql);
        if(!$result)
            return '查询出错';
        if ($result->num_rows === 0)
            return false;
        $status = $result->fetch_assoc()['status'];
        if ($status === 1 || $status ==='1')
            return true;
        return false;
    }


    /**
     * 查找推广员玩家下的消费总额/快速赛总额
     * @param $userId
     * @param string $match
     * @return array
     */
    static function getPlayerConsume($userId, $match='race') {
        $data = self::$data;
        //筛选出所有快速赛和好友赛
        $fastLocaleId = ToolForAgent::getAllFastList();
        //查找出我的所有玩家，包括分裂的
        $playerList = self::findPromoterAllPlayers($userId);
        if(!sizeof($playerList))
            return $data;

        $allConsume = array();
        foreach ($playerList as $player) {
            $oneConsumeSql = "select consume_amount, localeId from consume where user_id = '$player' and status = 1; ";
            $result = ToolMySql::query($oneConsumeSql);
            $oneConsumeList = $result->fetch_all(MYSQLI_ASSOC);

            foreach ($oneConsumeList as $oneConsume){
                $consume_amount = $oneConsume['consume_amount'];
                $localeId = $oneConsume['localeId'];
                $temp = array("consume_amount"=>$consume_amount,"localeId"=>$localeId);
                $allConsume[] = $temp;
            }
        }
        //计算所有总额和快速赛总额
        foreach ($allConsume as $consumeInfo) {
            //总
            $data["consume_total"] += $consumeInfo['consume_amount'];
            //快速
            if(in_array($consumeInfo['localeId'],$fastLocaleId ))
                $data["fast_consume_total"] += $consumeInfo['consume_amount'];
        }
        return $data;
    }

    /**
     * 获取一个推广员的收益
     * @param $userId
     * @param $ON bool true的数据格式是按游戏服务端的给，false为自己用的格式
     * @return array
     */
    static function getProfit($userId, $ON) {
        $data = array("inviteTotal"=>0, "profitTotal"=>0, "canGet"=>0);
        //受邀玩家人数
        $data['inviteTotal'] = sizeof(self::findPromoterAllPlayers($userId));;
        //先获取自身的收益
        $consumeData = self::getPlayerConsume($userId);
        $myFastProfit = $consumeData['fast_consume_total'] * Config::ServerFee * Config::LadderAgentMax * Config::PromoterFee;
        $myOtherProfit = ($consumeData['consume_total'] -$consumeData['fast_consume_total']) * Config::ServerFee * Config::AgentMAX * Config::PromoterFee;
        $data['profitTotal']= round(($myFastProfit + $myOtherProfit),2);
        //减去已经拿走的
        $given = self::givenAwardForGame($userId);
        $data['canGet'] = round($data['profitTotal']-$given, 2);
        return $data;
    }

    /**
     * 玩家领取乐券
     * @param $userId
     * @return bool
     */
    static function getAward($userId) {
        //可领取金额 = 总收益 - 已领取
        $currentTotal = self::getProfit($userId,true)['canGet'];
        if ($currentTotal < self::$minGetPrize) {
            return false;
        }

        $resultString = DajialeService::companyTransfer($userId,$currentTotal);
        $resultObj = json_decode($resultString, true);
        $rid = self::getRidByUserId($userId);
        $status = 1;

        if ($resultObj['code'] !== "200") {
            $status = 0;
        }
        $insertSql = "INSERT INTO ".Config::SQL_DB.".`getprize`(`userId`,`rid`,`amount`,`status`,`msg`)
                        VALUES('$userId','$rid','$currentTotal','$status', '$resultString')";

        ToolMySql::query($insertSql);
        if (!$status)
            return false;
        return true;
    }
}


//ToolMySql::conn();
//Promotion::bePromoter('2020032213000555367');
//Promotion::getFromUidInfo('2020032213000555367');
//Promotion::getFromUid('2019062511000009002');
//var_dump(Promotion::bePromoter('2020032123000555365'));//成为推广员
//var_dump(Promotion::operatePromoter('20119',true));//
//var_dump(Promotion::getPlayerConsume('2020032213000555367'));//推广员下玩家消耗
//var_dump(Promotion::getProfit('2019061418000006002',false) );//推广员下玩家消耗
//var_dump(Promotion::getProChain('2020041920000658038'));//推广员下玩家消耗
//var_dump(Promotion::promoterList(1,5,''));
//var_dump(Promotion::isPromoter('2020032213000555367'));
//var_dump(Promotion::getAward('2020032213000555367'));
//var_dump(Promotion::givenAwardList(1,3,"error", '20123'));
//var_dump(Promotion::isAward('2020032213000555367'));
//ToolMySql::conn();
//var_dump(Promotion::getProfit('2019061418000006002',true) );//推广员下玩家消耗
//var_dump(Promotion::findPromoterAllPlayers("2019061418000006002"));
//ToolRunTime::AvgRunTime(1,"Promotion::getProfit",array("2019061418000006002",true) ,true);
//Promotion::getProfit("2019061418000006002",true);
//ToolMySql::close();