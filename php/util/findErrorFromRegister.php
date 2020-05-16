<?php
include_once dirname(__FILE__) . "./register2Agent.php";
include_once dirname(__FILE__) . "../mysql/toolMySql.php";

ToolMySql::conn();
Register2Agent::findError();
ToolMySql::close();
