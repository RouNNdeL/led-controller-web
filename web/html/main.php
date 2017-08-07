<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 07/08/2017
 * Time: 16:42
 */
require_once(__DIR__ . "/../includes/Utils.php");
$lang = Utils::getInstance()->lang;
echo <<<TAG
<!DOCTYPE html>
<html lang="$lang">
TAG;
?>
<?php require_once(__DIR__ . "/../includes/html_head.php"); ?>
<body>
<ul class="nav nav-tabs">
    <li role="presentation" class="active"><a href="#">Main setup</a></li>
    <li role="presentation"><a href="#">Profile 1</a></li>
    <li role="presentation"><a href="#">Add profile&nbsp;&nbsp;<span class="glyphicon glyphicon-plus"
                                                                     aria-hidden="true"></span></a>
    </li>
</ul>
<?php
require_once(__DIR__."/../includes/DeviceProfile.php");
$p = new DeviceProfile();
?>
</body>
</html>