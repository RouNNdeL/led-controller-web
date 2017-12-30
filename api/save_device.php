<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2017-12-30
 * Time: 11:05
 */

header("Content-Type: application/json");
if($_SERVER['REQUEST_METHOD'] !== 'POST')
{
    echo "{\"status\":\"error\",\"message\":\"Invalid request\"}";
    http_response_code(400);
    exit(400);
}
$json = json_decode(file_get_contents("php://input"), true);
if($json == false || !isset($json["times"]) || !isset($json["args"]) || !isset($json["effect"])
    || !isset($json["colors"])|| !isset($json["device"]) || !isset($json["device"]["type"])
    || ($json["device"]["type"] !== "a" && $json["device"]["type"] !== "d"))
{
    echo "{\"status\":\"error\",\"message\":\"Invalid JSON\"}";
    http_response_code(400);
    exit(400);
}

require_once(__DIR__ . "/../web/includes/Profile.php");
require_once(__DIR__ . "/../web/includes/Data.php");
require_once(__DIR__ . "/../web/includes/Device.php");
require_once(__DIR__ . "/../web/includes/DigitalDevice.php");
require_once(__DIR__ . "/../web/includes/AnalogDevice.php");

$data = Data::getInstance();
if($json["device"]["type"] === "a")
{
    $device = AnalogDevice::fromJson($json);
    $profile = $data->getProfile($json["device"]["profile"]);
    $profile->analog_devices[$json["device"]["num"]] = $device;
    Data::save();
}
else
{
    $device = DigitalDevice::fromJson($json);
    $profile = $data->getProfile($json["device"]["profile"]);
    $profile->digital_devices[$json["device"]["num"]] = $device;
    Data::save();
}