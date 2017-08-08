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
require_once(__DIR__."/../includes/Data.php");
require_once(__DIR__."/../includes/Navbar.php");

$profiles = Data::getInstance()->getProfiles();

$navbar = new Navbar();
$navbar->initDefault($profiles, Utils::getInstance()->getString("global_options"));
$navbar->setActive(0);
echo $navbar->toHtml();
?>
</body>
</html>