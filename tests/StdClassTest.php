<?php

/**
 * Tests for Std class
 *
 * @package Amk\JsonSerialize
 */

namespace Amk\JsonSerialize\Tests;

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
        $this->assertTrue(is_string($serializedValue), 'Value is string');
        $unserializedValue = JsonSerialize::unserialize($serializedValue);
        $this->assertEquals($value, $unserializedValue, 'Test stdClass object');

        $obj->c = new stdClass();
        $obj->c->a = 'test1';
        $obj->c->b = 'test2';
        $obj->c->c = null;
        $obj->c->d = ['a' => 'test', 'b' => 'test'];

        $value  = $obj;
        $serializedValue = JsonSerialize::serialize($value);
        $this->assertTrue(is_string($serializedValue), 'Value is string');
        $unserializedValue = JsonSerialize::unserialize($serializedValue);
        $this->assertEquals($value, $unserializedValue, 'Test stdClass object multiple level');

        $obj->c->e = $obj;
        $value = $obj;
        $serializedValue = JsonSerialize::serialize($value);
        $this->assertTrue(is_string($serializedValue), 'Value is string');
        $unserializedValue = JsonSerialize::unserialize($serializedValue);
        $this->assertSame($unserializedValue->c->e, null, 'Test stdClass object recursion'); /** @phpstan-ignore-line */

        $obj = new stdClass();
        $obj->a = 1;
        $obj->b = [1,2,3];

        if (($serializedValue = JsonSerialize::serialize($obj, JSON_PRETTY_PRINT | JsonSerialize::JSON_SKIP_CLASS_NAME)) == false) {
            $this->assertTrue($serializedValue, 'serialize fail'); // @phpstan-ignore-line
        }
        $unserializedValue = new stdClass();
        $unserializedValue = JsonSerialize::unserializeToObj($serializedValue, $unserializedValue);
        $this->assertEquals($obj, $unserializedValue, 'Test JSON_SERIALIZE_SKIP_CLASS_NAME flag');
    }
}
