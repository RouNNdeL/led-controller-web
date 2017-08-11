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
if($json == false || !isset($json["analog_count"]) || !isset($json["color_config_1"]) ||
    !isset($json["color_config_2"]) || !isset($json["digital_count"]) || !isset($json["brightness"]))
{
    echo "{\"status\":\"error\",\"message\":\"Invalid JSON\"}";
    http_response_code(400);
    exit(400);
}

$enabled = isset($json["enabled"]);
$analog_count = $json["analog_count"];
$color_config_1 = $json["color_config_1"];
$color_config_2 = $json["color_config_2"];
$digital_count = $json["digital_count"];
$brightness = $json["brightness"];

require_once(__DIR__."/../web/includes/Data.php");
require_once(__DIR__."/../web/includes/Utils.php");
$data = Data::getInstance();
try
{
    $data->enabled = $enabled;
    $data->setDigitalCount($digital_count);
    $data->setAnalogCount($analog_count);
    $data->setColorConfiguration($color_config_1, $color_config_2);
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