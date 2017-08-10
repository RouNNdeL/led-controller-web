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
$add = "";
if(isset($additional_css))
{
    foreach ($additional_css as $css)
    {
        $add.="<link rel=\"stylesheet\" href=\"/web/css/$css\"/>";
    }
}
if(isset($additional_js))
{
    foreach ($additional_js as $js)
    {
        $add.="<script src=\"/web/js/$js\"/></script>";
    }
}
echo <<<TAG
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>$title</title>
    <script src="/web/js/jquery-3.2.1.min.js"></script>
    <script src="/bootstrap/js/bootstrap.min.js"></script>
    <script src="/web/js/bootstrap-slider.min.js"></script>
    <link rel="stylesheet" href="/bootstrap/css/bootstrap.min.css"/>
    <link rel="stylesheet" href="/web/css/main.css"/>
    <link rel="stylesheet" href="/web/css/bootstrap-slider.min.css"/>
    $add
</head>
TAG;
