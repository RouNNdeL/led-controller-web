<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 08/08/2017
 * Time: 00:41
 */

require_once(__DIR__ . "/Profile.php");

class Data
{
    const SAVE_PATH = "/_data/data.dat";

    const RGB = 0b000;
    const RBG = 0b001;
    const GRB = 0b010;
    const GBR = 0b011;
    const BRG = 0b100;
    const BGR = 0b101;

    const MASK_ANALOG1 = 0b111000;
    const MASK_ANALOG2 = 0b000111;

    const MASK_DIGITAL_COUNT = 0b1100;
    const MASK_ANALOG_COUNT = 0b0011;

    /**
     * @var Data
     */
    private static $instance;

    public $active_profile;
    public $enabled;

    private $brightness;
    private $device_count;
    private $color_configuration;
    /**
     * @var Profile[]
     */
    private $profiles;

    /**
     * Data constructor.
     * @param $active_profile
     * @param $enabled
     * @param $brightness
     * @param $device_count
     * @param $strip_configuration
     * @param array $profiles
     */
    private function __construct(int $active_profile, bool $enabled, int $brightness, int $device_count,
                                 int $strip_configuration, array $profiles)
    {
        $this->active_profile = $active_profile;
        $this->enabled = $enabled;
        $this->brightness = $brightness;
        $this->device_count = $device_count;
        $this->color_configuration = $strip_configuration;
        $this->profiles = $profiles;
    }


    public function setColorConfiguration(int $analog1, int $analog2)
    {
        if(($analog1 < 0 && $analog1 !== -1) || $analog1 > 5 ||
            ($analog2 < 0 && $analog2 !== -1) || $analog2 > 5)
        {
            throw new InvalidArgumentException("Color configuration values need to be in range 0-5 or -1, when not updated");
        }
        if ($analog1 == -1)
            $analog1 = $this->getColorConfiguration(1);
        if ($analog2 == -1)
            $analog2 = $this->getColorConfiguration(2);
        $this->color_configuration = ($analog1 << 3) | $analog2;
    }

    public function getColorConfiguration(int $n_strip)
    {
        if ($n_strip == 1)
        {
            return ($this->color_configuration & self::MASK_ANALOG1) >> 3;
        }
        else if ($n_strip == 2)
        {
            return $this->color_configuration & self::MASK_ANALOG2;
        }
        throw new InvalidArgumentException("n_strip has to be either 1 or 2");
    }

    public function getAnalogCount()
    {
        return $this->device_count & self::MASK_ANALOG_COUNT;
    }

    public function getDigitalCount()
    {
        return ($this->device_count & self::MASK_DIGITAL_COUNT) >> 2;
    }

    public function setAnalogCount($count)
    {
        if ($count < 0 || $count > 2)
            throw new InvalidArgumentException("Analog count has to be in range 0-2");
        $digital = $this->getDigitalCount();
        $this->device_count = ($digital << 2) | $count;
    }

    public function setDigitalCount($count)
    {
        if ($count < 0 || $count > 3)
            throw new InvalidArgumentException("Digital count has to be in range 0-3");
        $analog = $this->getAnalogCount();
        $this->device_count = ($count << 2) | $analog;
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
        return array_push($this->profiles, $profile);
    }

    public function removeProfile(int $index)
    {
        if(isset($this->profiles[$index]))
        {
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

        $data = new Data(0, true, 255, 0,
            self::RGB << 3 | self::RGB, $profiles);
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
        $strip = Utils::getString("profile_analog");
        $fan = Utils::getString("profile_digital");
        for ($i = 0; $i < $this->getAnalogCount(); $i++)
        {
            $device_url = $n_profile."a".$i;
            $html .= "<li role=\"presentation\""
                .($i == 0 ? " class=\"active\"" : "")
                ." data-device-url=\"$device_url\"><a>"
                . str_replace("\$n", $i + 1, $strip) . "</a></li>";
        }
        if ($this->getDigitalCount() > 0 && $this->getAnalogCount() > 0)
            $html .= "<li role=\"separator\" class=\"nav-divider\"></li>";
        for ($i = 0; $i < $this->getDigitalCount(); $i++)
        {
            $device_url = $n_profile."d".$i;
            $html .= "<li role=\"presentation\""
                .($i == 0 && $this->getAnalogCount() == 0 ? " class=\"active\"" : "").
                " data-device-url=\"$device_url\"><a>"
                . str_replace("\$n", $i + 1, $fan) . "</a></li>";
        }
        $html .= "</ul>";

        return $html;
    }
}