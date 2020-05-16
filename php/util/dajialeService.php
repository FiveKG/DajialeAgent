<?php
include_once dirname(__FILE__) . "/../util/toolNet.php";
include_once dirname(__FILE__) . "/../util/toolTime.php";
include_once dirname(__FILE__) . "/../config.php";

class DajialeService {

    static $statusType = array(1=>'待支付',2=>'充值处理中',3=>'充值成功',4=>'充值失败');
    static $modeType = array(1=>'微信',2=>'支付宝',12=>'奖金');
    static $caseStatusType = array(1=>'待审核',2=>'审核通过',3=>'审核不通过',4=>'提现处理中',5=>'提现成功',6=>'提现失败');

    /**
     * 同步玩家和玩家的联系给大家乐
     * @param $userId
     * @param $from_uid
     * @return bool
     */
    static function SynP2p($userId,$from_uid) {
        $path = "/user/updateUserInvitation/";
        $argMap = array("userId"=>$userId,"invitationCode"=>$from_uid);

        $response = self::sendPost($argMap, $path);
        $response = json_decode($response,true);
        if($response['code'] !== "200")
            return $response;
        return true;
    }
    /**
     * 获取玩家资料
     * @param String $userId
     * @return bool
     */
    static function getAccountData( $userId) {
        $path = "/useraccount/getByUserId/".$userId;
        $response = self::sendGet(array(), $path);

        $response = json_decode($response,true);
        if($response['code'] !== "200")
            return false;
		return $response['obj'];
    }


    /**
     * 分页查询选手充值
     * @param string $userId 为空时查询所有订单
     * @param string $tradeNo
     * @param $startDate
     * @param $endDate
     * @param $page
     * @param $limit
     * @return bool
     */
    static function rechargePage($startDate, $endDate, $page, $limit, $userId ="",$tradeNo='') {
        $path = "/recharge/page";
        $argMap = array("params"=>array("gameId"=>Config::gameId));
        if ($userId)
            $argMap['params']['userId'] = $userId;
        if ($startDate && $endDate) {
            $argMap["startTime"] = $startDate;
            $argMap["endTime"] = $endDate;
        }
        if ($page && $limit)
            $argMap["page"] = array("size"=>(int)$limit,"current"=>(int)$page);
        if ($tradeNo)
            $argMap = array("params"=>array("gameId"=>Config::gameId,"tradeNo"=>$tradeNo));

        $response = self::sendPost($argMap, $path);
        $response = json_decode($response,true);

        if($response['code'] !== "200")
            return false;
        return $response['obj'];
    }

    /**
     * 充值统计分页查询(详细)
     * @param string $userId 为空则为全部
     * @param int $status 0-发起 1-待支付 2-充值处理中 3-充值成功 4-充值失败 5-已关闭 6-转入退款 7-已撤销
     * @param array[int] $modes 1-微信，2-支付宝 12-奖金充值（不传默认查全部）
     * @param string $startDate
     * @param string $endDate
     * @param string $page
     * @param string $limit
     * @return bool
     */
    static function getRechargeDetail($userId='',$status=3, $modes='',$startDate='', $endDate='', $page='', $limit='') {
        $path = "/recharge/getRechargeDetail";

        $argMap = array("params"=>array("gameId"=>Config::gameId));
        if ($startDate && $endDate) {
            $argMap["startTime"] = $startDate;
            $argMap["endTime"] = $endDate;
        }
        if($userId)
            $argMap['params']['userId'] = $userId;
        if($status)
            $argMap['params']['status'] = $status;
        if($modes)
            $argMap['params']['modes'] = $modes;
        if ($page && $limit)
            $argMap["page"] = array("size"=>(int)$limit,"current"=>(int)$page);

        $response = self::sendPost($argMap, $path);
        $response = json_decode($response,true);

        if($response['code'] !== "200")
            return false;
        return $response['obj'];
    }




    /**
     * 分页查询天梯积分流水
     * @return bool
     */
    static function ladderDetail($startDate, $endDate, $page, $limit, $userId="", $matchId="" ) {
        $path = "/ladderDetail/page";
        $argMap = array("params"=>array("gameId"=>Config::gameId));

        if ($userId)
            $argMap = array("params"=>array("userId"=>$userId, "gameId"=>Config::gameId));
        if ($matchId)
            $argMap = array("params"=>array("userId"=>$userId, "gameId"=>Config::gameId,"matchId"=>$matchId));
        if ($startDate && $endDate) {
            $argMap["startTime"] = $startDate;
            $argMap["endTime"] = $endDate;
        }
        if ($page && $limit)
            $argMap["page"] = array("size"=>(int)$limit,"current"=>(int)$page);

        $response = self::sendPost($argMap, $path);

        $response = json_decode($response,true);

        if($response['code'] !== "200")
            return false;
        return $response['obj'];
    }

    /**
     * 查询提现列表
     * @param $userId
     * @param $startDate
     * @param $endDate
     * @param $page
     * @param $limit
     * @return bool
     */
    static function  cashPage($userId,$startDate, $endDate, $page, $limit) {
        $path = "/cash/page";
        $argMap = array("params"=>array("userId"=>"$userId"));

        if ($startDate && $endDate) {
            $argMap["startTime"] = $startDate;
            $argMap["endTime"] = $endDate;
        }
        if ($page && $limit)
            $argMap["page"] = array("size"=>(int)$limit,"current"=>(int)$page);

        $response = self::sendPost($argMap, $path);

        $response = json_decode($response,true);
        if($response['code'] !== "200")
            return false;
        return $response['obj'];
    }

    /**
     * 查询赛事
     * @param $page
     * @param $limit
     * @param $status 1正常 2冻结 3取消赛事 4选手取消
     * @param string $userId 选手ID，为空代表所有
     * @param string $matchId 赛事ID，为空代表所有
     * @return bool
     */
    static function  matchSignUpPage($page, $limit, $status, $userId="", $matchId = "") {
        $path = "/matchsignup/page";

        $argMap = array("params"=>array("status"=>(int)$status));
        if ($userId)
            $argMap = array("params"=>array("userId"=>$userId,"status"=>$status));
        if ($matchId)
            $argMap = array("params"=>array("userId"=>$userId,"status"=>$status,"matchId"=>$matchId));


        if ($page && $limit)
            $argMap["page"] = array("size"=>(int)$limit,"current"=>(int)$page);
        $response = self::sendPost($argMap, $path);
        $response = json_decode($response,true);

        if($response['code'] !== "200")
            return false;
        return $response['obj'];
    }

    /**
     * 查询赛事详细信息
     * @param $matchId
     * @return bool
     */
    static function matchDetail($matchId) {
        $path = "/match/info/".$matchId;
        $response = self::sendGet(array(), $path);

        $response = json_decode($response,true);
        if($response['code'] !== "200")
            return false;
        return $response['obj'];
    }

    /**
     * 查询大致赛事信息
     * @param $matchId
     * @return bool
     */
    static function matchInfo($matchId) {
        $path = "/match/".$matchId;
        $response = self::sendGet(array(), $path);

        $response = json_decode($response,true);

        if($response['code'] !== "200")
            return false;
        return $response['obj'];
    }


    /**
     * 分页查询赛事奖励记录
     * @param $matchId
     * @param $userId
     * @return bool
     */
    static function matchPrizes($matchId, $userId) {
        $path = "/matchprizes/page";
        $argMap = array("params"=>array("matchId"=>$matchId,"userId"=>"$userId","gameId"=>Config::gameId));
        $response = self::sendPost($argMap, $path);
        $response = json_decode($response,true);

        if($response['code'] !== "200")
            return false;
        return $response['obj'];
    }

    /**
     * 选手交易查询（汇总）
     * @param $startTime
     * @param $endTime
     * @param string $userId 默认为空，全部
     * @param string $status 状态：0-发起 1-待支付 2-充值处理中 3-充值成功 4-充值失败 5-已关闭 6-转入退款 7-已撤销
     * @param array[int] $modes 1-微信，2-支付宝 12-奖金充值 不传默认查全部
     * @return bool
     */
    static function getRechargeTotal($startTime, $endTime, $userId='',$status=3,$modes='' ) {
        $path = "/recharge/getRechargeTotal";
        $argMap = array("startTime"=>$startTime, "endTime"=>$endTime, "params"=>array("gameId"=>Config::gameId));
        if ($userId)
            $argMap["params"]['userId'] = $userId;
        if ($status)
            $argMap["params"]['status'] = $status;
        if ($modes)
            $argMap["params"]['mode'] = $modes;


        $response = self::sendPost($argMap, $path);
        $response = json_decode($response,true);
        if($response['code'] !== "200")
            return false;
        return $response['obj'];
    }

    /**
     * 消费总额统计
     * @param $startTime
     * @param $endTime
     * @return bool
     */
    static function getConsumeTotal($startTime='', $endTime='') {
        $path = "/matchsignup/sumConsumeTotal";

        $argMap = array("gameId"=>Config::gameId);
        if ($startTime && $endTime)
            $argMap = array("gameId"=>Config::gameId,"startTime"=>$startTime,"endTime"=>$endTime );

        $response = self::sendPost($argMap, $path);
        $response = json_decode($response,true);
        if($response['code'] !== "200")
            return false;
        return $response['obj'];
    }

    /**
     * @param string $startTime
     * @param string $endTime
     * @param string $page
     * @param string $limit
     * @param string $machine 1-非AI，2-AI，其他值-全部
     * @param string $gameMaster 1表示过滤gameMaster,即陪玩用户
     * @return bool
     */
    static function pageRecharge($machine='',$gameMaster=1,$startTime='', $endTime='', $page='', $limit='') {
        $path = "/consume/page/recharge";

        $argMap = array("params"=>array("gameId"=>Config::gameId));
        if ($startTime && $endTime) {
            $argMap["startTime"] = $startTime;
            $argMap["endTime"] = $endTime;
        }
        if ($page && $limit)
            $argMap["page"] = array("size"=>(int)$limit,"current"=>(int)$page);
        if ($machine)
            $argMap['params']['machine'] = $machine;
        if ($gameMaster)
            $argMap['params']['gameMaster'] = $gameMaster;

        $response = self::sendPost($argMap, $path);
        $response = json_decode($response,true);

        if($response['code'] !== "200")
            return false;
        return $response['obj'];
    }

    /**
     * 公司转账用户
     * @param $userId
     * @param $amount
     * @return string{
            "code": "200",
            "msg": "操作成功",
            "obj": "转账成功"
            }
     */
    static function companyTransfer($userId,$amount) {
        $path = "/companyaccount/companyTransfer";
        $argMap = array("gameId"=>Config::gameId, "userId"=>$userId,"amount"=>$amount);

        $response = self::sendPost($argMap, $path);
        return $response;
    }

    /**
     * @param $argMap
     * @param $path
     * @return bool|string
     */
    static function  sendGet($argMap, $path) {
        $url = Config::DajialeGetway.$path;
        $headMap = self::getSignHeadMap();
        $sign = self::signDajiale($argMap, $headMap, "/match/api".$path);
        $sha256Sign = self::signSha256($sign);
        $headMap["sign"] = $sha256Sign;

        $hearders = self::getHeaders($headMap);
        return ToolNet::sendByGet($url, $argMap, $hearders);
    }


    /**
     * 发送post请求
     * @param argMap
     * @param path
     * @return
     */
    static function sendPost($argMap,$path) {
		$url = Config::DajialeGetway.$path;
		$headMap =self:: getSignHeadMap();
		$sign = self::signDajiale($argMap, $headMap, "/match/api".$path);
		$sha256Sign = self::signSha256($sign);
		$headMap["sign"] = $sha256Sign;

        $hearders = self::getHeaders($headMap);

		return ToolNet::sendByPost($url, $argMap, $hearders);

	}

    /**
     * 把头转换为php的头格式
     * @param $headMap
     * @return array
     */
	static function getHeaders($headMap) {
        $hearders = [];
        foreach($headMap as $key=>$value) {
            $element = $key.":".$value;
            $hearders[] = $element;
        }
        return $hearders;
    }



    static function signSha256($sign) {
		return strtoupper(self::getSHA256Str($sign.Config::DajialeSecret));
	}

	static function getSHA256Str($str) {
        return hash("sha256", $str);
	}

    /**
     * 签名要素
     * @return array
     */
    static function getSignHeadMap(){
		$headMap = array();
        $headMap["timestamp"] =(string)ToolTime::current_millis();
        $headMap["companyId"] = Config::DajialeUserId;
		return $headMap;
	}

    /**
     * 生成签名
     * @param argMap
     * @param url
     * @return
     */
    static function signDajiale($argMap,$headMap,$url) {
		$signMap = $argMap;
        $signMap = array_merge($signMap,$headMap);
		if($url) {
            $signMap['uri'] = $url;
		}
        return self::signString($signMap);
    }

    /**判断是否关联数组
     * @param $arr
     * @return bool
     */
    static function _checkAssocArray($arr)
    {
        if(!is_array($arr))
            return false;
        return array_diff_assoc(array_keys($arr), range(0, sizeof($arr))) ? TRUE : FALSE;
    }


    /**
     * 签名用字符串
     * @param $signMap {xx=>['1','2']}
     * @return mixed
     */
    static function signString($signMap) {
		$signBuilder = "";
        $paramNameList = array_keys($signMap);
        sort($paramNameList);

        foreach($paramNameList as $param) {
            if (self::_checkAssocArray($signMap[$param])) {
                $subSign = self::innerMapSign($signMap[$param]);
                $signBuilder = $signBuilder.$param."={".$subSign."}"."&";
            }else if (is_array($signMap[$param])) {
                $subSign = self::innerListSign($signMap[$param]);
                $signBuilder = $signBuilder."=[".$subSign."]"."&";
            }else {
                $signBuilder = $signBuilder.$param."=".$signMap[$param]."&";
            }
        }

        return substr($signBuilder,0,strlen($signBuilder)- strlen('&'));
	}

	static function innerMapSign($innerMap) {
		$builder = "";
		$keys = array_keys($innerMap);
        sort($keys);
		foreach ($keys as $key) {
            $builder = $builder."\"".$key."\"".":";
            $val = $innerMap[$key];

            if(is_string($val)) {
                $builder = $builder."\"".$val."\",";
            }else {
                $builder = $builder.$val.",";
            }
        }
        return $builder = substr($builder, 0, -1);
    }

    /**
     * @param $list 对象数组
     * @return string
     */
    static function innerListSign($list) {
        $builder = "";
        foreach ($list as $one) {
            if(self::_checkAssocArray($one)) {
                $builder = $builder."{".self::innerMapSign($one)."}".",";
            }else {
                $builder = $builder.(string)$one.",";
            }
        }
        return $builder = substr($builder, 0, -1);
    }
}
//var_dump(DajialeService::getAccountDate("2020032814000189019"));

//var_dump(DajialeService::matchsignupPage("2020032814000189019","1581963200000","1585641600000", 1,5 ));
//DajialeService::getRechargeDetail("",3, '',1586620800000,1586707199000);
//DajialeService::userAccounts();
//DajialeService::matchHistory();
//DajialeService::matchsignupPage(1, 5 ,"2020040811000351638");
//DajialeService::matchInfo(2020040911001219173);
//DajialeService::matchDetail(2020040916001222247);
//DajialeService::matchPrizes("2020040916001222282","2020040811000351638");
//DajialeService::matchInfo('2020041021001251059');
//DajialeService::ladderDetail('','','','',"2020040809000348041",'2020041111001253048');
//DajialeService::getRechargeTotal("1586534400000","1586620799000");
//DajialeService::getConsumeTotal("2020-04-11 00:00:00","2020-04-11 23:59:59");
//DajialeService::pageRecharge(3, 1, 1586534400000, 1586620799000);
//DajialeService::matchSignUpPage("","",4,"2020040914000366003");
//var_dump(DajialeService::companyTransfer('2020040811000351638', '100'));