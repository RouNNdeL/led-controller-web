<?php

/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 07/08/2017
 * Time: 18:40
 */
require_once(__DIR__."/DeviceProfile.php");

class Profile
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var array
     */
    public $devices;

    function __construct()
    {

    }

    /**
     * @param $name - string to set (max. 30 bytes)
     * @return bool - <code>false</code> if string was longer than 30 bytes
     */
    public function setName(string $name)
    {
        if(strlen($name) > 30)
            return false;
        $this->name = $name;
        return true;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}