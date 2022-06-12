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
class ExampleClassMagicSerializeUnserialize extends \Amk\JsonSerialize\AbstractJsonSerializable
{
    /** @var int */
    protected $value1 = 0;
    /** @var int */
    protected $value2 = 0;

    /**
     * Class costructor
     *
     * @param int $v1 value 1
     * @param int $v2 value 2
     */
    public function __construct($v1, $v2)
    {
        $this->value1 = $v1;
        $this->value2 = $v2;
    }

    /**
     * Return array to serialize
     *
     * @return mixed[]
     */
    public function __serialize()
    {
        return array(
            'a' => $this->value1 + 10,
            'b' => $this->value2 + 10
        );
    }

    /**
     * Unserialize data
     *
     * @param mixed[] $data serialized data
     *
     * @return void
     */
    public function __unserialize($data)
    {
        $this->value1 = $data['a'] - 10;
        $this->value2 = $data['b'] - 10;
    }
}
