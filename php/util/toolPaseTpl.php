<?php
/**
 * Created by YX.
 * Date: 2019-05-14
 * Time: 11:59
 */
include_once dirname(__FILE__) . "/../template/Tpl_match.php";
include_once dirname(__FILE__) . "/toolTime.php";
class ParseTpl
{
    static function getMatchCfgField($matchType,$field)
    {
        if (!key_exists($matchType, Tpl_match::match))
            return 0;
        return Tpl_match::match[$matchType][$field];
    }

    static function getMatchCfgToClient($matchType)
    {
        if (!key_exists($matchType, Tpl_match::match))
            return null;
        $item = Tpl_match::match[$matchType];
        if ($item[Tpl_match::DATE] != 0)
            $item[Tpl_match::DATE] = ToolTime::strToUtc($item[Tpl_match::DATE]);
        return $item;
    }

    static function getMatchRoundInfo($matchType,$roundIdx)
    {
        if (!key_exists($matchType, Tpl_match::match))
            return array(0, 0);
        $arrOutNum = explode("|", Tpl_match::match[$matchType][Tpl_match::OUTNUM]);
        if($roundIdx >= count($arrOutNum))
            return null;
        $roundInfo = explode("*", $arrOutNum[$roundIdx]);
        return $roundInfo;
    }

    static function getRegDateDesc($matchType)
    {
        $regD1 = self::getMatchCfgField($matchType,Tpl_match::REGDATE_1);
        $regD2 = self::getMatchCfgField($matchType,Tpl_match::REGDATE_2);
        $regD3 = self::getMatchCfgField($matchType,Tpl_match::REGDATE_3);
        if ($regD1 !== 0)
        {
            return "每".$regD1."小时";
        }
        elseif ($regD2 !== 0)
        {
            return "每周".$regD2;
        }
        elseif ($regD3 !== 0)
        {
            return "每月".$regD3."号";
        }
        else
        {
            return "人满即开";
        }
    }

    static function getChangGuiNearestStartTime($matchType)
    {
        $strDate = self::getMatchCfgField($matchType,Tpl_match::DATE);
//        var_dump("111111111111   ".$strDate);strtotime("2019-5-14-17:30");//T
        $dateUtc = ToolTime::strToUtc($strDate);//strtotime($strDate);
//        var_dump("22222222222   ".$dateUtc);
//        var_dump("ress   ".date("Y-m-d h:i:s",$dateUtc));
        $curUtc = ToolTime::getUtc();

        if ($curUtc < $dateUtc)
        {
            return $dateUtc;
        }

        $regD1 = self::getMatchCfgField($matchType,Tpl_match::REGDATE_1);
        $regD2 = self::getMatchCfgField($matchType,Tpl_match::REGDATE_2);
        $regD3 = self::getMatchCfgField($matchType,Tpl_match::REGDATE_3);
//        var_dump("111111111111   ".$regD1);
        if ($regD1 !== 0)
        {
            $difSec = $curUtc - $dateUtc;
            $times = (int)($difSec/($regD1*ToolTime::OneHourSec));
//            var_dump("times   ".$times);

            $ret = $dateUtc + ($times + 1) * $regD1 * ToolTime::OneHourSec;
//            var_dump("ret   ".$ret);
//            var_dump("ress   ".date("Y-m-d h:i:s",$ret));
            return $ret;
        }
        elseif ($regD2 !== 0)
        {
             //0 - 6 ,周日到周六
            $w = date("w",$dateUtc);
             //获得本周指定星期几的 时间戳
            $dateUtc = $dateUtc - ToolTime::OneDaySec * ($w - $regD2);
            if ($dateUtc > $curUtc)
            {
                return $dateUtc;
            }

            $difSec = $curUtc - $dateUtc;
            $weeks = (int)($difSec/ToolTime::OneWeekSec);
            return $curUtc + ($weeks + 1) * ToolTime::OneWeekSec;
        }
        else{

        }
    }

    //获得比赛奖励
    static function getMatchReward($matchType,$rank){
        $per1  = self::getMatchCfgField($matchType,Tpl_match::FIRPER);
        $per2  = self::getMatchCfgField($matchType,Tpl_match::SECPER);
        $per3  = self::getMatchCfgField($matchType,Tpl_match::THIPER);
        $per4  = self::getMatchCfgField($matchType,Tpl_match::FOUPER);
        $arrTianti = explode("|", self::getMatchCfgField($matchType,Tpl_match::TT_SCORE));
        $bmCount = self::getMatchCfgField($matchType,Tpl_match::BEGNUM);

        $moneyPool = self::getMatchCfgField($matchType,Tpl_match::COST) * $bmCount;
        $awardPer = 0;
        $money = 0;
        if ($rank === 1){
            $money = $per1 * $moneyPool;
            $awardPer = $per1 * 100;
        }elseif($rank === 2){
            $money = $per2 * $moneyPool;
            $awardPer = $per2 * 100;
        }elseif($rank === 3){
            $money = $per3 * $moneyPool;
            $awardPer = $per3 * 100;
        }elseif($rank === 4){
            $money = $per4 * $moneyPool;
            $awardPer = $per4 * 100;
        }
        $ttScore = 0;
        if($rank - 1 < count($arrTianti))
            $ttScore = $arrTianti[$rank - 1];
        return array("money"=>$money,"ttScore"=>$ttScore,"awardPer" => $awardPer);
    }
}
