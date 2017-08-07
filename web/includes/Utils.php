<?php

/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 07/08/2017
 * Time: 16:54
 */
class Utils
{
    /**
     * @var Utils
     */
    private static $instance;
    const DEFAULT_LANG = "en";

    public $strings;
    public $lang;

    /**
     * Utils constructor.
     */
    public function __construct()
    {
        $lang = isset($_GET["lang"]) ? $_GET["lang"] : Utils::DEFAULT_LANG;
        $this->lang = $lang;
        $this->loadStrings();
    }

    private function loadStrings()
    {
        $lang = $this->lang;
        $path = $_SERVER["DOCUMENT_ROOT"]."/_lang/$lang.json";
        $file = file_get_contents($path);
        $this->strings = json_decode($file, true);
    }

    public function getString(string $name)
    {
        if($this->strings != null && isset($this->strings[$name]))
        {
            return $this->strings[$name];
        }

        return null;
    }

    public static function getInstance()
    {
        if(Utils::$instance == null)
        {
            Utils::$instance = new Utils();
        }

        return Utils::$instance;
    }
}