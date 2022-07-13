<?php

/**
 * Example class
 *
 * @package Amk\JsonSerialize
 */

namespace Amk\JsonSerialize\Tests\Examples;

use stdClass;

/**
 * Example class
 */
class ExampleClassEmptyCostructor
{
    /** @var string */
    public $publicProp = 'public';
    /** @var string */
    protected $protectedProp = 'protected';
    /** @var string */
    private $privateProp = 'private';
    /** @var object */
    protected $stdObject = null;
    /** @var self */
    protected $subExample = null;

    /**
     * Class contructor
     */
    public function __construct()
    {
        if ($this->privateProp) {
            $this->privateProp ++;
        }
    }

    /**
     * Update props values
     *
     * @return void
     */
    public function updateValues()
    {
        $this->publicProp = 'public_updated';
        $this->protectedProp = 'protected_updated';
        $this->privateProp = 'private_updated';
        $this->stdObject = new stdClass();
        $this->stdObject->a = 1;
        $this->stdObject->b = 2;
    }

    /**
     * Return object to associative array
     *
     * @return mixed[]
     */
    public function getArray()
    {
        $result = get_object_vars($this);
        if (is_object($result['stdObject'])) {
            $result['stdObject'] = (array) $result['stdObject'];
        }
        if (is_object($result['subExample'])) {
            $result['subExample'] = $this->subExample->getArray();
        }
        return $result;
    }

    /**
     * Init sub class
     *
     * @return void
     */
    public function initSubClass()
    {
        $this->subExample = new self();
        $this->subExample->updateValues();
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
