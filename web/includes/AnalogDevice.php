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
        $html = "";
        $timings = $this->getTimingsForEffect();
        $timing_strings = self::TIMING_STRINGS;
        $profile_colors = Utils::getString("profile_colors");
        $profile_effect = Utils::getString("profile_effect");
        $profile_timing = Utils::getString("profile_timing");
        $profile_apply = Utils::getString("profile_apply");
        $profile_color_input = Utils::getString("profile_color_input");
        $profile_add_color = Utils::getString("profile_add_color");

        $colors_html = "";
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

    public static final function effects()
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