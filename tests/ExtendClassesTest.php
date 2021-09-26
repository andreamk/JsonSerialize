<?php

/**
 * Tests vor basic values null, int, string ...
 *
 * @package Amk\JsonSerialize
 */

declare(strict_types=1);

namespace Amk\JsonSerialize\Tests;

use Amk\JsonSerialize\JsonSerialize;
use Amk\JsonSerialize\Tests\Examples\ExampleClassEmptyCostructor;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Extended classes
 */
final class ExtendClassesTest extends TestCase
{

     /**
      * Tests for Extended class
      *
      * @return void
      */
    public function testExtendedClass()
    {
        $value  = new ExampleClassEmptyCostructor();
        $value->updateValues();
        $value->initSubClass();

        $serializedValue = JsonSerialize::serialize($value, JSON_PRETTY_PRINT);
        $unserializedValue = JsonSerialize::unserialize($serializedValue);
        $this->assertEquals($value, $unserializedValue, 'Test class with empty costructor');

        echo $serializedValue;
    }
}
