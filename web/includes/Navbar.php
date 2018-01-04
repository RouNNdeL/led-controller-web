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
        $data = Data::getInstance();
        $profiles = $data->getProfiles();
        $this->addLink(Utils::getString("global_options"), "/main");
        $this->highlight = $data->getHighlightIndex() + 1;
        foreach($profiles as $i => $profile)
        {
            $this->addLink($profile->getName(), "/profile/".$i);
        }
        if(sizeof($profiles) < Data::MAX_OVERALL_COUNT)
        {
            $this->addLink(Utils::getString("add_profile").
                "&nbsp;&nbsp;<span class=\"glyphicon glyphicon-plus\" aria-hidden=\"true\"></span>",
                "/profile/new");
        }
    }
}