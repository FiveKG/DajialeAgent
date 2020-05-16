<?php
include_once dirname(__FILE__) . "/util/toolTime.php";
$passwordHash = password_hash('123456', PASSWORD_DEFAULT);
var_dump($passwordHash);

var_dump(ToolTime::getToday());
var_dump(date('Y-m-d H:i',ToolTime::getLocalSec()));
var_dump(date('Y-m-d H:i',time()));
var_dump(date('Y-m-d H:i',ToolTime::getUtc()));
var_dump('?');
echo($str);

