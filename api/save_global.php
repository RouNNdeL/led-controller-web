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
if($json == false || !isset($json["fan_count"]) || !isset($json["brightness"])
    || !isset($json["current_profile"]) || !isset($json["auto_increment"]))
{
    echo "{\"status\":\"error\",\"message\":\"Invalid JSON\"}";
    http_response_code(400);
    exit(400);
}

$enabled = isset($json["enabled"]);
$fan_count = $json["fan_count"];
$brightness = $json["brightness"];
$current_profile = $json["current_profile"];
$auto_increment = $json["auto_increment"];

require_once(__DIR__."/../web/includes/Data.php");
require_once(__DIR__."/../web/includes/Utils.php");
require_once(__DIR__."/../network/tcp.php");

$data = Data::getInstance();
try
{
    $data->enabled = $enabled;
    $data->active_profile = $current_profile;
    $data->setFanCount($fan_count);
    $data->setBrightness($brightness);
    $auto_increment = $data->setAutoIncrement($auto_increment);

    Data::save();
    $success_msg = Utils::getString("options_save_success");
    echo "{\"status\":\"success\",\"message\":\"$success_msg\", \"auto_increment_val\": $auto_increment}";
}
catch (InvalidArgumentException $exception)
{
    http_response_code(400);
    $message = $exception->getMessage();
    echo "{\"status\":\"error\",\"message\":\"$message\"}";
}