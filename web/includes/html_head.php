<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 07/08/2017
 * Time: 16:43
 */
require_once (__DIR__."/Utils.php");
$utils = Utils::getInstance();
$title = $utils->getString("title");
echo <<<TAG
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>$title</title>
    <script src="/web/js/jquery-3.2.1.min.js"></script>
    <script src="/web/js/color-picker.min.js"></script>
    <script src="/bootstrap/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="/bootstrap/css/bootstrap.min.css"/>
</head>
TAG;
