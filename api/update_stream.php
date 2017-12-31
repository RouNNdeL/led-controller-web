<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2017-12-31
 * Time: 13:21
 */

header('Cache-Control: no-cache');
header("Content-Type: text/event-stream\n\n");

require_once(__DIR__ . "/../web/includes/Data.php");

$filename = $_SERVER["DOCUMENT_ROOT"] . Data::UPDATE_PATH;
$runs = 0;
echo "retry: 500\n\n";
flush();
ob_end_flush();
error_reporting(0);
while(1)
{
    if(file_exists($filename))
    {
        echo "event: globals\n";
        echo "data: " . file_get_contents($filename) . "\n\n";

        ob_end_flush();
        flush();
        usleep(400000);
        unlink($filename);
    }
    else if($runs % 10 == 0)
    {
        echo "event: globals\n";
        echo "data: " . Data::getInstance(true)->globalsToJson(true) . "\n\n";
        ob_end_flush();
        flush();
    }
    if($runs > 100)
    {
        die();
    }
    $runs++;
    usleep(200000);
}