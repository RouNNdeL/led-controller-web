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
<?php
$additional_css = array("profile.css");
$additional_js = array("profile.js");
require_once(__DIR__ . "/../includes/html_head.php");
?>
<body>

<?php
require_once(__DIR__ . "/../includes/Data.php");
require_once(__DIR__ . "/../includes/Navbar.php");

if(!isset($_GET["n_profile"]))
{
    http_response_code(500);
    include(__DIR__ . "/../error/500.php");
    exit(500);
}
$n_profile = $_GET["n_profile"];

if(Data::getInstance()->getProfile($n_profile) === false)
{
    http_response_code(404);
    include(__DIR__ . "/../error/404.php");
    exit(404);
}

$navbar = new Navbar();
$data = Data::getInstance();
$navbar->initDefault();
$navbar->setActive($data->getActiveIndex($n_profile)+1);
echo $navbar->toHtml();
$profile = $data->getProfile($n_profile);
?>
<input id="profile_n" type="hidden" value="<?php echo $n_profile ?>">
<input id="current_profile" type="hidden" value="<?php echo $data->current_profile ?>">
<div class="container-fluid">
    <div class="row profile-content">
        <?php
        require_once(__DIR__ . "/../../network/tcp.php");
        if(!tcp_send(null))
        {
            $warning = Utils::getString("warning");
            $message = Utils::getString("warning_device_offline");;
            echo <<< TAG
    <div class="col-md-12" style="margin-top: 12px">
        <div class="alert alert-danger">
            <strong>$warning</strong> $message
        </div>
    </div>
TAG;
        }
        $visible = $data->enabled ? "style=\"display: none\"" : "";
        $str_warning = Utils::getString("warning");
        $str_led_disabled = Utils::getString("warning_led_disabled");
        echo <<<TAG
        <div id="profile-warning-led-disabled" class="col-md-12" $visible>
            <div class="alert alert-warning">
                <strong>$str_warning</strong> $str_led_disabled
            </div>
        </div>
TAG;
        $visible = $data->current_profile === $n_profile ? "style=\"display: none\"" : "";
        $str_diff_profile = Utils::getString("warning_diff_profile_selected");
        preg_replace("\$n", $data->current_profile+1, $str_diff_profile);
        echo <<<TAG
        <div id="profile-warning-diff-profile" class="col-md-12" $visible>
            <div class="alert alert-warning">
                <strong>$str_warning</strong> $str_diff_profile
            </div>
        </div>
TAG;
?>
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

                    <h3 style="margin-top: 0"><?php echo Utils::getString("profile_devices") ?></h3>
                    <?php echo $data->getDeviceNavbarHtml($n_profile) ?>
                    <ul class="nav">
                        <li role="separator" class="nav-divider"></li>
                    </ul>
                    <?php
                    $profile_delete_explain = Utils::getString("profile_delete_explain");
                    ?>
                    <button id="btn-delete-profile" class="btn btn-danger btn-block
                        <?php if($data->getProfileCount() === 1) echo " disabled\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"$profile_delete_explain\""; else echo "\"" ?>>
                        <?php echo Utils::getString("profile_delete") ?>
                        </button>
                </div>
            </div>
        </div>
        <div class=" col-sm-8 col-md-9 col-lg-10
                    " id="device-settings">
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <?php $device = $n_profile . "a0" ?>
                            <iframe id="device-settings-iframe" frameborder="0"
                                    src="/device_settings/<?php echo $device ?>"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</body>
</html>