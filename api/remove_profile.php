<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 11/08/2017
 * Time: 16:14
 */
header("Content-Type: application/json");
if($_SERVER['REQUEST_METHOD'] !== 'GET')
{
    echo "{\"status\":\"error\",\"message\":\"Invalid request\"}";
    http_response_code(400);
    exit(400);
}
if(!isset($_GET["profile_n"]))
{
    http_response_code(404);
    include(__DIR__."/../web/error/404.php");
    exit(404);
}
$profile_n = $_GET["profile_n"];
require_once(__DIR__."/../web/includes/Data.php");
Data::getInstance()->removeProfile($profile_n-1);
Data::save();
header("Location: /main");