<?php
class ToolRunTime {
    static function AvgRunTime($time, $function,$params, $show ) {
        $allTime = 0;
        for($i =0; $i<$time; $i++) {
            $startTime = microtime(true);
            ToolMySql::conn();
            $data = call_user_func_array($function,$params);
            ToolMySql::close();
            $endTime = microtime(true);
            $runTime = ($endTime-$startTime)*1000;
            $allTime+= $runTime;
        }
        var_dump(($allTime/$time).' ms');
        if ($show) {
            var_dump($data);
        }
    }
}