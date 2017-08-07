<?php

/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 07/08/2017
 * Time: 18:42
 */
class DeviceProfile
{
    public $mode;
    public $dynamic_modes;
    public $color_count;

    public $time_off;
    public $time_fadein;
    public $time_on;
    public $time_fadeout;

    /**
     * @var array()
     */
    private $colors;

    /**
     * DeviceProfile constructor.
     */
    public function __construct()
    {

    }


    /**
     * @param string $color
     * @return bool|int - false if to many colors in the array, otherwise number of colors in the array
     */
    public function addColor(string $color)
    {
        if(sizeof($this->colors) >= 16)
            return false;
        return array_push($this->colors, $color);
    }

    public function removeColor(int $pos)
    {
        unset($this->colors[$pos]);
    }

    public function getColors()
    {
        return $this->colors;
    }

    public function setTimings(int $off, int $fadein, int $on, int $fadeout)
    {
        if($off > 255 || $off < 0 || $fadein > 255 || $fadein < 0 ||
            $on > 255 || $on < 0 || $fadeout > 255 || $fadeout < 0)
        {
            throw new InvalidArgumentException("Timings have to be in range 0-255");
        }

        $this->time_off = $off;
        $this->time_fadein = $fadein;
        $this->time_on = $on;
        $this->time_fadeout = $fadeout;
    }

    private static function getTiming(int $x)
    {
        if($x < 0 || $x > 255)
        {
            throw new InvalidArgumentException("x has to be an integer in range 0-255");
        }

        if($x <= 100)
        {
            return $x/8;
        }
        if($x <= 180)
        {
            return $x/2-30;
        }
        if($x <= 240)
        {
            return $x-120;
        }
        if($x == 255)
        {
            return 10*60;
        }
        return $x*30-7080;
    }

    public static function getTimings()
    {
        $a = array();
        for ($i = 0; $i < 256; $i++)
        {
            $a[$i] = self::getTiming($i);
        }
        return $a;
    }
}