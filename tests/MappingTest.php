<?php

/**
 * Tests for Std class
 *
 * @package Amk\JsonSerialize
 */

namespace Amk\JsonSerialize\Tests;

use Amk\JsonSerialize\JsonSerialize;
use Amk\JsonSerialize\JsonUnserializeMapping;
use Amk\JsonSerialize\Tests\Examples\ExampleClassEmptyCostructor;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Tests for Std class
 */
final class MappingTest extends TestCase
{
    /**
     * Tests scalar mapping
     *
     * @return void
     */
    public function testScalarMapping()
    {
        $obj = new stdClass();

        $obj->intVals = new stdClass();
        $obj->intVals->toFloat = 1;
        $obj->intVals->toBool = 10;
        $obj->intVals->toString = 10;
        $obj->intVals->toNULL = 10;
        $obj->intVals->notMapperd = 34;

        $obj->floatVals = new stdClass();
        $obj->floatVals->toInt = 1.5;
        $obj->floatVals->toString = 2.53433;
        $obj->floatVals->notMapperd = 34.4445;

        $obj->stringVals = new stdClass();
        $obj->stringVals->toInt = "23232";
        $obj->stringVals->toFloat = "12.2";
        $obj->stringVals->toBool = "1";
        $obj->stringVals->notMapperd = "not mapped";

        $obj->nullVal = new stdClass();
        $obj->nullVal->toString = null;
        $obj->nullVal->toInt = null;
        $obj->nullVal->toBool = null;
        $obj->nullVal->toFloat = null;
        $obj->nullVal->notMapperd = null;

        $map = new JsonUnserializeMapping(
            [
            '' => 'object',
            'intVals' => 'object',
            'intVals/toFloat' => 'float',
            'intVals/toBool' => 'bool',
            'intVals/toString' => 'string',
            'intVals/toNULL' => 'null',
            'floatVals' => 'object',
            'floatVals/toInt' => 'int',
            'floatVals/toString' => 'string',
            'stringVals' => 'object',
            'stringVals/toInt' => 'int',
            'stringVals/toFloat' => 'float',
            'stringVals/toBool' => 'bool',
            'nullVal' => 'object',
            'nullVal/toString' => 'string',
            'nullVal/toInt' => 'int',
            'nullVal/toBool' => 'bool',
            'nullVal/toFloat' => 'float'
            ]
        );

        $value  = $obj;
        $serializedValue = JsonSerialize::serialize($value, JsonSerialize::JSON_SERIALIZE_SKIP_CLASS_NAME);
        $unserObj = JsonSerialize::unserializeWithMapping($serializedValue, $map);

        $this->assertSame(is_object($unserObj), true, 'isn\'t object');
        $this->assertSame(is_object($unserObj->intVals), true, 'isn\'t object');
        $this->assertSame(is_object($unserObj->floatVals), true, 'isn\'t object');
        $this->assertSame(is_object($unserObj->stringVals), true, 'isn\'t object');
        $this->assertSame(is_object($unserObj->nullVal), true, 'isn\'t object');

        $this->assertSame($unserObj->intVals->toFloat, (float) $obj->intVals->toFloat);
        $this->assertSame($unserObj->intVals->toBool, (bool) $obj->intVals->toBool);
        $this->assertSame($unserObj->intVals->toString, (string) $obj->intVals->toString);
        $this->assertSame($unserObj->intVals->toNULL, null);
        $this->assertSame($unserObj->intVals->notMapperd, $obj->intVals->notMapperd);

        $this->assertSame($unserObj->floatVals->toInt, (int) $obj->floatVals->toInt);
        $this->assertSame($unserObj->floatVals->toString, (string) $obj->floatVals->toString);
        $this->assertSame($unserObj->floatVals->notMapperd, $obj->floatVals->notMapperd);

        $this->assertSame($unserObj->stringVals->toInt, (int) $obj->stringVals->toInt);
        $this->assertSame($unserObj->stringVals->toFloat, (float) $obj->stringVals->toFloat);
        $this->assertSame($unserObj->stringVals->toBool, (bool) $obj->stringVals->toBool);
        $this->assertSame($unserObj->stringVals->notMapperd, $obj->stringVals->notMapperd);

        $this->assertSame($unserObj->nullVal->toString, (string) null);
        $this->assertSame($unserObj->nullVal->toInt, (int) null);
        $this->assertSame($unserObj->nullVal->toBool, (bool) null);
        $this->assertSame($unserObj->nullVal->toFloat, (float) null);
        $this->assertSame($unserObj->nullVal->notMapperd, $obj->nullVal->notMapperd);
    }

     /**
      * Tests for Std class
      *
      * @return void
      */
    public function testNullableTypes()
    {
        $obj = new stdClass();
        $obj->intVals = [
            'notNull' => 10,
            'null'  => null
        ];
        $obj->stringVals = [
            'notNull' => "test",
            'null'  => null
        ];
        $obj->objVals = [
            'notNull' => new stdClass(),
            'null'  => null
        ];
        $obj->arrayVals = [
            'notNull' => ["a", "b", "c"],
            'null'  => null
        ];

        $map = new JsonUnserializeMapping(
            [
            '' => 'object',
            'intVals/notNull' => '?int',
            'intVals/null' => '?int',
            'stringVals/notNull' => '?string',
            'stringVals/null' => '?string',
            'objVals/notNull' => '?object',
            'objVals/null' => '?object',
            'arrayVals/notNull' => '?array',
            'arrayVals/null' => '?array',
            ]
        );

        $value  = $obj;
        $serializedValue = JsonSerialize::serialize($value, JsonSerialize::JSON_SERIALIZE_SKIP_CLASS_NAME);
        $unserObj = JsonSerialize::unserializeWithMapping($serializedValue, $map);

        $this->assertEquals($obj, $unserObj);
    }


    /**
     * Tests for Std class
     *
     * @return void
     */
    public function testClassTypes()
    {
        $value = [];
        $obj = new ExampleClassEmptyCostructor();
        $obj->publicProp = 'First item';
        $value['el1'] = $obj;
        $obj = new ExampleClassEmptyCostructor();
        $obj->publicProp = 'Second item';
        $value['el2'] = $obj;

        $map = new JsonUnserializeMapping(
            [
            'el1' => 'cl:' . ExampleClassEmptyCostructor::getClass(),
            'el2' => 'cl:' . ExampleClassEmptyCostructor::getClass()
            ]
        );

        $serializedValue = JsonSerialize::serialize($value, JsonSerialize::JSON_SERIALIZE_SKIP_CLASS_NAME);
        $unserVal = JsonSerialize::unserializeWithMapping($serializedValue, $map);

        $this->assertEquals($value, $unserVal);
    }

    /**
     * Test wild card prop
     *
     * @return void
     */
    public function testWildCardProp()
    {
        $value = new stdClass();
        $value->list1 = [];
        $value->list2 = [];

        for ($i = 0; $i < 3; $i++) {
            $obj = new ExampleClassEmptyCostructor();
            $obj->publicProp = 'Item NUM ' . $i;
            $value->list1[] = $obj;
        }

        for ($i = 0; $i < 2; $i++) {
            $obj = new ExampleClassEmptyCostructor();
            $obj->publicProp = 'Item NUM ' . $i;
            $value->list2['el_' . $i] = [ 'test' => $obj ];
        }

        $map = new JsonUnserializeMapping(
            [
            '' => 'object',
            'list1/*' => 'cl:' . ExampleClassEmptyCostructor::getClass(),
            'list2/*/test' => 'cl:' . ExampleClassEmptyCostructor::getClass()
            ]
        );

        $serializedValue = JsonSerialize::serialize($value, JsonSerialize::JSON_SERIALIZE_SKIP_CLASS_NAME);
        $unserVal = JsonSerialize::unserializeWithMapping($serializedValue, $map);

        $this->assertEquals($value, $unserVal);
    }

    /**
     * Test reference object
     *
     * @return void
     */
    public function testReference()
    {
        $value = new stdClass();

        $value->list = [
            'a' => new stdClass(),
            'b' => 2,
            'c' => new stdClass()
        ];

        $value->list['c']->value = $value;
        $value->list['c']->item = 'item';
        $value->list['c']->array = [
            $value->list['a'],
            $value->list['a'],
            $value->list['a']
        ];


        $map = new JsonUnserializeMapping(
            [
            '' => 'object',
            'list/a' => 'object',
            'list/c' => 'object',
            'list/c/value' => 'rf:',
            'list/c/array/*' => 'rf:list/a'
            ]
        );

        $serializedValue = JsonSerialize::serialize($value, JsonSerialize::JSON_SERIALIZE_SKIP_CLASS_NAME);
        $unserVal = JsonSerialize::unserializeWithMapping($serializedValue, $map);

        var_dump($unserVal);

        $this->assertEquals($value, $unserVal);
    }
}