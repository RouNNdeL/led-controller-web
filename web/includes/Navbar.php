<?php

/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 08/08/2017
 * Time: 18:42
 */
require_once("Data.php");

class Navbar
{
    private $tabs;
    private $active;

    function __construct()
    {
        $this->active = 0;
        $this->tabs = array();
    }

    public function addRaw($content)
    {
        array_push($this->tabs, $content);
    }

    public function toHtml()
    {
        $html = "";
        $html .= "<ul class=\"nav nav-pills\">";
        for ($i = 0; $i < sizeof($this->tabs); $i++)
        {
            $tab = $this->tabs[$i];
            $html .= "<li role=\"presentation\"" .
                ($i == $this->active ? "class=\"active\"" : "") . ">$tab</li>";
        }
        $html .= "</ul>";
        return $html;
    }

    public function addLink(string $text, string $url)
    {
        if (strlen($url) == 0)
            $url = "#";
        $this->addRaw("<a href=\"$url\">$text</a>");
    }

    /**
     * @param int $active
     */
    public function setActive(int $active)
    {
        $this->active = $active;
    }

    public function initDefault()
    {
        $utils = Utils::getInstance();
        $profiles = Data::getInstance()->getProfiles();
        $this->addLink($utils->getString("global_options"), "/main");
        for($i = 0; $i < sizeof($profiles); $i++)
        {
            $this->addLink($profiles[$i]->getName(), "/profile/".($i+1));
        }
        if(sizeof($profiles) < 12)
        {
            $this->addLink($utils->getString("add_profile"), "/profile/new");
        }
    }
}