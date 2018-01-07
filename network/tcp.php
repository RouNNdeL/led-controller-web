<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2017-12-23
 * Time: 16:15
 */

function tcp_send($string)
{
    error_reporting(0);
    $filename = __DIR__ . "/interface.data";
    if(!file_exists($filename))
    {
        return false;
    }
    $interface = explode(":", file_get_contents($filename));
    $fp = fsockopen($interface[0], $interface[1], $errno, $errstr, 0.1);
    error_reporting(E_ALL);
    if(!$fp)
    {
        unlink($filename);
        return false;
    }
    else
    {
        if($string !== null) fwrite($fp, $string);
        fclose($fp);
        return true;
    }
}