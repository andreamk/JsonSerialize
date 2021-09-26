<?php

/**
 * Tests for Std class
 *
 * @package Amk\JsonSerialize
 */

declare(strict_types=1);

namespace Amk\JsonSerialize\UnitTests;

use Amk\JsonSerialize\JsonSerialize;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Tests for Std class
 */
final class StdClassTest extends TestCase
{

     /**
      * Tests for Std class
      *
      * @return void
      */
    public function testStdClass()
    {
        $obj = new stdClass();
        $obj->a = 1;
        $obj->b = [1,2,3];

        $value  = $obj;
        $serializedValue = JsonSerialize::serialize($value);
        $unserializedValue = JsonSerialize::unserialize($serializedValue);
        $this->assertEquals($value, $unserializedValue, 'Test stdClass object');

        $obj->c = new stdClass();
        $obj->c->a = 'test1';
        $obj->c->b = 'test2';
        $obj->c->c = null;
        $obj->c->d = ['a' => 'test', 'b' => 'test'];

        $value  = $obj;
        $serializedValue = JsonSerialize::serialize($value);
        $unserializedValue = JsonSerialize::unserialize($serializedValue);
        $this->assertEquals($value, $unserializedValue, 'Test stdClass object multiple level');

        /*
        $obj->c->e = $obj;

        $value  = $obj;
        $serializedValue = JsonSerialize::serialize($value);
        $unserializedValue = JsonSerialize::unserialize($serializedValue);
        $this->assertEquals($value, $unserializedValue, 'Test stdClass object recursion');
        */
    }
}
