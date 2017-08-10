<?php

/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 07/08/2017
 * Time: 18:42
 */
abstract class DeviceProfile
{
    // This are the binary codes that will be send to the AVR
    const MODE_OFF = 0b0000;
    const MODE_NORMAL = 0b0001;
    const MODE_RAINBOW = 0b0010;
    const MODE_DEMO = 0b1111;

    // This are the values that we will use in PHP and JS
    const EFFECT_OFF = 100;
    const EFFECT_STATIC = 101;
    const EFFECT_BREATHING = 102;
    const EFFECT_BLINKING = 102;
    const EFFECT_FADING = 104;
    const EFFECT_RAINBOW = 105;
    const EFFECT_DEMO = 199;

    const TIMING_STRINGS = ["off", "fadein", "on", "fadeout"];

    const /** @noinspection CssInvalidPropertyValue */
        COLOR_TEMPLATE =
        "<div>
            <div class=\"color-swatch-container\">
                <div class=\"input-group color-swatch\">
                    <span class=\"input-group-addon\">
                        <input type=\"radio\" aria-label=\"\$label\" name=\"color\"\$active>
                    </span>
                    <div class=\"color-box\" style=\"background-color: \$color\"></div>
                </div>
            </div>
            <button class=\"btn btn-danger color-delete-btn\"><span class=\"glyphicon glyphicon-trash\"></span></button>
        </div>";

    const INPUT_TEMPLATE = "<div class=\"form-group inline inline-form\">
                                <label>
                                    \$label
                                    <input class=\"form-control\" type=\"text\" name=\"\$name\" 
                                           placeholder=\"\$placeholder\" value=\"\$value\">
                                </label>
                            </div>";

    public $effect;
    public $dynamic_modes;
    public $color_count;

    public $timings;

    /**
     * @var array()
     */
    private $colors;

    /**
     * DeviceProfile constructor.
     * @param array $colors
     * @param int $effect
     * @param int $off
     * @param int $fadein
     * @param int $on
     * @param int $fadeout
     */
    protected function __construct(array $colors, int $effect, int $off, int $fadein, int $on, int $fadeout)
    {
        $this->colors = $colors;
        $this->effect = $effect;
        $this->setTimings($off, $fadein, $on, $fadeout);
    }


    /**
     * @param string $color
     * @return bool|int - false if to many colors in the array, otherwise number of colors in the array
     */
    public function addColor(string $color)
    {
        if (sizeof($this->colors) >= 16)
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
        if ($off > 255 || $off < 0 || $fadein > 255 || $fadein < 0 ||
            $on > 255 || $on < 0 || $fadeout > 255 || $fadeout < 0
        )
        {
            throw new InvalidArgumentException("Timings have to be in range 0-255");
        }

        $this->timings[0] = $off;
        $this->timings[1] = $fadein;
        $this->timings[2] = $on;
        $this->timings[3] = $fadeout;
    }

    public function toBinary()
    {
        //TODO: Create a function that will represent this object in a binary form for the AVR's EEPROM
    }

    /**
     * @return string
     */
    public abstract function toHTML();

    private static function getTiming(int $x)
    {
        if ($x < 0 || $x > 255)
        {
            throw new InvalidArgumentException("x has to be an integer in range 0-255");
        }

        if ($x <= 100)
        {
            return $x / 8;
        }
        if ($x <= 180)
        {
            return $x / 2 - 30;
        }
        if ($x <= 240)
        {
            return $x - 120;
        }
        if ($x == 255)
        {
            return 10 * 60;
        }
        return $x * 30 - 7080;
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