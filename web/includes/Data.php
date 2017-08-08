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
    const BGR = 0b001;

    const MASK_STRIP1 = 0b111000;
    const MASK_STRIP2 = 0b000111;

    const MASK_DIGITAL_COUNT = 0b0011;
    const MASK_ANALOG_COUNT = 0b1100;

    /**
     * @var Data
     */
    private static $instance;

    public $active_profile;
    public $enabled;

    private $brightness;
    private $device_count;
    private $strip_configuration;
    /**
     * @var array(Profile)
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
        $this->strip_configuration = $strip_configuration;
        $this->profiles = $profiles;
    }


    public function setStripConfiguration(int $strip1, int $strip2)
    {
        //($this->strip_configuration);
        if ($strip1 == -1)
            $strip1 = $this->getStripConfiguration(1);
        if ($strip2 == -1)
            $strip2 = $this->getStripConfiguration(2);
        $this->strip_configuration = ($strip1 << 3) | $strip2;
        //var_dump($this->strip_configuration);
    }

    public function getStripConfiguration(int $n_strip)
    {
        if ($n_strip == 1)
        {
            return ($this->strip_configuration & self::MASK_STRIP1) >> 3;
        }
        else if ($n_strip == 2)
        {
            return $this->strip_configuration & self::MASK_STRIP2;
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
}