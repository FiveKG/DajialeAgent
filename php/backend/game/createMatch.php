<?php
include_once dirname(__FILE__) . "/../../util/postJson.php";
include_once dirname(__FILE__) . "/../../mysql/toolMySql.php";
include_once dirname(__FILE__) . "/../../util/statusCode.php";
include_once dirname(__FILE__) . "/../../service/game.php";
include_once dirname(__FILE__) . "/../../util/jwt.php";
try {
    $jsonPost = new GamePostOrGetJson();

    $match = array(
        'localeName'=>'string', 'perNumb'=>'int', 'userNumb'=>'int', 'gameType'=>'int',
        'ctrlCardLv'=>'int', 'category'=>'int', 'maxNumb'=>'int', 'matchServerId'=>'int', 'onoff'=>'int'
    );
    $casino = array(
        "condition"=>'int', "category"=>'int', "outRule"=>'int', "entranceFee"=>'int',
        "roomCost"=>'int', "carryMax"=>'int', "winMax"=>'int', "baseLine"=>'int'
    );
    $race = array(
        "raceTime"=>'string', "enrollTime"=>'string', "round"=>'int', "pernum"=>'string',
        "userNumb"=>'int', "score"=>'int', "condition"=>'string', "category"=>'int',
        "outRule"=>'string', "roomCost"=>'int', "baseLine"=>'int' , "tactics" => 'string'
    );
    $award = array(
        "minId"=>'int', "maxId"=>'int', "awards"=>'string'
    );
    $matchValidator = array(
        "sameIP"=>'int', "onlyAI"=>'string', "sameDesk"=>'string', "winRatio"=>'string', "winStreak"=>'string',
        "onoff"=>'int', "ipNoDesk"=>'string'
    );

    if (!$TokenResult = Jwt::verifyToken()) {
        StatusCode::responseError(StatusCode::SYS_TOKEN_VERIFY_FAIL, "token验证失败");
    }

    $requireData = array();
    //匹配配置
    //判断转换类型
    $requireData['match'] = $jsonPost->getStr("match");
    foreach ($match as $key => $value) {
        $matchValue = $requireData['match']->$key;

        if (is_null($matchValue))
            StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, $key."不能为空");

        if ($value ==='int') {
            if(!is_numeric($matchValue))
                StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, $key."类型错误");
            $requireData['match']->$key = (int)$matchValue;
        }
    }

    if( $requireData['match']->category == 1) {
        //娱乐房配置
        $requireData['casino'] = $jsonPost->getStr("casino");
        foreach ($casino as $key => $value) {
            $casinoValue = $requireData['casino']->$key;
            if (is_null($casinoValue))
                StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, $key."不能为空");

            if ($value ==='int') {
                if(!is_numeric($casinoValue))
                    StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, $key."类型错误");
                $requireData['casino']->$key = (int)$casinoValue;
            }
        }
    }
    else if( $requireData['match']->category == 2) {
        //比赛
        $requireData['race'] = $jsonPost->getStr("race");
        foreach ($race as $key => $value) {
            $raceValue = $requireData['race']->$key;

            if (is_null($raceValue))
                StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, $key . "不能为空");

            if ($value === 'int') {
                if (!is_numeric($raceValue))
                    StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, $key . "类型错误");
                $requireData['race']->$key = (int)$raceValue;
            }
        }

        //比赛奖励
        $requireData['award'] = $jsonPost->getStr("award");
        foreach ($award as $key => $value) {
            $awardValue = $requireData['award']->$key;
            if (is_null($awardValue))
                StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, $key . "不能为空");

            if ($value === 'int') {
                if (!is_numeric($awardValue))
                    StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, $key . "类型错误");
                $requireData['award']->$key = (int)$awardValue;
            }
        }
    }
    //检验器
    $requireData['matchValidator'] = $jsonPost->getStr("matchValidator");
    foreach ($matchValidator as $key => $value) {
        $matchValidatorValue = $requireData['matchValidator']->$key;
        if (is_null($matchValidatorValue))
            StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, $key."不能为空");

        if ($value ==='int') {
            if(!is_numeric($matchValidatorValue))
                StatusCode::responseError(StatusCode::SYS_QUERY_PARAMS_ERROR, $key."类型错误");
            $requireData['matchValidator']->$key = (int)$matchValidatorValue;
        }
    }

    ToolMySql::conn();
    $result = Game::createMatch($requireData);
    if ($result !== true)
        StatusCode::responseError(StatusCode::SYS_MYSQL_QUERY_ERROR, $result);
    StatusCode::responseSuccess(StatusCode::SUCCESS, $result);

    ToolMySql::close();
} catch (Exception $e) {
    echo $e->getMessage();
}