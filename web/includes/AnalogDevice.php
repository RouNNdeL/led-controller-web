<?php

/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 10/08/2017
 * Time: 15:47
 */
class AnalogDevice extends DeviceProfile
{
    public function toHTML()
    {
        // TODO: Implement toHTML() method.
    }

    public static function _static()
    {
        return self::static (array("FFFFFF"), 8);
    }

    public static function static (array $colors, int $on)
    {
        return new self($colors, self::EFFECT_STATIC, 0, 0, $on, 0);
    }

    public static function _breathing()
    {
        return self::breathing(array("FF0000", "00FF00", "0000FF"), 4, 8, 4, 8);
    }

    public static function breathing(array $colors, int $off, int $fadein, int $on, int $fadeout)
    {
        return new self($colors, self::EFFECT_BREATHING, $off, $fadein, $on, $fadeout);
    }

    public static function _fading()
    {
        return self::fading(array("FF0000", "00FF00", "0000FF"), 4, 8);
    }

    public static function fading(array $colors, int $fade, int $on)
    {
        return new self($colors, self::EFFECT_FADING, 0, $fade, $on, $fade);
    }

    public static function _blinking()
    {
        return self::blinking(array("FF0000", "00FF00", "0000FF"), 8, 8);
    }

    public static function blinking(array $colors, int $off, int $on)
    {
        return new self($colors, self::EFFECT_BLINKING, $off, 0, $on, 0);
    }
}