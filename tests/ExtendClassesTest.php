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

        $value->publicProp = 'change prop';
        $serializedValue = JsonSerialize::serialize($value, JSON_PRETTY_PRINT);
        $unserializedValue = new ExampleClassEmptyCostructor();
        JsonSerialize::unserializeToObj($serializedValue, $unserializedValue);
        $this->assertEquals($value, $unserializedValue, 'Test unserializeToObj with class with empty costructor');


        $serializedValue = JsonSerialize::serializeObj(
            $value,
            [ 'subExample', 'stdObject' ],
            JSON_PRETTY_PRINT | JsonSerialize::JSON_SERIALIZE_SKIP_CLASS_NAME
        );
        $unserializedValue = JsonSerialize::unserialize($serializedValue);
        $check_value = [
            "publicProp" => "change prop",
            "protectedProp" => "protected_updated",
            "privateProp" => "private_updated"
        ];
        $this->assertEquals($check_value, $unserializedValue, 'Test sierialize obj with skip props and skip class name');
    }
}
