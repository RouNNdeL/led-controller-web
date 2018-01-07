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
if($json == false || !isset($json["devices"]) || !isset($json["profile_n"]))
{
    echo "{\"status\":\"error\",\"message\":\"Invalid JSON\"}";
    http_response_code(400);
    exit(400);
}

require_once(__DIR__ . "/../web/includes/Utils.php");
require_once(__DIR__ . "/../web/includes/Profile.php");
require_once(__DIR__ . "/../web/includes/Data.php");
require_once(__DIR__ . "/../web/includes/Device.php");
require_once(__DIR__ . "/../web/includes/DigitalDevice.php");
require_once(__DIR__ . "/../web/includes/AnalogDevice.php");
require_once(__DIR__ . "/../network/tcp.php");

$data = Data::getInstance();
$response = array();
$profile = $data->getProfile($json["profile_n"]);

if($profile === false)
{
    $response["status"] = "error";
    $response["message"] = "Invalid profile index";
    http_response_code(400);
}
else
{
    $response["status"] = "success";

    foreach($json["devices"] as $item)
    {
        if($item["device"]["type"] === "a")
        {
            $device = AnalogDevice::fromJson($item);
            $profile->analog_devices[$item["device"]["num"]] = $device;
        }
        else
        {
            $device = DigitalDevice::fromJson($item);
            $profile->digital_devices[$item["device"]["num"]] = $device;
        }
    }
    Data::save();
    $avr_index = $data->getAvrIndex($json["profile_n"]);
    $response["message"] = $avr_index !== false ? tcp_send($profile->toSend($avr_index)) ?
        Utils::getString("options_save_success") :
        Utils::getString("options_save_success_offline") : Utils::getString("options_save_success");
}

echo json_encode($response);