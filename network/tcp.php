<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2017-12-23
 * Time: 16:15
 */

function tcp_send($string)
{

    $interface = explode(":", file_get_contents(__DIR__."/interface.data"));
    $fp = fsockopen($interface[0], $interface[1], $errno, $errstr, 2);
    if (!$fp) {
        return false;
    } else {
        fwrite($fp, $string);
        fclose($fp);
        return true;
    }
}