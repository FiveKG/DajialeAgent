<?php
include_once dirname(__FILE__) . "/../util/statusCode.php";

try {
    $json_string = file_get_contents('../settings.json');

    $data = json_decode($json_string, true);

    StatusCode::responseSuccess(StatusCode::SUCCESS, $data);
} catch (Exception $e) {
    echo $e->getMessage();
}