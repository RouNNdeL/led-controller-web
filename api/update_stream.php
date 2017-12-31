<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2017-12-31
 * Time: 13:21
 */

header('Cache-Control: no-cache');
header("Content-Type: text/event-stream\n\n");

require_once (__DIR__."/../web/includes/Data.php");

$filename = $_SERVER["DOCUMENT_ROOT"] . Data::UPDATE_PATH;
$sent = false;

while(1)
{
    if($sent)
    {
        unlink($filename);
        $sent = false;
    }
    if(file_exists($filename))
    {
        sleep(.5);
        echo "event: globals\n";
        echo "data: ".Data::getInstance(true)->globalsToJson()."\n\n";

        ob_end_flush();
        flush();
        $sent = true;
    }
    sleep(.5);
}