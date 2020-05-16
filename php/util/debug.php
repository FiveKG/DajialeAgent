<?php
include_once dirname(__FILE__) . "/../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../service/promotion.php";
class Debug {
    /**
     * 清空该代理下的所有代理商
     * @param $agentId
     * @return bool|mysqli_result
     */
    static function clearAgent($agentId) {
        $clearSql = "update agentusers set parent_id = '' where parent_id = '$agentId'; ";
        return ToolMySql::query($clearSql);
    }

    /**
     * 生成代理
     * @param $agentId
     * @param $tel
     * @return bool|string
     */
    static function makeAgent($agentId,$tel,$profit) {
        $passwordHash = password_hash('123456', PASSWORD_DEFAULT);
        $username = uniqid();

        $findPidSql = "select id,level,status from agentusers where id = '$agentId'";
        $findPidResult = ToolMySql::query($findPidSql);
        $findPid = $findPidResult->fetch_assoc();
        $pLevel = $findPid['level'];
        $id =  $findPid['id'];
        $pStatus = $findPid['status'];
        if (!$id)
            return '此代理不存在';
        if ($pLevel =='3')
            return '此代理不可再生成下级代理';
        $level = ((int)$pLevel)+1;
        if ($pStatus != '1')
            return '此代理状态不正常';

        $findUsernameSql = "select id from agentusers where username = '$username'";
        $findUsernameResult = ToolMySql::query($findUsernameSql);
        $row = $findUsernameResult->num_rows;
        if ($row)
            return '用户名已存在';

        $findTelSql = "select id from agentusers where tel = '$tel'";
        $findTelResult = ToolMySql::query($findTelSql);
        $row = $findTelResult->num_rows;
        if ($row)
            return '电话号码已存在';

        $registerSql = "INSERT INTO `operation`.`agentusers`
                    (`username`,`password`,`parent_id`,`level`,`status`,`tel`,`profit`)
                VALUES
                    ('$username','$passwordHash','$agentId','$level','1','$tel','$profit')";

        if (ToolMySql::query($registerSql)  !== true) {
            return false;
        }
        var_dump("'$agentId'添加下级代理:'$id',手机:$tel,分成:$profit");
        return true;
    }

    /**
     * 生成玩家
     * @param $agentId
     * @param $fromUid
     * @return string
     * @throws Exception
     */
    static function addPlayerUser($agentId, $fromUid) {
        //注册
        $id = uniqid();
        $rid = random_int(10000,20000);
        $unionId = uniqid();
        $username = uniqid();
        $phoneNumber = random_int(1000,2000).random_int(1000,9999).random_int(100,999);
        $insertSql = "insert into operation.playerusers (`id`,`rid`,`wxunionid`,`username`,`parent_id`,`tel`,`from_uid`) values ('$id','$rid','$unionId','$username', '$agentId', '$phoneNumber','$fromUid')";
        $result = ToolMySql::query($insertSql);
        if (!$result)
            return "注册玩家失败，sql:".$insertSql;
        var_dump("生成玩家'$id',代理为'$agentId',受邀于'$fromUid'");
        return $id;
    }

    /**
     * 玩家充值
     * @param $userId
     * @param $amount
     * @return bool|string
     */
    static function playerCharge($userId, $amount) {
        $id = uniqid();
        $insertSql = "INSERT INTO `operation`.`charge`(`id`,`user_id`,`charge_amount`,`currency`,`status`,`mode`,`game_id`,`create_at`)
	                    VALUES('$id','$userId','$amount','乐券',1,12,'2019122822000018001',now())";
        $result = ToolMySql::query($insertSql);
        if (!$result)
            return "充值失败,sql:".$insertSql;
        var_dump("玩家'$userId'充值金额:'$amount'");
        return true;
    }

    /**
     * 玩家消耗
     * @param $userId
     * @param $amount
     * @param $localeId
     * @return bool|string
     */
    static function playerConsume($userId, $amount, $localeId) {
        $matchid = uniqid();
        $insertSql = "INSERT INTO `operation`.`consume`(`user_id`,`matchid`,`consume_amount`,`create_at`,`status`,`localeId`,`rank`,`score`,`is_lord`,`ladder`)
	        VALUES('$userId','$matchid','$amount',now(),1,'$localeId','1','100','1','10')";
        $result = ToolMySql::query($insertSql);
        if (!$result)
            return "消耗失败,sql:".$insertSql;
        var_dump("玩家'$userId'消耗金额:'$amount'");
        return true;
    }

    static function addPromoter($agentId, $fromUid) {
        $id1 = self::addPlayerUser($agentId, $fromUid);
        self::playerCharge($id1, 100);

        for($i = 0; $i<5; $i++) {
            $id2 = self::addPlayerUser($agentId, $id1);
            self::playerCharge($id2, 100);
            var_dump("$id2,100");
        }
        Promotion::bePromoter($id1);
        $sql = "select rid from playerusers where id = '$id1'";
        $result = ToolMySql::query($sql);
        $rid = $result->fetch_assoc()['rid'];
        Promotion::operatePromoter($rid, true);

        var_dump("推广员:$id1");
    }

    static function  consumeAll($amount,$localeId){
        $sql = "select id from promoter ";
        $reuslt= ToolMySql::query($sql);
        $list = $reuslt->fetch_all(MYSQLI_ASSOC);
        foreach ($list as $info ) {
            self::playerConsume($info['id'], $amount, $localeId);
        }
    }
}
//ToolMySql::conn();
////var_dump(Debug::makeAgent('10040', '18814123231', 40));
/////// //5ea43fc737c26
//////var_dump(Debug::playerCharge('5ea44200a7bca','100'));
//for($i = 0 ; $i<5;$i++) {
//var_dump(Debug::addPlayerUser('','546876'));
//}
//var_dump(Debug::playerCharge('5ebafdccb0041','10000'));
//var_dump(Debug::playerCharge('5ebafdccb0cac','10000'));
//var_dump(Debug::playerCharge('5ebafdccb151f','10000'));
//var_dump(Debug::playerCharge('5ebafdccb1c3a','10000'));
//var_dump(Debug::playerCharge('5ebafdccb246e','10000'));
//var_dump(Debug::playerCharge('546876','10000'));
//////////
////
//var_dump(Debug::playerConsume('5ebafdccb0041','11000','101'));
//var_dump(Debug::playerConsume('5ebafdccb0cac','11000','101'));
//var_dump(Debug::playerConsume('5ebafdccb151f','11000','101'));
//var_dump(Debug::playerConsume('5ebafdccb1c3a','11000','101'));
//var_dump(Debug::playerConsume('5ebafdccb246e','11000','101'));
//var_dump(Debug::playerConsume('546876','11000','101'));
////
////
//////$agentId,$tel,$profit
////var_dump(Debug::makeAgent('10040','18811112222', 30));
////ToolMySql::conn();
//Debug::addPromoter('','5ebb0360bde11');
//Debug::consumeAll('11000','205');
//ToolMySql::close();