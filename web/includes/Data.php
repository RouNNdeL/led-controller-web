<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 08/08/2017
 * Time: 00:41
 */

require_once(__DIR__ . "/Profile.php");
require_once(__DIR__ . "/../../api_ai/update_profile_entities.php");

class Data
{
    const SAVE_PATH = "/_data/data.dat";

    /**
     * @var Data
     */
    private static $instance;

    public $active_profile;
    public $enabled;

    private $brightness;
    private $fan_count;
    /**
     * @var Profile[]
     */
    private $profiles;

    /**
     * Data constructor.
     * @param $active_profile
     * @param $enabled
     * @param $brightness
     * @param $fan_count
     * @param $strip_configuration
     * @param array $profiles
     */
    private function __construct(int $active_profile, bool $enabled, int $brightness, int $fan_count, array $profiles)
    {
        $this->active_profile = $active_profile;
        $this->enabled = $enabled;
        $this->brightness = $brightness;
        $this->fan_count = $fan_count;
        $this->profiles = $profiles;
    }

    /**
     * @return int
     */
    public function getFanCount(): int
    {
        return $this->fan_count;
    }

    /**
     * @param int $fan_count
     */
    public function setFanCount(int $fan_count)
    {
        $this->fan_count = $fan_count;
    }

    public function getBrightness()
    {
        return ceil($this->brightness / 2.55);
    }

    public function setBrightness($percent)
    {
        $this->brightness = floor($percent * 2.55);
    }

    /**
     * @return int
     */
    public function getProfileCount()
    {
        return sizeof($this->profiles);
    }

    public function addProfile(Profile $profile)
    {
        if (sizeof($this->profiles) >= 12)
            return false;
        put($this->profiles[sizeof($this->profiles)-1]->getName());
        return array_push($this->profiles, $profile);
    }

    public function removeProfile(int $index)
    {
        if(sizeof($this->profiles) == 1)
            return false;
        if(isset($this->profiles[$index]))
        {
            delete($this->profiles[$index]->getName());
            array_splice($this->profiles, $index, 1);
            return true;
        }
        return false;
    }

    /**
     * @return Profile[]
     */
    public function getProfiles()
    {
        return $this->profiles;
    }

    public function getProfile($n)
    {
        return $this->profiles[$n];
    }

    private function _save()
    {
        $path = $_SERVER["DOCUMENT_ROOT"] . self::SAVE_PATH;
        $filename = dirname($path);
        if (!is_dir($filename))
        {
            mkdir(dirname($path));
        }
        file_put_contents($path, serialize($this));
    }

    /**
     * @return Data|bool
     */
    private static function fromFile()
    {
        $path = $_SERVER["DOCUMENT_ROOT"] . self::SAVE_PATH;
        $contents = file_get_contents($path);
        return $contents == false ? false : unserialize($contents);
    }

    public static function save()
    {
        self::$instance->_save();
    }

    /**
     * @return Data
     */
    private static function default()
    {
        $profiles = array();
        $name = Utils::getInstance()->getString("default_profile_name");
        $name = str_replace("\$n", 1, $name);
        $profile1 = new Profile($name);
        array_push($profiles, $profile1);

        $data = new Data(0, true, 255, 0, $profiles);
        $data->_save();
        return $data;
    }

    public static function getInstance()
    {
        if (self::$instance == null)
        {
            $data = self::fromFile();
            self::$instance = $data == false ? self::default() : $data;
        }

        return self::$instance;
    }

    public function getDeviceNavbarHtml($n_profile)
    {
        $html = "<ul id=\"device-navbar\" class=\"nav nav nav-pills nav-stacked\">";
        $pc = Utils::getString("profile_pc");
        $gpu = Utils::getString("profile_gpu");
        $fan = Utils::getString("profile_digital");

        $device_url = $n_profile."a0";
        $html .= "<li role=\"presentation\" class=\"active\"" .
            " data-device-url=\"$device_url\"><a>"
            . $pc . "</a></li>";

        $device_url = $n_profile."a1";
        $html .= "<li role=\"presentation\"" .
            " data-device-url=\"$device_url\"><a>"
            . $gpu . "</a></li>";

        if ($this->getFanCount() > 0)
            $html .= "<li role=\"separator\" class=\"nav-divider\"></li>";
        for ($i = 0; $i < $this->getFanCount(); $i++)
        {
            $device_url = $n_profile."d".$i;
            $html .= "<li role=\"presentation\"" .
                " data-device-url=\"$device_url\"><a>"
                . str_replace("\$n", $i + 1, $fan) . "</a></li>";
        }
        $html .= "</ul>";

        return $html;
    }
}