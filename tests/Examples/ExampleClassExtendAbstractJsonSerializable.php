<?php

/**
 * Example class
 *
 * @package Amk\JsonSerialize
 */

namespace Amk\JsonSerialize\Tests\Examples;

use Amk\JsonSerialize\AbstractJsonSerializable;
use stdClass;

/**
 * Example class
 */
class ExampleClassExtendAbstractJsonSerializable extends AbstractJsonSerializable
{
    /** @var int */
    protected $protectedProp = null;

    /** @var int */
    protected $a = null;
    /** @var int */
    protected $b = null;

    /**
     * Class contructor
     *
     * @param int $a generic param
     * @param int $b generic param
     */
    public function __construct($a, $b)
    {
        $this->a = $a;
        $this->b = $b;

        $this->protectedProp = $this->a + $this->b;
    }

    /**
     * This method is similar to the magic __sleep method but instead of returning
     * the list of properties to include it returns the list of properties to exclude
     *
     * @link https://www.php.net/manual/en/language.oop5.magic.php#object.sleep
     *
     * @return string[]
     */
    protected function jsonSleep()
    {
        return ['protectedProp'];
    }

    /**
     * This method is similar to the magic __wakeup method and it
     * is called after the json object has been read
     *
     * @link https://www.php.net/manual/en/language.oop5.magic.php#object.wakeup
     *
     * @return void
     */
    protected function jsonWakeup()
    {
        $this->protectedProp = $this->a + $this->b;
    }

    /**
     * Set the value of a
     *
     * @param int $a generic param
     *
     * @return void
     */
    public function setA($a)
    {
        $this->a = $a;
    }

    /**
     * Set the value of b
     *
     * @param int $b generic param
     *
     * @return void
     */
    public function setB($b)
    {
        $this->b = $b;
    }
}
