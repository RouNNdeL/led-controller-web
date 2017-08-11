<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 11/08/2017
 * Time: 16:14
 */
header("Content-Type: application/json");
if($_SERVER['REQUEST_METHOD'] !== 'POST')
{
    echo "{\"status\":\"error\",\"message\":\"Invalid request\"}";
    http_response_code(400);
    exit(400);
}
$json = json_decode(file_get_contents("php://input"), true);
if(!isset($json["profile_n"]))
{
    http_response_code(400);
    echo "{\"status\":\"error\",\"message\":\"Invalid JSON\"}";
    exit(400);
}
$profile_n = $json["profile_n"];
require_once(__DIR__."/../web/includes/Data.php");
Data::getInstance()->removeProfile($profile_n);
Data::save();
echo json_encode(array("status" => "success"));