<?php
/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 08/08/2017
 * Time: 00:41
 */

require_once(__DIR__."/Profile.php");

class Data
{
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

    public $active_profile;
    public $enabled;

    private $brightness;
    private $device_count;
    private $strip_configuration;
    /**
     * @var array(Profile)
     */
    private $profiles;

    function __construct()
    {
        $this->setStripConfiguration(self::GRB, self::GRB);
    }

    public function setStripConfiguration(int $strip1, int $strip2)
    {
        //($this->strip_configuration);
        if($strip1 == -1)
            $strip1 = $this->getStripConfiguration(1);
        if($strip2 == -1)
            $strip2 = $this->getStripConfiguration(2);
        $this->strip_configuration = ($strip1 << 3) | $strip2;
        //var_dump($this->strip_configuration);
    }

    public function getStripConfiguration(int $n_strip)
    {
        if($n_strip == 1)
        {
            return ($this->strip_configuration & self::MASK_STRIP1) >> 3;
        }
        else if($n_strip == 2)
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
        return $this->device_count & self::MASK_DIGITAL_COUNT;
    }

    public function setAnalogCount($count)
    {
        if($count < 0 || $count > 2)
            throw new InvalidArgumentException("Analog count has to be in range 0-2");
        $digital = $this->getDigitalCount();
        $this->device_count = ($digital << 2) | $count;
    }

    public function setDigitalCount($count)
    {
        if($count < 0 || $count > 3)
            throw new InvalidArgumentException("Digital count has to be in range 0-3");
        $analog = $this->getAnalogCount();
        $this->device_count = ($count << 2) | $analog;
    }

    public function getBrightness()
    {
        return ceil($this->brightness/2.55);
    }

    public function setBrightness($percent)
    {
        $this->brightness = floor($percent*2.55);
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
        if(sizeof($this->profiles) >= 12)
            return false;
        return array_push($this->profiles, $profile);
    }
}