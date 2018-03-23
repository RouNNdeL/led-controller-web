<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 2017-12-23
 * Time: 17:16
 */

require_once __DIR__ . "/../secure.php";
require_once __DIR__ . "/../web/includes/Data.php";

$data = Data::getInstance();
var_dump($data->getNewProfiles());