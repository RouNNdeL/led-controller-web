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
require_once(__DIR__."/../includes/Data.php");
require_once(__DIR__."/../includes/Navbar.php");

$n_profile = $_GET["n_profile"]-1;

if(Data::getInstance()->getProfileCount() <= $n_profile)
{
    include(__DIR__."/../error/404.php");
    exit(404);
}

$navbar = new Navbar();
$navbar->initDefault();
$navbar->setActive($n_profile+1);
echo $navbar->toHtml();

?>
</body>
</html>