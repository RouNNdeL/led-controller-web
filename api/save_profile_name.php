<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 11/08/2017
 * Time: 15:42
 */
header("Content-Type: application/json");
if($_SERVER['REQUEST_METHOD'] !== 'POST')
{
    echo "{\"status\":\"error\",\"message\":\"Invalid request\"}";
    http_response_code(400);
    exit(400);
}
$json = json_decode(file_get_contents("php://input"), true);
if(!isset($json["name"]) || !isset($json["profile_n"]))
{
    echo "{\"status\":\"error\",\"message\":\"Invalid JSON\"}";
    http_response_code(400);
    exit(400);
}
$profile_n = $json["profile_n"];
$name = $json["name"];

require_once(__DIR__."/../web/includes/Data.php");
$data = Data::getInstance();
try
{
    $data->getProfile($profile_n)->setName($name);
    Data::save();
    echo "{\"status\":\"success\"}";
}
catch (InvalidArgumentException $exception)
{
    http_response_code(400);
    echo "{\"status\":\"error\",\"message\":\"$exception->getMessage()\"}";
}