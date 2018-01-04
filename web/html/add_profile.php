<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 08/08/2017
 * Time: 19:56
 */
require_once(__DIR__."/../includes/Data.php");
require_once(__DIR__."/../includes/Utils.php");

$data = Data::getInstance();
$name = Utils::getInstance()->getString("default_profile_name");
$i = $data->getProfileCount();
$name = str_replace("\$n", $i+1, $name);
$overflow = $data->addProfile(new Profile($name));
if($overflow !== false)
{
    Data::save();
    header("Location: /profile/" . $i);
}
else
{
    include(__DIR__."/../error/404.php");
}