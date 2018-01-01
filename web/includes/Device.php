<?php

/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 07/08/2017
 * Time: 18:42
 */
abstract class Device
{
    const TIMING_STRINGS = ["off", "fadein", "on", "fadeout", "rotation", "offset"];

    const AVR_EFFECT_BREATHE = 0x00;
    const AVR_EFFECT_FADE = 0x01;
    const AVR_EFFECT_FILLING_FADE = 0x02;
    const AVR_EFFECT_RAINBOW = 0x03;
    const AVR_EFFECT_FILL = 0x04;
    const AVR_EFFECT_ROTATING = 0x05;
    const AVR_EFFECT_PIECES = 0x0C;

    const /** @noinspection CssInvalidPropertyValue */
        COLOR_TEMPLATE =
        "<div class=\"color-container\">
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

    const HIDDEN_TEMPLATE = "<input type=\"hidden\" name=\"\$name\" value=\"\$value\">";

    public $effect;
    public $timings;
    public $args;

    /**
     * @var array()
     */
    private $colors;

    /**
     * Device constructor. <b>Note:</b> Timings are interpreted as raw values input by user,
     * unless <code>$t_converted</code> is explicitly set to <code>true</code>
     * @param array $colors
     * @param int $effect
     * @param float|int $off
     * @param float|int $fadein
     * @param float|int $on
     * @param float|int $fadeout
     * @param float|int $rotate
     * @param float|int $offset
     * @param array $args
     * @param bool $t_converted
     */
    protected function __construct(array $colors, int $effect, float $off, float $fadein, float $on, float $fadeout,
                                   float $rotate, float $offset, array $args = array(), bool $t_converted = false)
    {
        $this->colors = $colors;
        $this->effect = $effect;
        $t_converted ? $this->setTimings($off, $fadein, $on, $fadeout, $rotate, $offset) :
            $this->setTimingsRaw($off, $fadein, $on, $fadeout, $rotate, $offset);
        $this->args = $args;
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

    public function setTimingsRaw(float $off, float $fadein, float $on, float $fadeout, float $rotation, float $offset)
    {
        $this->setTimings(self::convertToTiming($off), self::convertToTiming($fadein), self::convertToTiming($on),
            self::convertToTiming($fadeout), self::convertToTiming($rotation), self::convertToTiming($offset));
    }

    public function setTimings(int $off, int $fadein, int $on, int $fadeout, int $rotation, int $offset)
    {
        if($off > 255 || $off < 0 || $fadein > 255 || $fadein < 0 ||
            $on > 255 || $on < 0 || $fadeout > 255 || $fadeout < 0 ||
            $rotation > 255 || $rotation < 0 || $offset > 255 || $offset < 0)
        {
            throw new InvalidArgumentException("Timings have to be in range 0-255");
        }

        $this->timings[0] = $off;
        $this->timings[1] = $fadein;
        $this->timings[2] = $on;
        $this->timings[3] = $fadeout;
        $this->timings[4] = $rotation;
        $this->timings[5] = $offset;
    }

    /**
     * @return string
     */
    public function toHTML()
    {
        $html = "";
        $profile_colors = Utils::getString("profile_colors");
        $profile_effect = Utils::getString("profile_effect");
        $profile_color_input = Utils::getString("profile_color_input");
        $profile_add_color = Utils::getString("profile_add_color");
        $color_limit = $this->colorLimit();

        $colors_html = "";
        $effects_html = "";

        for($i = 0; $i < sizeof($this->getColors()); $i++)
        {
            $template = self::COLOR_TEMPLATE;
            $template = str_replace("\$active", $i == 0 ? "checked" : "", $template);
            $template = str_replace("\$label", "color-$i", $template);
            $template = str_replace("\$color", "#" . $this->getColors()[$i], $template);
            $colors_html .= $template;
        }

        foreach(static::effects() as $id => $effect)
        {
            $string = Utils::getString("profile_" . $effect);
            $effects_html .= "<option value=\"$id\"" . ($id == $this->effect ? " selected" : "") . ">$string</option>";
        }

        $btn_style = sizeof($this->colors) >= $color_limit ? " style=\"display: none\"" : "";
        $html .= "<div class=\"inline\">
        <label>
            $profile_effect
            <select class=\"form-control\" name=\"effect\" id=\"effect-select\">
                $effects_html
            </select>
        </label>
        <h3>$profile_colors</h3>
        <div id=\"swatches-container\" data-color-limit=\"$color_limit\">
            $colors_html
            <button id=\"add-color-btn\" class=\"btn btn-primary color-swatch\" type=\"button\"$btn_style>$profile_add_color</button>
        </div>

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
        $html .= "<div id=\"timing-arg-container\">";
        $html .= $this->timingArgHtml();
        $html .= "</div>";

        return $html;
    }

    public function timingArgHtml()
    {
        $html = "";

        $timings = $this->getTimingsForEffect();
        $timing_strings = self::TIMING_STRINGS;
        $profile_timing = Utils::getString("profile_timing");
        $profile_arguments = Utils::getString("profile_arguments");

        $arguments_html = "";
        $timing_html = "";

        if(sizeof($this->args) > 0)
        {
            foreach($this->args as $name => $argument)
            {
                switch($name)
                {
                    case "direction":
                        $str_cw = Utils::getString("profile_direction_cw");
                        $str_ccw = Utils::getString("profile_direction_ccw");
                        $str = Utils::getString("profile_arguments_".$name);
                        $selected0 = $argument ? " selected" : "";
                        $selected1 = $argument ? "" : " selected";
                        $arguments_html .= "<label class=\"inline-form\">
                                            $str
                                            <select class=\"form-control\" name=\"arg_$name\">
                                                <option value=\"" . DigitalDevice::DIRECTION_CW . "\"$selected0>$str_cw</option>
                                                <option value=\"" . DigitalDevice::DIRECTION_CCW . "\"$selected1>$str_ccw</option>
                                            </select>
                                        </label>";
                        break;
                    case "smooth":
                    case "fade_smooth":
                    case "fill_fade_return":
                        $str_yes = Utils::getString("yes");
                        $str_no = Utils::getString("no");
                        $str = Utils::getString("profile_arguments_" . $name);
                        $selected0 = $argument ? " selected" : "";
                        $selected1 = $argument ? "" : " selected";
                        $arguments_html .= "<label class=\"inline-form\">
                                            $str
                                            <select class=\"form-control\" name=\"arg_$name\">
                                                <option value=\"" . 1 . "\"$selected0>$str_yes</option>
                                                <option value=\"" . 0 . "\"$selected1>$str_no</option>
                                            </select>
                                        </label>";
                        break;
                    default:
                        $template = self::INPUT_TEMPLATE;
                        $template = str_replace("\$label", Utils::getString("profile_arguments_$name"), $template);
                        $template = str_replace("\$name", "arg_" . $name, $template);
                        $template = str_replace("\$placeholder", "", $template);
                        $template = str_replace("\$value", $argument, $template);
                        $arguments_html .= $template;
                }
            }
        }

        for($i = 0; $i < 6; $i++)
        {
            if(($timings & (1 << (5 - $i))) > 0)
            {
                $template = self::INPUT_TEMPLATE;
                $template = str_replace("\$label", Utils::getString("profile_timing_$timing_strings[$i]"), $template);
                $template = str_replace("\$name", "time_" . $timing_strings[$i], $template);
                $template = str_replace("\$placeholder", "1", $template);
                $template = str_replace("\$value", self::getTiming($this->timings[$i]), $template);
                $timing_html .= $template;
            }
            else
            {
                $template = self::HIDDEN_TEMPLATE;
                $template = str_replace("\$name", "time_" . $timing_strings[$i], $template);
                $template = str_replace("\$value", 0, $template);
                $timing_html .= $template;
            }
        }

        $html .= "<div>";
        if($timings != 0)
            $html .= "<h3>$profile_timing</h3>";
        $html .= "$timing_html</div>";
        if(sizeof($this->args) > 0)
            $html .= "<div><h3>$profile_arguments</h3>$arguments_html</div>";

        return $html;
    }

    public function toJson()
    {
        $data = array();

        $data["color_count"] = sizeof($this->colors);
        $data["times"] = $this->timings;
        $data["colors"] = $this->colors;
        $data["color_cycles"] = isset($this->args["color_cycles"]) ? $this->args["color_cycles"] : 1;
        $data["effect"] = $this->avrEffect();
        $data["args"] = $this->argsToArray();

        return $data;
    }

    public abstract function getTimingsForEffect();

    public abstract function colorLimit();

    public abstract function argsToArray();

    public static abstract function fromJson(array $json);

    public static abstract function effects();

    public static abstract function defaultFromEffect(int $effect);

    public static function getTiming(int $x)
    {
        if($x < 0 || $x > 255)
        {
            throw new InvalidArgumentException("x has to be an integer in range 0-255");
        }

        if($x <= 80)
        {
            return $x / 16;
        }
        if($x <= 120)
        {
            return $x / 8 - 5;
        }
        if($x <= 160)
        {
            return $x / 2 - 50;
        }
        if($x <= 190)
        {
            return $x - 130;
        }
        if($x <= 235)
        {
            return 2 * $x - 320;
        }
        if($x <= 245)
        {
            return 15 * $x - 3375;
        }
        return 60 * $x - 14400;
    }

    public static function convertToTiming($float)
    {
        foreach(self::getTimings() as $i => $timing)
        {
            if($float < $timing) return $i - 1;
        }
        return 0;
    }

    public static function getTimings()
    {
        $a = array();
        for($i = 0; $i < 256; $i++)
        {
            $a[$i] = self::getTiming($i);
        }
        return $a;
    }

    public abstract function avrEffect();
}