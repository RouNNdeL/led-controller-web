<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2017-12-23
 * Time: 17:16
 */

require_once __DIR__."/../secure.php";

if(isset($_GET["token"]) && $_GET["token"] === $interface_token)
{
    $addr = $_SERVER["REMOTE_ADDR"];
    file_put_contents("interface.data", $addr . ":" . $_GET["port"]);
    echo "success";
}
else
{
    http_response_code(401);
    exit(401);
}