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
    private $highlight;

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
        $html .= "<ul id=\"main-navbar\" class=\"nav nav-pills\">";
        for ($i = 0; $i < sizeof($this->tabs); $i++)
        {
            $tab = $this->tabs[$i];
            $html .= "<li role=\"presentation\"" .
                ($i == $this->active ? " class=\"active\"" :
                    ($i == $this->highlight ? " class=\"highlight\"" : "")) . ">$tab</li>";
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
        $profiles = Data::getInstance()->getProfiles();
        $this->addLink(Utils::getString("global_options"), "/main");
        $this->highlight = Data::getInstance()->active_profile+1;
        for($i = 0; $i < sizeof($profiles); $i++)
        {
            $this->addLink($profiles[$i]->getName(), "/profile/".($i+1));
        }
        if(sizeof($profiles) < 12)
        {
            $this->addLink(Utils::getString("add_profile").
                "&nbsp;&nbsp;<span class=\"glyphicon glyphicon-plus\" aria-hidden=\"true\"></span>",
                "/profile/new");
        }
    }
}