<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 11/08/2017
 * Time: 13:24
 */

header("Content-Type: application/json");
if($_SERVER['REQUEST_METHOD'] !== 'POST')
{
    echo "{\"status\":\"error\",\"message\":\"Invalid request\"}";
    http_response_code(400);
    exit(400);
}
$json = json_decode(file_get_contents("php://input"), true);
if($json == false || !isset($json["fan_count"]) || !isset($json["brightness"]))
{
    echo "{\"status\":\"error\",\"message\":\"Invalid JSON\"}";
    http_response_code(400);
    exit(400);
}

$enabled = isset($json["enabled"]);
$fan_count = $json["fan_count"];
$brightness = $json["brightness"];

require_once(__DIR__."/../web/includes/Data.php");
require_once(__DIR__."/../web/includes/Utils.php");
$data = Data::getInstance();
try
{
    $data->enabled = $enabled;
    $data->setFanCount($fan_count);
    $data->setBrightness($brightness);
    Data::save();
    $success_msg = Utils::getString("options_save_success");
    echo "{\"status\":\"success\",\"message\":\"$success_msg\"}";
}
catch (InvalidArgumentException $exception)
{
    http_response_code(400);
    echo "{\"status\":\"error\",\"message\":\"$exception->getMessage()\"}";
}