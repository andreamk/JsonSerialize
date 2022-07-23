<?php

/**
 * Tests vor basic values null, int, string ...
 *
 * @package Amk\JsonSerialize
 */

namespace Amk\JsonSerialize\Tests;

use Amk\JsonSerialize\JsonSerialize;
use Amk\JsonSerialize\Tests\Examples\ExampleClassEmptyCostructor;
use Amk\JsonSerialize\Tests\Examples\ExampleClassResource;
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
        $value = new ExampleClassEmptyCostructor();
        $value->updateValues();
        $value->initSubClass();

        $serializedValue = JsonSerialize::serialize($value, JSON_PRETTY_PRINT);
        $this->assertTrue(is_string($serializedValue), 'Value is string');
        $unserializedValue = JsonSerialize::unserialize($serializedValue);
        $this->assertEquals($value, $unserializedValue, 'Test class with empty costructor');

        $value->publicProp = 'change prop';
        $serializedValue = JsonSerialize::serialize($value, JSON_PRETTY_PRINT);
        $this->assertTrue(is_string($serializedValue), 'Value is string');
        $unserializedValue = new ExampleClassEmptyCostructor();
        JsonSerialize::unserializeToObj($serializedValue, $unserializedValue);
        $this->assertEquals($value, $unserializedValue, 'Test unserializeToObj with class with empty costructor');

        $serializedValue = JsonSerialize::serialize(
            $value,
            JSON_PRETTY_PRINT | JsonSerialize::JSON_SKIP_CLASS_NAME
        );
        $this->assertTrue(is_string($serializedValue), 'Value is string');
        $unserializedValue = JsonSerialize::unserialize($serializedValue);
        $this->assertEquals($value->getArray(), $unserializedValue, 'Test sierialize obj with skip props and skip class name');
    }

    /**
     * Test resource
     *
     * @return void
     */
    public function testResourceClass()
    {
        $testDir = rtrim(sys_get_temp_dir(), '\\/') . '/json_serialize_tests';

        if (!file_exists($testDir)) {
            mkdir($testDir);
        } else {
            $files = glob($testDir . '/*');
            if (is_array($files)) {
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
            }
        }

        $contentString = 'File content';
        $object = new ExampleClassResource($testDir . '/test.txt');
        $object->writeContent($contentString);

        $serializedValue = JsonSerialize::serialize($object, JSON_PRETTY_PRINT);
        $this->assertTrue(is_string($serializedValue), 'Value is string');
        $object = new ExampleClassResource($testDir . '/test.txt');
        /** @var ExampleClassResource $unserializedValue */
        $unserializedValue = JsonSerialize::unserialize($serializedValue);
        $this->assertInstanceOf(ExampleClassResource::getClassName(), $unserializedValue);

        /* content readed from file */
        $contentCheck = $unserializedValue->getContent();
        $this->assertEquals($contentString, $contentCheck, "check if resource after unserilized object is initialized");


        /* test array of objects */
        $numElements = 5;

        $list = array();
        $contentString = 'Content index ';
        for ($i = 0; $i < $numElements; $i++) {
            $list[$i] = new ExampleClassResource($testDir . '/test_' . $i . '.txt');
            $list[$i]->writeContent($contentString . $i);
        }

        $serializedValue = json_encode($list, JSON_PRETTY_PRINT);
        $this->assertTrue(is_string($serializedValue), 'Value is string');
        $unserializedValue = JsonSerialize::unserialize($serializedValue);
        $this->assertTrue(is_array($unserializedValue), "check if resource after unserilized object is array");
        $this->assertEquals($numElements, count($unserializedValue), "check if resource after unserilized object is initialized");

        for ($i = 0; $i < $numElements; $i++) {
            $origContent = $contentString . $i;
            $unserialContent = $unserializedValue[$i]->getContent();
            $this->assertEquals($origContent, $unserialContent, "Check content index " . $i);
        }
    }
}
