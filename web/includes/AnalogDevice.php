<?php

/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 10/08/2017
 * Time: 15:47
 */
class AnalogDevice extends DeviceProfile
{
    const EFFECT_OFF = 100;
    const EFFECT_STATIC = 101;
    const EFFECT_BREATHING = 102;
    const EFFECT_BLINKING = 102;
    const EFFECT_FADING = 104;
    const EFFECT_RAINBOW = 105;
    const EFFECT_DEMO = 199;

    const AVR_BREATHE = 0x00;
    const AVR_FADE = 0x01;
    const AVR_RAINBOW = 0x03;

    public function getTimingsForEffect()
    {
        switch ($this->effect)
        {
            case self::EFFECT_OFF:
                return 0b000000;
            case self::EFFECT_STATIC:
                return 0b001001;
            case self::EFFECT_BREATHING:
                return 0b111101;
            case self::EFFECT_BLINKING:
                return 0b101001;
            case self::EFFECT_FADING:
                return 0b011001;
            case self::EFFECT_RAINBOW:
                return 0b010001;
            case self::EFFECT_DEMO:
                return 0b000000;
            default:
                return 0b000000;
        }
    }

    public static function _static()
    {
        return self::static (array("FFFFFF"), 8, 0);
    }

    public static function static (array $colors, int $on, int $offset)
    {
        return new self($colors, self::AVR_BREATHE, 0, 0, $on, 0, 0, $offset);
    }

    public static function _breathing()
    {
        return self::breathing(array("FF0000", "00FF00", "0000FF"), 4, 8, 4, 8, 0, 0, 255);
    }

    public static function breathing(array $colors, int $off, int $fadein, int $on, int $fadeout, int $offset,
                                     int $min_val, $max_value)
    {
        $args = [];
        $args[0] = 0;
        $args[1] = $min_val;
        $args[2] = $max_value;
        return new self($colors, self::AVR_BREATHE, $off, $fadein, $on, $fadeout, 0, $offset, $args);
    }

    public static function _fading()
    {
        return self::fading(array("FF0000", "00FF00", "0000FF"), 4, 8, 0);
    }

    public static function fading(array $colors, int $fade, int $on, int $offset)
    {
        return new self($colors, self::AVR_FADE, 0, 0, $on, $fade, 0, $offset);
    }

    public static function _blinking()
    {
        return self::blinking(array("FF0000", "00FF00", "0000FF"), 8, 8, 0);
    }

    public static function blinking(array $colors, int $off, int $on, int $offset)
    {
        return new self($colors, self::AVR_BREATHE, $off, 0, $on, 0, 0, $offset);
    }

    public static function effects()
    {
        $effects = array();

        $effects[self::EFFECT_OFF] = "effect_off";
        $effects[self::EFFECT_STATIC] = "effect_static";
        $effects[self::EFFECT_BREATHING] = "effect_breathing";
        $effects[self::EFFECT_BLINKING] = "effect_blinking";
        $effects[self::EFFECT_FADING] = "effect_fading";
        $effects[self::EFFECT_RAINBOW] = "effect_rainbow";
        $effects[self::EFFECT_DEMO] = "effect_demo";

        return $effects;
    }
}