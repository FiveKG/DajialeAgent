<?php
/**
 * Created by YX.
 * Date: 2019-03-25
 * Time: 14:11
 */

class Result
{
    var $handle;
    var $eventJson;
}

class EchoResultToClient
{
    static function echoResult($handle, $data)
    {
        echo self::getEventJson($handle, $data);
    }

    static function getEventJson($handle, $data)
    {
        $result = new Result();
        $result->handle = $handle;
        $result->eventJson = json_encode($data, JSON_UNESCAPED_UNICODE);
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }
}

class EchoResultToSocket
{
    var $arrayJson;

    function __construct()
    {
        $this->arrayJson = array();
    }

    function pushEvent($handle, $data)
    {
        $result = new Result();
        $result->handle = $handle;
        $result->eventJson = json_encode($data);
        array_push($this->arrayJson, json_encode($result));
    }

    function echoResult()
    {
        echo json_encode($this->arrayJson);
    }
}

class EchoToClientSocket
{
    static $instance;

    /**
     * @return EchoToClientSocket
     */
    static function ins()
    {
        if (null == EchoToClientSocket::$instance)
            EchoToClientSocket::$instance = new EchoToClientSocket();
        return EchoToClientSocket::$instance;
    }

    /** @var array DataToSocketClient */
    var $datas = array();

    function addSendData($playerId, $handle, $obj)
    {
        $dataToSocketClient = new DataToSocketClient();
        $dataToSocketClient->playerId = $playerId;
        $r = new Result();
        $r->handle = $handle;
        $r->eventJson = json_encode($obj);
        $dataToSocketClient->sendText = json_encode($r);
        array_push($this->datas, $dataToSocketClient);
    }

    function addErrorData($playerId, $error)
    {
        $data = array();
        $data["error"] = $error;
        self::ins()->addSendData($playerId, "/error", $data);
    }

    function echoResult()
    {
        $arrJson = array();
        $r = new Result();
        $r->handle = "/sendToClient";
        $r->eventJson = json_encode($this->datas);
        array_push($arrJson, json_encode($r));

        echo json_encode($arrJson);
        EchoToClientSocket::$instance = null;
    }
}

class DataToSocketClient
{
    var $playerId;
    var $sendText;
}

class RandomAiInfo
{
    static function randomName()
    {
        $arrFirst = array(
            "成熟","静谧","狂热","轻盈","厚重","神圣","混乱",
            "痴情","亲爱","愚笨","复生","绝命","遗忘","真诚",
            "摸鱼","铲屎","破碎","杰出","夜下","勇敢","质朴",
            "纯洁","无畏","疯狂","伟大"
        );
        $arrSecond = array(
            "的","之","想"
        );
        $arrThrid = array(
            "御灵士","相簿","翅膀","史莱喵","忠犬","水神","残暴",
            "酒鬼","御宅","熊人族","天人","钻头","机师","铲屎官",
            "深海","烈焰","飓风","大地","光明","黑暗","使徒",
            "劳模","罗盘娘","街舞队","希望","狂犬","宿敌","天王",
            "勇者","火箭鸡","霹雳鸭","咸鱼","名侦探","狗头人","猎手",
            "脚男","征服王","村民","上将","终结者","猎人","神职者",
            "神父","女巫","血兽","黑兽","主教","三基佬","七刀",
            "该隐","使者","奶妈","猎手","月神","大将","司令官",
            "店主","黑洞","魅影","战神","王","酋长","萌新",
            "欧鳇","玄学家","决斗者","伙伴","小子","少年","格斗家",
            "男生","灵能者","旅行家","少爷","大叔","收藏家","骑士",
            "男爵","伯爵","宫廷","公爵","大公","亲王","选帝侯",
            "国王","老咸鱼"
        );

        $firName = $arrFirst[rand(0,count($arrFirst) - 1)];
        $secName = $arrSecond[rand(0,count($arrSecond) - 1)];
        $thrName = $arrThrid[rand(0,count($arrThrid) - 1)];
        return $firName.$secName.$thrName;
    }

    static function randomHeadIcon()
    {
        return rand(0,19);
    }

    static function checkIsAi($playerId)
    {
        $ss = substr($playerId,0,2);
        if($ss == "ai")
        {
            return true;
        }
        return false;
    }
    static function randomLeDou()
    {
        return rand(200,10000);
    }
}