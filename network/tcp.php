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
    $fp = fsockopen($interface[0], $interface[1], $errno, $errstr, 30);
    if (!$fp) {
        //echo "$errstr ($errno)<br />\n";
    } else {
        fwrite($fp, $string);
        fclose($fp);
    }
}