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
class ExampleClassEmptyCostructor extends AbstractJsonSerializable
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
     * Init sub class
     *
     * @return void
     */
    public function initSubClass()
    {
        $this->subExample = new self();
        $this->subExample->updateValues();
    }
}
