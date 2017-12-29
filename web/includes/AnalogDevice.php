<?php

/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 10/08/2017
 * Time: 15:47
 */
class AnalogDevice extends Device
{
    const EFFECT_OFF = 100;
    const EFFECT_STATIC = 101;
    const EFFECT_BREATHING = 102;
    const EFFECT_BLINKING = 103;
    const EFFECT_FADING = 104;
    const EFFECT_RAINBOW = 105;
    const EFFECT_DEMO = 199;

    const AVR_BREATHE = 0x00;
    const AVR_FADE = 0x01;
    const AVR_RAINBOW = 0x03;

    public function getTimingsForEffect()
    {
        switch($this->effect)
        {
            case self::EFFECT_OFF:
                return 0b000000;
            case self::EFFECT_STATIC:
                return 0b000000;
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

    public static function _off()
    {
        return self::off();
    }

    public static function off()
    {
        return new self(array("000000"), self::EFFECT_OFF, 0, 0, 1, 0, 0, 0);
    }

    public static function _static()
    {
        return self::static (array("FFFFFF"), 1, 0);
    }

    public static function static(array $colors, int $on, int $offset)
    {
        return new self($colors, self::EFFECT_STATIC, 0, 0, $on, 0, 0, $offset);
    }

    public static function _breathing()
    {
        return self::breathing(array("FF0000", "00FF00", "0000FF"), 1, 2, 1, 2, 0, 0, 255);
    }

    public static function breathing(array $colors, int $off, int $fadein, int $on, int $fadeout, int $offset,
                                     int $min_val, $max_value)
    {
        $args = [];
        $args["breathe_min_val"] = $min_val;
        $args["breathe_max_val"] = $max_value;
        return new self($colors, self::EFFECT_BREATHING, $off, $fadein, $on, $fadeout, 0, $offset, $args);
    }

    public static function _fading()
    {
        return self::fading(array("FF0000", "00FF00", "0000FF"), 4, 8, 0);
    }

    public static function fading(array $colors, int $fade, int $on, int $offset)
    {
        return new self($colors, self::EFFECT_FADING, 0, 0, $on, $fade, 0, $offset);
    }

    public static function _blinking()
    {
        return self::blinking(array("FF0000", "00FF00", "0000FF"), 8, 8, 0);
    }

    public static function blinking(array $colors, int $off, int $on, int $offset)
    {
        return new self($colors, self::EFFECT_BLINKING, $off, 0, $on, 0, 0, $offset);
    }

    /**
     * @param int $effect
     * @return AnalogDevice
     */
    public static function defaultFromEffect(int $effect)
    {
        switch($effect)
        {
            case self::EFFECT_OFF:
                return self::_off();
            case self::EFFECT_STATIC:
                return self::_static();
            case self::EFFECT_BREATHING:
                return self::_breathing();
            case self::EFFECT_BLINKING:
                return self::_blinking();
            case self::EFFECT_FADING:
                return self::_fading();
            case self::EFFECT_RAINBOW:
                return null;
            default:
                throw new InvalidArgumentException("Unknown effect: ".$effect);
        }
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

        return $effects;
    }
}