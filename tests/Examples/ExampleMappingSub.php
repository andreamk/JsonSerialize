<?php

/**
 * Example class
 *
 * @package Amk\JsonSerialize
 */

namespace Amk\JsonSerialize\Tests\Examples;

/**
 * Example class with open resource on __construct and __wakeup functions
 */
class ExampleMappingSub extends \Amk\JsonSerialize\AbstractJsonSerializable
{
    /** @var ExampleMapping */
    protected $parent = null;
    /** @var string[] */
    protected $array = [ 'a', 'b', 'c'];

    /**
     * Class contructor
     *
     * @param ExampleMapping $parent prent object
     */
    public function __construct(ExampleMapping $parent)
    {
        $this->parent = $parent;
    }

    /**
     * Filter props on json encode
     *
     * @return string[]
     */
    public function __sleep()
    {
        $props = array_keys(get_object_vars($this));
        return array_diff($props, array('parent'));
    }

    /**
     * Return class name of current class
     *
     * @return string
     */
    public static function getClass()
    {
        return __CLASS__;
    }
}
