<?php

/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 07/08/2017
 * Time: 18:42
 */
abstract class DeviceProfile
{
    const TIMING_STRINGS = ["off", "fadein", "on", "fadeout", "rotation", "offset"];

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
    public $color_count;
    public $timings;
    public $args;

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
     * @param int $rotate
     * @param int $offset
     * @param array $args
     */
    protected function __construct(array $colors, int $effect, int $off, int $fadein, int $on, int $fadeout,
                                   int $rotate, int $offset, array $args = array())
    {
        $this->colors = $colors;
        $this->effect = $effect;
        $this->setTimings($off, $fadein, $on, $fadeout, $rotate, $offset);
        $this->args = $args;
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

    public function setTimings(int $off, int $fadein, int $on, int $fadeout, int $rotation, $offset)
    {
        if ($off > 255 || $off < 0 || $fadein > 255 || $fadein < 0 ||
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

        if(sizeof($this->args) > 0) {
            foreach ($this->args as $name => $argument) {
                switch ($name) {
                    case "direction":
                        $str_cw = Utils::getString("profile_direction_cw");
                        $str_ccw = Utils::getString("profile_direction_ccw");
                        $str_dir = Utils::getString("profile_arguments_direction");
                        $arguments_html .= "<label class=\"inline-form\">
                                            $str_dir
                                            <select class=\"form-control\">
                                                <option value=\"" . DigitalDevice::DIRECTION_CW . "\">$str_cw</option>
                                                <option value=\"" . DigitalDevice::DIRECTION_CCW . "\">$str_ccw</option>
                                            </select>
                                        </label>";
                        break;
                    case "smooth":
                        $str_yes = Utils::getString("yes");
                        $str_no = Utils::getString("no");
                        $str_smth = Utils::getString("profile_arguments_smooth");
                        $arguments_html .= "<label class=\"inline-form\">
                                            $str_smth
                                            <select class=\"form-control\">
                                                <option value=\"" . 1 . "\">$str_yes</option>
                                                <option value=\"" . 0 . "\">$str_no</option>
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
        }

        for ($i = 0; $i < 6; $i++)
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

        foreach (static::effects() as $id => $effect)
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
        if (sizeof($this->args) > 0)
            $html .= "<div><h3>$profile_arguments</h3>$arguments_html</div>";

        $html .= "<button class=\"btn btn-primary\">$profile_apply</button>";
        return $html;
    }

    public abstract function getTimingsForEffect();

    public static abstract function effects();

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