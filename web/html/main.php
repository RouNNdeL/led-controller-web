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
    <br>
    <div class="checkbox">
        <label>
            <input type="checkbox" <?php if(Data::getInstance()->enabled) echo " checked"?>> <?php echo Utils::getInstance()->getString("options_enabled")?>
        </label>
    </div>
    <label>
        <?php echo Utils::getString("options_analog_count");?>
        <select class="form-control" name="analog_count">
            <?php
            for($i = 0; $i < 3; $i++)
            {
                if($i == Data::getInstance()->getAnalogCount())
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
    <label class="inline-select<?php if(Data::getInstance()->getAnalogCount() < 1) echo " hidden"?>">
        <?php str_replace("\$port", "ANALOG1", Utils::getString("options_color_config"))?>
        <select class="form-control" name="color_config_1">
            <option value="0">RGB</option>
            <option value="1">RBG</option>
            <option value="2">GRB</option>
            <option value="3">GBR</option>
            <option value="4">BRG</option>
            <option value="5">BGR</option>
        </select>
    </label>
    <label class="inline-select<?php if(Data::getInstance()->getAnalogCount() < 2) echo " hidden"?>">
        <?php str_replace("\$port", "ANALOG2", Utils::getString("options_color_config"))?>
        <select class="form-control" name="color_config_2">
            <option value="0">RGB</option>
            <option value="1">RBG</option>
            <option value="2">GRB</option>
            <option value="3">GBR</option>
            <option value="4">BRG</option>
            <option value="5">BGR</option>
        </select>
    </label>
    <br>
    <br>
    <label>
        <?php echo Utils::getString("options_digital_count");?>
        <select class="form-control" name="digital_count">
            <?php
            for($i = 0; $i < 4; $i++)
            {
                if($i == Data::getInstance()->getDigitalCount())
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
    <br>
    <br>
    <label>
        <?php echo Utils::getString("options_global_brightness")?>
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
                data-slider-value="<?php echo Data::getInstance()->getBrightness()?>"
                data-slider-tooltip="show"
        >
    </label>
    <br>
    <br>
    <button class="btn btn-primary"><?php echo Utils::getString("options_save")?></button>
    <button class="btn btn-danger"><?php echo Utils::getString("options_reset_defaults")?></button>
</div>
</body>
</html>