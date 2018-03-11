<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2018-02-17
 * Time: 17:17
 */

if(!isset(apache_request_headers()["Authorization"]))
{
    http_response_code(401);
    exit(0);
}

preg_match("/Bearer (.*)/", apache_request_headers()["Authorization"], $match);

require_once __DIR__ . "/../smart/database/DbUtils.php";
require_once __DIR__ . "/../smart/database/OAuthUtils.php";

$user_id = OAuthUtils::getUserForToken(DbUtils::getConnection(), $match[1]);
if($user_id !== null)
{
    $json = json_decode(file_get_contents("php://input"), true);
    $request_id = $json["requestId"];
    $input = $json["inputs"][0];
    $intent = $input["intent"];

    switch($intent)
    {
        case "action.devices.SYNC":
        {
            $devices = file_get_contents(__DIR__ . "/devices.json");
            $devices = str_replace("\$request_id", $request_id, $devices);
            $devices = str_replace("\$user_id", $user_id, $devices);
            echo $devices;
            break;
        }
        case "action.devices.EXECUTE":
        {
            require_once __DIR__."/../web/includes/Data.php";
            require_once __DIR__."/../network/tcp.php";
            $data = Data::getInstance();
            foreach($input["payload"]["commands"] as $set)
            {
                foreach($set["execution"] as $command)
                {
                    switch($command["command"])
                    {
                        case "action.devices.commands.BrightnessAbsolute":
                        {
                            foreach($set["devices"] as $device)
                            {
                                $data->brightness_array[$device["id"]] = $command["params"]["brightness"];
                                Data::save();
                                tcp_send($data->globalsToJson());
                            }
                            break;
                        }
                        case "action.devices.commands.OnOff":
                            {
                                foreach($set["devices"] as $device)
                                {
                                    $data->brightness_array[$device["id"]] = $command["params"]["brightness"];
                                    Data::save();
                                    tcp_send($data->globalsToJson());
                                }
                                break;
                            }
                    }
                }
            }
        }
    }
}
else
{
    http_response_code(401);
}
