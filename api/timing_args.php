<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2017-12-29
 * Time: 22:59
 */

header("Content-Type: application/json");
if($_SERVER['REQUEST_METHOD'] !== 'POST')
{
    echo "{\"status\":\"error\",\"message\":\"Invalid request\"}";
    http_response_code(400);
    exit(400);
}
$json = json_decode(file_get_contents("php://input"), true);
if($json == false || !isset($json["type"]) || !isset($json["effect"]) || ($json["type"] !== "a" && $json["type"] !== "d"))
{
    echo "{\"status\":\"error\",\"message\":\"Invalid JSON\"}";
    http_response_code(400);
    exit(400);
}

$effect = $json["effect"];
$type = $json["type"];

$response = array();
$response["status"] = "success";

require_once (__DIR__."/../web/includes/Utils.php");
require_once (__DIR__."/../web/includes/Device.php");
if($type === "a")
{
    require_once (__DIR__."/../web/includes/AnalogDevice.php");
    require_once (__DIR__."/../web/includes/DigitalDevice.php");
    $device = AnalogDevice::defaultFromEffect($effect);
    $response["html"] = $device->timingArgHtml();
    switch($effect)
    {
        case AnalogDevice::EFFECT_OFF: $response["limit_colors"] = 0; break;
        case AnalogDevice::EFFECT_STATIC: $response["limit_colors"] = 1; break;
        default: $response["limit_colors"] = 16;
    }
}
else
{
    require_once (__DIR__."/../web/includes/DigitalDevice.php");
    $device = DigitalDevice::defaultFromEffect($effect);
    $response["html"] = $device->timingArgHtml();
    switch($effect)
    {
        case DigitalDevice::EFFECT_OFF: $response["limit_colors"] = 0; break;
        case DigitalDevice::EFFECT_STATIC: $response["limit_colors"] = 1; break;
        default: $response["limit_colors"] = 16;
    }
}

echo json_encode($response);