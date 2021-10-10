<?php

/**
 * Tests vor basic values null, int, string ...
 *
 * @package Amk\JsonSerialize
 */

namespace Amk\JsonSerialize\Tests;

use Amk\JsonSerialize\JsonSerialize;
use Amk\JsonSerialize\Tests\Examples\ExampleClassExtendAbstractJsonSerializable;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Extended classes
 */
final class ExtendAbstractTest extends TestCase
{

     /**
      * Tests for Extended class
      *
      * @return void
      */
    public function testExtendedClass()
    {

        $obj = new ExampleClassExtendAbstractJsonSerializable(5, 10);
        $serializedValue = json_encode($obj, JSON_PRETTY_PRINT);
        $unserializedValue = JsonSerialize::unserialize($serializedValue);

        $this->assertEquals($obj, $unserializedValue, 'Test class with jsonSleep and jsonWakeup');
    }
}
