<?php

/**
 * Created by PhpStorm.
 * User: Krzysiek
 * Date: 07/08/2017
 * Time: 18:40
 */
require_once(__DIR__ . "/Device.php");
require_once(__DIR__."/AnalogDevice.php");
require_once(__DIR__."/DigitalDevice.php");

class Profile
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var DigitalDevice[]
     */
    public $digital_devices = array();
    /**
     * @var AnalogDevice[]
     */
    public $analog_devices = array();

    function __construct($name)
    {
        $this->name = $name;

        array_push($this->analog_devices, AnalogDevice::_blinking());
        array_push($this->analog_devices, AnalogDevice::_static());
        array_push($this->digital_devices, DigitalDevice::_filling());
        array_push($this->digital_devices, DigitalDevice::_marquee());
        array_push($this->digital_devices, DigitalDevice::_fading());
    }

    /**
     * @param $name - string to set (max. 30 bytes)
     */
    public function setName(string $name)
    {
        if(strlen($name) > 30)
            throw new InvalidArgumentException("Name can only be 30 bytes long");
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function toJson()
    {
        $arr = array();
        /** @type Device[] */
        $merge = array_merge($this->analog_devices, $this->digital_devices);
        foreach($merge as $i => $device)
        {
            $arr[$i] = $device->toJson();
        }
        return $arr;
    }

    public function toSend($num)
    {
        $json = array();
        $json["type"] = "profile_update";
        $json["options"] = array("n" => $num);
        $json["data"] = $this->toJson();

        return json_encode($json);
    }
}