<?php

/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 10/08/2017
 * Time: 15:48
 */
class DigitalDevice extends Device
{
    const EFFECT_OFF = 100;
    const EFFECT_STATIC = 101;
    const EFFECT_BREATHING = 102;
    const EFFECT_BLINKING = 103;
    const EFFECT_FADING = 104;
    const EFFECT_RAINBOW = 105;
    const EFFECT_FILLING = 106;
    const EFFECT_MARQUEE = 107;
    const EFFECT_ROTATING = 108;
    const EFFECT_SWEEP = 109;
    const EFFECT_ANDROID_PB = 110;
    const EFFECT_TWO_HALVES = 111;
    const EFFECT_DOUBLE_FILL = 112;
    const EFFECT_HIGHS = 113;
    const EFFECT_SOURCES = 114;
    const EFFECT_PIECES = 115;
    const EFFECT_DEMO = 199;

    const DIRECTION_CW = 0;
    const DIRECTION_CCW = 1;

    /**
     * DigitalDevice constructor.
     * @param array $colors
     * @param int $effect
     * @param int $off
     * @param int $fadein
     * @param int $on
     * @param int $fadeout
     * @param int $rotating
     * @param int $offset
     * @param array $args
     */
    public function __construct(array $colors, int $effect, float $off, float $fadein, float $on, float $fadeout,
                                float $rotating, float $offset, array $args = array())
    {
        parent::__construct($colors, $effect, $off, $fadein, $on, $fadeout, $rotating, $offset, $args);
    }

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
                return 0b001101;
            case self::EFFECT_RAINBOW:
                return 0b010001;
            case self::EFFECT_FILLING:
                return 0b111111;
            case self::EFFECT_MARQUEE:
                return 0b001111;
            case self::EFFECT_ROTATING:
                return 0b0110;
            case self::EFFECT_SWEEP:
                return 0b111111;
            case self::EFFECT_ANDROID_PB:
                return 0b111001;
            case self::EFFECT_TWO_HALVES:
                return 0b111001;
            case self::EFFECT_DOUBLE_FILL:
                return 0b111001;
            case self::EFFECT_HIGHS:
                return 0b010001;
            case self::EFFECT_SOURCES:
                return 0b010001;
            case self::EFFECT_PIECES:
                return 0b011001;
            case self::EFFECT_DEMO:
                return 0b000001;
            default:
                return 0b0000;
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
        return self::static (array("FFFFFF"));
    }

    public static function static(array $colors)
    {
        return new self($colors, self::EFFECT_STATIC, 0, 0, 1, 0, 0, 0);
    }

    public static function _breathing()
    {
        return self::breathing(array("FF0000", "00FF00", "0000FF"), 1, .5, 1, .5, 0, 0, 255, 1);
    }

    public static function breathing(array $colors, float $off, float $fadein, float $on, float $fadeout, float $offset,
                                     int $min_val, int $max_value, int $color_cycles)
    {
        $args = [];
        $args["breathe_min_val"] = $min_val;
        $args["breathe_max_val"] = $max_value;
        $args["color_cycles"] = $color_cycles;
        return new self($colors, self::EFFECT_BREATHING, $off, $fadein, $on, $fadeout, 0, $offset, $args);
    }

    public static function _fading()
    {
        return self::fading(array("FF0000", "00FF00", "0000FF"), 0.5, 1, 0, 1);
    }

    public static function fading(array $colors, float $fade, float $on, float $offset,  int $color_cycles)
    {
        $args = array("color_cycles" => $color_cycles);
        return new self($colors, self::EFFECT_FADING, 0, 0, $on, $fade, 0, $offset, $args);
    }

    public static function _blinking()
    {
        return self::blinking(array("FF0000", "00FF00", "0000FF"), 1, 1, 0, 1);
    }

    public static function blinking(array $colors, float $off, float $on, float $offset, int $color_cycles)
    {
        $args = array("color_cycles" => $color_cycles);
        return new self($colors, self::EFFECT_BLINKING, $off, 0, $on, 0, 0, $offset, $args);
    }

    public static function _filling()
    {
        return self::filling(array("FF0000", "00FF00", "0000FF"), 0, 1, 1, 0, 0,
            self::DIRECTION_CW, 1, 1, 1, 1);
    }

    public static function filling(array $colors, float $off, float $fadein, float $on, float $fadeout, float $rotating,
                                   float $offset, bool $direction, bool $smooth, int $piece_count, int $color_count,
                                   int $color_cycles)
    {
        $args = array();
        $args["direction"] = $direction;
        $args["smooth"] = $smooth;
        $args["fill_fade_piece_count"] = $piece_count;
        $args["fill_fade_color_count"] = $color_count;
        $args["color_cycles"] = $color_cycles;
        return new self($colors, self::EFFECT_FILLING, $off, $fadein, $on, $fadeout, $rotating, $offset, $args);
    }

    public static function _marquee()
    {
        return self::marquee(array("FF0000", "00FF00", "0000FF"),
            1, 5, 2, 0, 1);
    }

    public static function marquee(array $colors, int $fade, int $on, int $rotating, int $offset, int $color_cycles)
    {
        $args = array("color_cycles" => $color_cycles);
        return new self($colors, self::EFFECT_MARQUEE, 0, 0, $on, $fade, $rotating, $offset, $args);
    }

    /**
     * @param int $effect
     * @return DigitalDevice
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
            case self::EFFECT_FILLING:
                return self::_filling();
            case self::EFFECT_MARQUEE:
                return self::_marquee();
            case self::EFFECT_ROTATING:
                return null;
            default:
                throw new InvalidArgumentException("Unknown effect: " . $effect);
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
        $effects[self::EFFECT_FILLING] = "effect_filling";
        $effects[self::EFFECT_MARQUEE] = "effect_marquee";
        $effects[self::EFFECT_ROTATING] = "effect_rotating";
        $effects[self::EFFECT_SWEEP] = "effect_sweep";
        $effects[self::EFFECT_ANDROID_PB] = "effect_android_pb";
        $effects[self::EFFECT_TWO_HALVES] = "effect_two_halves";
        $effects[self::EFFECT_DOUBLE_FILL] = "effect_double_fill";
        $effects[self::EFFECT_HIGHS] = "effect_highs";
        $effects[self::EFFECT_SOURCES] = "effect_sources";
        $effects[self::EFFECT_PIECES] = "effect_pieces";
        $effects[self::EFFECT_DEMO] = "effect_demo";

        return $effects;
    }
}