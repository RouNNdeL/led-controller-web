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
    const UPDATE_PATH = "/_data/update.dat";
    const MAX_ACTIVE_COUNT = 8;
    const MAX_OVERALL_COUNT = 32;

    /**
     * @var Data
     */
    private static $instance;

    public $current_profile;
    public $enabled;

    private $brightness;
    private $fan_count;
    private $auto_increment;

    /** @var int[] */
    private $active_indexes;
    /** @var int[] */
    private $inactive_indexes;
    /** @var int[] */
    private $avr_indexes;

    /** @var Profile[] */
    private $profiles;

    /**
     * Data constructor.
     * @param int $current_profile
     * @param bool $enabled
     * @param int $brightness
     * @param int $fan_count
     * @param int $auto_increment
     * @param array $profiles
     */
    private function __construct(int $current_profile, bool $enabled, int $brightness, int $fan_count,
                                 int $auto_increment, array $profiles)
    {
        $this->current_profile = $current_profile;
        $this->enabled = $enabled;
        $this->brightness = $brightness;
        $this->fan_count = $fan_count;
        $this->auto_increment = $auto_increment;
        $this->profiles = $profiles;
        if(sizeof($profiles) <= self::MAX_ACTIVE_COUNT)
        {
            $this->active_indexes = range(0, sizeof($profiles) - 1);
            $this->inactive_indexes = array();
        }
        else
        {
            $this->active_indexes = range(0, self::MAX_ACTIVE_COUNT - 1);
            $this->inactive_indexes = range(self::MAX_ACTIVE_COUNT, sizeof($profiles) - self::MAX_ACTIVE_COUNT - 1);
        }
        $this->avr_indexes = $this->active_indexes;
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
        $this->brightness = ceil($percent * 2.55);
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
        if(sizeof($this->profiles) >= self::MAX_OVERALL_COUNT)
            return false;
        array_push($this->profiles, $profile);
        if(sizeof($this->active_indexes) < self::MAX_ACTIVE_COUNT)
        {
            array_push($this->active_indexes, max(array_keys($this->profiles)));
            for($i = 0; $i < self::MAX_ACTIVE_COUNT; $i++)
            {
                if(!isset($this->avr_indexes[$i]))
                {
                    $this->avr_indexes[$i] = max(array_keys($this->profiles));
                    break;
                }
            }
            return true;
        }
        array_push($this->inactive_indexes, max(array_keys($this->profiles)));
        return true;
    }

    public function removeProfile(int $index)
    {
        if(sizeof($this->profiles) == 1)
            return false;
        if(isset($this->profiles[$index]))
        {
            delete($this->profiles[$index]->getName());
            unset($this->profiles[$index]);
            if(($key = array_search($index, $this->active_indexes)) !== false)
            {
                array_splice($this->active_indexes, $index, 1);
                unset($this->avr_indexes[$key]);
            }
            if(($key = array_search($index, $this->inactive_indexes)) !== false)
            {
                array_splice($this->inactive_indexes, $index, 1);
            }
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

    /**
     * @return Profile[]
     */
    public function getActiveProfilesInOrder()
    {
        $arr = array();
        foreach($this->active_indexes as $index)
        {
            $arr[$index] = $this->profiles[$index];
        }
        return $arr;
    }

    /**
     * @return Profile[]
     */
    public function getInactiveProfilesInOrder()
    {
        $arr = array();
        foreach($this->inactive_indexes as $index)
        {
            $arr[$index] = $this->profiles[$index];
        }
        return $arr;
    }

    public function getActiveIndex($n)
    {
        return array_search($n,array_keys($this->profiles));
    }

    public function getHighlightIndex()
    {
        return array_search($this->avr_indexes[$this->current_profile],array_keys($this->profiles));
    }

    public function getProfile($n)
    {
        return isset($this->profiles[$n]) ? $this->profiles[$n] : false;
    }

    /**
     * @return mixed
     */
    public function getAutoIncrement()
    {
        return Device::getTiming($this->auto_increment);
    }

    /**
     * @param $value
     * @return float|int
     */
    public function setAutoIncrement($value)
    {
        $timing = Device::convertToTiming($value);
        $this->auto_increment = $timing;
        return Device::getTiming($timing);
    }

    public function globalsToJson($raw = false)
    {
        $array = array();

        $array["brightness"] = $raw ? $this->getBrightness() : $this->brightness;
        $array["profile_count"] = sizeof($this->profiles);
        $array["current_profile"] = $this->current_profile;
        $array["leds_enabled"] = $this->enabled;
        $array["fan_count"] = $this->fan_count;
        $array["auto_increment"] = $raw ? Device::getTiming($this->auto_increment) : $this->auto_increment;
        $array["fan_config"] = array(2, 0, 0);
        $array["profile_order"] = array(0, 1, 2, 3, 4, 5, 6, 7);

        return json_encode(array("type" => "globals_update", "data" => $array));
    }

    public function globalsFromJson($json)
    {
        $array = json_decode($json);

        $this->current_profile = $array["current_profile"];
        $this->enabled = $array["leds_enabled"];
    }

    private function _save()
    {
        $path = $_SERVER["DOCUMENT_ROOT"] . self::SAVE_PATH;
        $path_update = $_SERVER["DOCUMENT_ROOT"] . self::UPDATE_PATH;
        $dirname = dirname($path);
        if(!is_dir($dirname))
        {
            mkdir($dirname);
        }
        file_put_contents($path_update, $this->globalsToJson(true));
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

        $data = new Data(0, true, 255, 0, 0, $profiles);
        $data->_save();
        return $data;
    }

    public static function getInstance(bool $update = false)
    {
        if(self::$instance == null || $update)
        {
            $data = self::fromFile();
            self::$instance = $data == false ? self::default() : $data;
        }

        return self::$instance;
    }

    public function getDeviceNavbarHtml($n_profile)
    {
        $html = "";
        $pc = Utils::getString("profile_pc");
        $gpu = Utils::getString("profile_gpu");
        $fan = Utils::getString("profile_digital");

        $device_url = $n_profile . "a0";
        $html .= "<li role=\"presentation\" class=\"nav-item flex-fill\"" .
            "><a data-device-url=\"$device_url\" class=\"nav-link active\">"
            . $pc . "</a></li>";

        $device_url = $n_profile . "a1";
        $html .= "<li class=\"nav-item\" role=\"presentation\"" .
            "><a data-device-url=\"$device_url\" class=\"nav-link\">"
            . $gpu . "</a></li>";

        if($this->getFanCount() > 0)
            $html .= "<div class=\"dropdown-divider\"></div>";
        for($i = 0; $i < $this->getFanCount(); $i++)
        {
            $device_url = $n_profile . "d" . $i;
            $html .= "<li  class=\"nav-item\" role=\"presentation\"" .
                "><a data-device-url=\"$device_url\" class=\"nav-link\">"
                . str_replace("\$n", $i + 1, $fan) . "</a></li>";
        }

        return $html;
    }
}