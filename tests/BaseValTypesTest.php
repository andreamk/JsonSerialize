<?php

/**
 * Tests vor basic values null, int, string ...
 *
 * @package Amk\JsonSerialize
 */

namespace Amk\JsonSerialize\Tests;

use Amk\JsonSerialize\JsonSerialize;
use PHPUnit\Framework\TestCase;

/**
 * Tests vor basic values null, int, string ...
 */
final class BaseValTypesTest extends TestCase
{
    /**
     * Scalar values tests
     *
     * @return void
     */
    public function testScalarValues()
    {
        $value  = null;
        $serializedValue = JsonSerialize::serialize($value);
        $this->assertTrue(is_string($serializedValue), 'Value is string');
        $unserializedValue = JsonSerialize::unserialize($serializedValue);
        $this->assertSame($value, $unserializedValue, 'Test null value');

        $value  = 0;
        $serializedValue = JsonSerialize::serialize($value);
        $this->assertTrue(is_string($serializedValue), 'Value is string');
        $unserializedValue = JsonSerialize::unserialize($serializedValue);
        $this->assertSame($value, $unserializedValue, 'Test 0 int');

        $value  = 10;
        $serializedValue = JsonSerialize::serialize($value);
        $this->assertTrue(is_string($serializedValue), 'Value is string');
        $unserializedValue = JsonSerialize::unserialize($serializedValue);
        $this->assertSame($value, $unserializedValue, 'Test 10 int');

        $value  = -10;
        $serializedValue = JsonSerialize::serialize($value);
        $this->assertTrue(is_string($serializedValue), 'Value is string');
        $unserializedValue = JsonSerialize::unserialize($serializedValue);
        $this->assertSame($value, $unserializedValue, 'Test -10 int');

        $value  = 10.1;
        $serializedValue = JsonSerialize::serialize($value);
        $this->assertTrue(is_string($serializedValue), 'Value is string');
        $unserializedValue = JsonSerialize::unserialize($serializedValue);
        $this->assertSame($value, $unserializedValue, 'Test 10.1 float');

        $value  = '';
        $serializedValue = JsonSerialize::serialize($value);
        $this->assertTrue(is_string($serializedValue), 'Value is string');
        $unserializedValue = JsonSerialize::unserialize($serializedValue);
        $this->assertSame($value, $unserializedValue, 'Test empty string');

        $value  = 'test string';
        $serializedValue = JsonSerialize::serialize($value);
        $this->assertTrue(is_string($serializedValue), 'Value is string');
        $unserializedValue = JsonSerialize::unserialize($serializedValue);
        $this->assertSame($value, $unserializedValue, 'Test string');

        $value  = '1000';
        $serializedValue = JsonSerialize::serialize($value);
        $this->assertTrue(is_string($serializedValue), 'Value is string');
        $unserializedValue = JsonSerialize::unserialize($serializedValue);
        $this->assertSame($value, $unserializedValue, 'Test numeric string');

        $value  = true;
        $serializedValue = JsonSerialize::serialize($value);
        $this->assertTrue(is_string($serializedValue), 'Value is string');
        $unserializedValue = JsonSerialize::unserialize($serializedValue);
        $this->assertSame($value, $unserializedValue, 'Test bool true');

        $value  = false;
        $serializedValue = JsonSerialize::serialize($value);
        $this->assertTrue(is_string($serializedValue), 'Value is string');
        $unserializedValue = JsonSerialize::unserialize($serializedValue);
        $this->assertSame($value, $unserializedValue, 'Test bool false');
    }

    /**
     * Array values tests
     *
     * @return void
     */
    public function testArrayValues()
    {
        $value  = [];
        $serializedValue = JsonSerialize::serialize($value);
        $this->assertTrue(is_string($serializedValue), 'Value is string');
        $unserializedValue = JsonSerialize::unserialize($serializedValue);
        $this->assertSame($value, $unserializedValue, 'Test empty array');

        $value  = [1,2,3,4];
        $serializedValue = JsonSerialize::serialize($value);
        $this->assertTrue(is_string($serializedValue), 'Value is string');
        $unserializedValue = JsonSerialize::unserialize($serializedValue);
        $this->assertSame($value, $unserializedValue, 'Test int array');

        $value  = ['a','b','c','d'];
        $serializedValue = JsonSerialize::serialize($value);
        $this->assertTrue(is_string($serializedValue), 'Value is string');
        $unserializedValue = JsonSerialize::unserialize($serializedValue);
        $this->assertSame($value, $unserializedValue, 'Test string array');

        $value  = ['a' => 1,'b' => 2,'c' => 3,'d' => 4];
        $serializedValue = JsonSerialize::serialize($value);
        $this->assertTrue(is_string($serializedValue), 'Value is string');
        $unserializedValue = JsonSerialize::unserialize($serializedValue);
        $this->assertSame($value, $unserializedValue, 'Test strgin array keys');

        $value  = [
            'a' => 1,
            'b' => 2,
            1 => [],
            'd' => [
                1,
                'a' => 1,
                'b' => 'test',
                'c' => [
                    1,
                    2,
                    3
                ]
            ]
        ];
        $serializedValue = JsonSerialize::serialize($value);
        $this->assertTrue(is_string($serializedValue), 'Value is string');
        $unserializedValue = JsonSerialize::unserialize($serializedValue);
        $this->assertSame($value, $unserializedValue, 'Test multi level array');
    }
}
