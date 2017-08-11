<?php

/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 10/08/2017
 * Time: 15:48
 */
class DigitalDevice extends DeviceProfile
{
    const MODE_FILLING = 0b0011;
    const MODE_MARQUEE = 0b0100;
    const MODE_ROTATING = 0b0101;
    const MODE_SWEEP = 0b0110;
    const MODE_ANDROID_PB = 0b0111;
    const MODE_TWO_HALVES = 0b1000;
    const MODE_DOUBLE_FILL = 0b1001;
    const MODE_HIGHS = 0b1010;
    const MODE_SOURCES = 0b1011;
    const MODE_PIECES = 0b1100;

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

    const DIRECTION_CW = 0;
    const DIRECTION_CCW = 1;

    public $arguments;

    /**
     * DigitalDevice constructor.
     * @param array $colors
     * @param int $effect
     * @param int $off
     * @param int $fadein
     * @param int $on
     * @param int $fadeout
     * @param array $arguments
     */
    public function __construct(array $colors, int $effect, int $off, int $fadein, int $on, int $fadeout, array $arguments = array())
    {
        parent::__construct($colors, $effect, $off, $fadein, $on, $fadeout);
        $this->arguments = $arguments;
    }


    public function toHTML()
    {
        $html = "";
        $timings = $this->getTimingsForEffect();
        $timing_strings = self::TIMING_STRINGS;
        $profile_colors = Utils::getString("profile_colors");
        $profile_effect = Utils::getString("profile_effect");
        $profile_timing = Utils::getString("profile_timing");
        $profile_arguments = Utils::getString("profile_arguments");
        $profile_apply = Utils::getString("profile_apply");
        $profile_color_input = Utils::getString("profile_color_input");
        $profile_add_color = Utils::getString("profile_add_color");

        $colors_html = "";
        $arguments_html = "";
        $timing_html = "";
        $effects_html = "";

        for ($i = 0; $i < sizeof($this->getColors()); $i++)
        {
            $template = self::COLOR_TEMPLATE;
            $template = str_replace("\$active", $i == 0 ? "checked" : "", $template);
            $template = str_replace("\$label", "color-$i", $template);
            $template = str_replace("\$color", "#" . $this->getColors()[$i], $template);
            $colors_html .= $template;
        }

        foreach ($this->arguments as $name => $argument)
        {
            switch ($name)
            {
                case "direction":
                    $str_cw = Utils::getString("profile_direction_cw");
                    $str_ccw = Utils::getString("profile_direction_ccw");
                    $str_dir = Utils::getString("profile_arguments_direction");
                    $arguments_html .= "<label class=\"inline-form\">
                                            $str_dir
                                            <select class=\"form-control\">
                                                <option value=\"" . self::DIRECTION_CW . "\">$str_cw</option>
                                                <option value=\"" . self::DIRECTION_CCW . "\">$str_ccw</option>
                                            </select>
                                        </label>";
                    break;
                default:
                    $template = self::INPUT_TEMPLATE;
                    $template = str_replace("\$label", Utils::getString("profile_argument_$name"), $template);
                    $template = str_replace("\$name", $name, $template);
                    $template = str_replace("\$placeholder", "", $template);
                    $template = str_replace("\$value", $argument, $template);
                    $arguments_html .= $template;
            }
        }

        for ($i = 0; $i < 4; $i++)
        {
            if (($timings & (1 << $i)) > 0)
            {
                $template = self::INPUT_TEMPLATE;
                $template = str_replace("\$label", Utils::getString("profile_timing_$timing_strings[$i]"), $template);
                $template = str_replace("\$name", $timing_strings[$i], $template);
                $template = str_replace("\$placeholder", "1", $template);
                $template = str_replace("\$value", $this->timings[$i], $template);
                $timing_html .= $template;
            }
        }

        foreach (self::effects() as $id => $effect)
        {
            $string = Utils::getString("profile_" . $effect);
            $effects_html .= "<option value=\"$id\"" . ($id == $this->effect ? " selected" : "") . ">$string</option>";
        }

        $html .= "<div class=\"inline\">
        <label>
            $profile_effect
            <select class=\"form-control\">
                $effects_html
            </select>
        </label>
        <h3>$profile_colors</h3>
        $colors_html
        <button id=\"add-color-btn\" class=\"btn btn-primary color-swatch\">$profile_add_color</button>

    </div>";
        $html .= "<div id=\"picker-container\" class=\"inline\">
                        <div id=\"color-picker\"></div>
                        <div>
                            <label>
                                $profile_color_input
                                <input class=\"form-control\" id=\"color-input\">
                            </label>
                        </div>
                  </div>";
        if ($timings != 0)
            $html .= "<div><h3>$profile_timing</h3>$timing_html</div>";
        if (sizeof($this->arguments) > 0)
            $html .= "<div><h3>$profile_arguments</h3>$arguments_html</div>";

        $html .= "<button class=\"btn btn-primary\">$profile_apply</button>";
        return $html;
    }

    public function getTimingsForEffect()
    {
        switch ($this->effect)
        {
            case self::EFFECT_OFF:
                return 0b0000;
            case self::EFFECT_STATIC:
                return 0b0010;
            case self::EFFECT_BREATHING:
                return 0b1111;
            case self::EFFECT_BLINKING:
                return 0b1010;
            case self::EFFECT_FADING:
                return 0b0110;
            case self::EFFECT_RAINBOW:
                return 0b0100;
            case self::EFFECT_FILLING:
                return 0b1110;
            case self::EFFECT_MARQUEE:
                return 0b0010;
            case self::EFFECT_ROTATING:
                return 0b0110;
            case self::EFFECT_SWEEP:
                return 0b1111;
            case self::EFFECT_ANDROID_PB:
                return 0b1110;
            case self::EFFECT_TWO_HALVES:
                return 0b1110;
            case self::EFFECT_DOUBLE_FILL:
                return 0b1110;
            case self::EFFECT_HIGHS:
                return 0b0100;
            case self::EFFECT_SOURCES:
                return 0b0100;
            case self::EFFECT_PIECES:
                return 0b0110;
            case self::EFFECT_DEMO:
                return 0b0000;
            default:
                return 0b0000;
        }
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

    public static function _filling()
    {
        return self::filling(array("FF0000", "00FF00", "0000FF"),
            0, 4, 0, self::DIRECTION_CW, 0);
    }

    public static function filling(array $colors, int $off, int $fadein, int $on, bool $direction, int $start_led)
    {
        $args = array();
        $args["start_led"] = $start_led;
        $args["direction"] = $direction;
        return new self($colors, self::EFFECT_FILLING, $off, $fadein, $on, 0, $args);
    }

    public static function _marquee()
    {
        return self::marquee(array("FF0000", "00FF00", "0000FF"),
            0, 4, 16, 6);
    }

    public static function marquee(array $colors, int $off, int $fadein, int $on, int $led_count)
    {
        $args = array();
        $args["led_count"] = $led_count;
        return new self($colors, self::EFFECT_MARQUEE, $off, $fadein, $on, 0, $args);
    }

    public static final function effects()
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