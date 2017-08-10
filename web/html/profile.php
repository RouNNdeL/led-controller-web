<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 08/08/2017
 * Time: 18:54
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

$n_profile = $_GET["n_profile"] - 1;

if (Data::getInstance()->getProfileCount() <= $n_profile)
{
    include(__DIR__ . "/../error/404.php");
    exit(404);
}

$navbar = new Navbar();
$navbar->initDefault();
$navbar->setActive($n_profile + 1);
echo $navbar->toHtml();
$data = Data::getInstance();
$profile = $data->getProfile($n_profile);
$data->setAnalogCount(2);
$data->setDigitalCount(1);
?>
<div class="container-fluid">
    <div class="row profile-content">
        <div class="col-sm-4 col-md-3 col-lg-2">
            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="form-group">
                        <label><?php echo Utils::getString("profile_name") ?>
                            <?php
                            $name_placeholder = Utils::getInstance()->getString("default_profile_name");
                            $name_placeholder = str_replace("\$n", $n_profile + 1, $name_placeholder);
                            $name = $profile->getName();
                            ?>
                            <input type="text" class="form-control" id="profile-name" value="<?php echo $name ?>"
                                   placeholder="<?php echo $name_placeholder ?>" name="profile_name">
                        </label>
                    </div>

                    <h3 style="margin-top: 0"><?php echo Utils::getString("profile_devices")?></h3>
                    <?php echo $data->getDeviceNavbarHtml() ?>
                </div>
            </div>
        </div>
        <div class="col-sm-8 col-md-9 col-lg-10">
            <div class="panel panel-default">
                <div class="panel-body">
                    <!-- TODO: Add device profile settings -->
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>