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
class ExampleMapping extends \Amk\JsonSerialize\AbstractJsonSerializable
{
    /** @var string */
    protected $val1 = 'v1';
    /** @var string */
    protected $val2 = 'v2';
    /** @var ExampleMappingSub */
    protected $child = null;
    /** @var ExampleMappingSub */
    protected $child2 = null;

    /**
     * Class contructor
     */
    public function __construct()
    {
        $this->child = new ExampleMappingSub($this);
        $this->child2 = new ExampleMappingSub($this);
    }

    /**
     * Filter props on json encode
     *
     * @return string[]
     */
    public function __sleep()
    {
        $props = array_keys(get_object_vars($this));
        return array_diff($props, array('child2'));
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
