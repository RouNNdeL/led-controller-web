<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 10/08/2017
 * Time: 15:16
 */
if (!isset($_GET["device_type"]) || !isset($_GET["device_n"]) || !isset($_GET["profile"]))
{
    http_response_code(500);
    include(__DIR__ . "/../error/500.php");
    exit(500);
}
$device_type = $_GET["device_type"];
$device_n = $_GET["device_n"];
$profile_n = $_GET["profile"];

if (($device_type != "d" && $device_type != "a"))
{
    http_response_code(500);
    include(__DIR__ . "/../error/500.php");
    exit(500);
}
require_once(__DIR__ . "/../includes/Utils.php");
require_once(__DIR__ . "/../includes/Data.php");

$profile = Data::getInstance()->getProfile($profile_n);
$device = ($device_type == "a" ? $profile->analog_devices : $profile->digital_devices)[$device_n];
$lang = Utils::getInstance()->lang;
echo <<<TAG
<!DOCTYPE html>
<html lang="$lang">
TAG;
$additional_css = ["device_settings.css", "color-picker.css"];
$additional_js = ["color-picker.js", "device_settings.js"];
require_once(__DIR__ . "/../includes/html_head.php");
?>
<body>
<div>
    <?php
    if ($device_type == "a")
    {
        if($device_n == 0)
            $title = Utils::getString("profile_settings_pc");
        else
            $title =  Utils::getString("profile_settings_gpu");
    }
    else
    {
        $title = Utils::getString("profile_settings_digital");
        $title = str_replace("\$n", $device_n+1, $title);
    }
    ?>

    <h2 class="device-title"><?php echo $title?></h2>
    <?php echo $device->toHTML() ?>

</div>
<button class="btn btn-primary" style="margin-top: 12px"><?php echo Utils::getString("profile_apply"); ?></button>
</body>
</html>
