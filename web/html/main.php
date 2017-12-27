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
<?php
    $additional_js = ["global.js"];
    require_once(__DIR__ . "/../includes/html_head.php");
?>
<body>

<?php
require_once(__DIR__ . "/../includes/Data.php");
require_once(__DIR__ . "/../includes/Navbar.php");

$profiles = Data::getInstance()->getProfiles();

$navbar = new Navbar();
$navbar->initDefault();
$navbar->setActive(0);
echo $navbar->toHtml();
?>

<div class="container-fluid">
    <form id="global-form">
        <br>
        <div class="checkbox">
            <label>
                <input name="enabled" type="checkbox" <?php if (Data::getInstance()->enabled) echo " checked" ?>> <?php echo Utils::getInstance()->getString("options_enabled") ?>
            </label>
        </div>
        <label>
            <?php echo Utils::getString("options_digital_count"); ?>
            <select class="form-control" name="fan_count">
                <?php
                for ($i = 0; $i < 4; $i++)
                {
                    if ($i == Data::getInstance()->getFanCount())
                    {
                        echo "<option value=\"$i\" selected>$i</option>";
                    }
                    else
                    {
                        echo "<option value=\"$i\">$i</option>";
                    }
                }
                ?>
            </select>
        </label>
        <?php

        ?>
        <br>
        <label>
            <?php echo Utils::getString("options_global_brightness") ?>
            <br>
            <input
                    type="text"
                    name="brightness"
                    data-provide="slider"
                    data-slider-ticks="[0,100]"
                    data-slider-ticks-labels=''
                    data-slider-min="0"
                    data-slider-max="100"
                    data-slider-step="1"
                    data-slider-value="<?php echo Data::getInstance()->getBrightness() ?>"
                    data-slider-tooltip="show"
            >
        </label></form>
    <br>
    <br>
    <button id="btn-save" class="btn btn-primary" disabled><?php echo Utils::getString("options_save") ?></button>
    <button id="btn-restore-defaults"
            class="btn btn-danger"><?php echo Utils::getString("options_reset_defaults") ?></button>
</div>
<div id="snackbar"></div>
</body>
</html>