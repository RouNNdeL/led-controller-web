<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 11/08/2017
 * Time: 15:02
 */
if($_SERVER['REQUEST_METHOD'] === 'POST')
{
    require_once(__DIR__ . "/../web/includes/Data.php");
    Data::getInstance()->enabled = true;
    Data::save();
    http_response_code(204);
}
else
{
    http_response_code(400);
}