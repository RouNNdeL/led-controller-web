<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2017-12-23
 * Time: 16:15
 */

error_reporting(0);
function tcp_send($string)
{

    $interface = explode(":", file_get_contents(__DIR__ . "/interface.data"));
    $fp = fsockopen($interface[0], $interface[1], $errno, $errstr, 0.1);
    if(!$fp)
    {
        return false;
    }
    else
    {
        if($string !== null) fwrite($fp, $string);
        fclose($fp);
        return true;
    }
}