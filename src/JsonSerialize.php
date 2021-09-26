<?php

/**
 * JsonSerialize class
 *
 * @package Amk\JsonSerialize
 */

namespace Amk\JsonSerialize;

use Exception;
use ReflectionClass;
use ReflectionObject;

/**
 * This class serializes and deserializes a variable in json keeping the class type and saving also private objects
 */
class JsonSerialize
{
    const CLASS_KEY_FOR_JSON_SERIALIZE = '==_CL_==_==';

    /**
     * Return json string
     *
     * @param mixed   $value value to serialize
     * @param integer $flags json_encode flags
     * @param integer $depth json_encode depth
     *
     * @link https://www.php.net/manual/en/function.json-encode.php
     *
     * @return string|bool  Returns a JSON encoded string on success or false on failure.
     */
    public static function serialize($value, $flags = 0, $depth = 512)
    {
        return version_compare(PHP_VERSION, '5.5', '>=') ?
            json_encode(self::valueToJsonData($value), $flags, $depth) :
            json_encode(self::valueToJsonData($value), $flags);
    }

    /**
     * Return json string, equivalent to serialize, but with the possibility to skip some properties of object
     * Accept only object
     *
     * @param object   $obj       object to serialize
     * @param string[] $skipProps properties to skip
     * @param integer  $flags     json_encode flags
     * @param integer  $depth     json_encode depth
     *
     * @link https://www.php.net/manual/en/function.json-encode.php
     *
     * @return string
     */
    public static function serializeObj($obj, $skipProps = [], $flags = 0, $depth = 512)
    {
        if (!is_object($obj)) {
            throw new Exception('Invalid obj param');
        }

        return version_compare(PHP_VERSION, '5.5', '>=') ?
            json_encode(self::objectToJsonData($obj, [], $skipProps), $flags, $depth) :
            json_encode(self::objectToJsonData($obj, [], $skipProps), $flags);
    }

    /**
     * Unserialize from json
     *
     * @param string  $json  json string
     * @param integer $depth json_decode depth
     * @param integer $flags json_decode flags
     *
     * @link https://www.php.net/manual/en/function.json-decode.php
     *
     * @return mixed
     */
    public static function unserialize($json, $depth = 512, $flags = 0)
    {
        $publicArray = json_decode($json, true, $depth, $flags);
        return self::jsonDataToValue($publicArray);
    }

    /**
     * Unserialize json on passed object
     *
     * @param string  $json  json string
     * @param object  $obj   object to fill
     * @param integer $depth json_decode depth
     * @param integer $flags json_decode flags
     *
     * @link https://www.php.net/manual/en/function.json-decode.php
     *
     * @return object
     */
    public static function unserializeToObject($json, $obj, $depth = 512, $flags = 0)
    {
        if (!is_object($obj)) {
            throw new Exception('invalid obj param');
        }

        $result = self::jsonDataToValue(json_decode($json, true, $depth, $flags), $obj);
        if ($result !== $obj) {
            throw new Exception('invalid obj param');
        }
    }

    /**
     * Convert object to array with private and protected proprieties.
     * Private parent class proprieties aren't considered.
     *
     * @param object   $obj        obejct to serialize
     * @param string[] $objParents objs parents unique objects hash list
     * @param string[] $skipProps  properties to skip
     *
     * @return array
     */
    protected static function objectToJsonData($obj, $objParents = [], $skipProps = [])
    {
        $reflect = new ReflectionObject($obj);
        $result  = [ self::CLASS_KEY_FOR_JSON_SERIALIZE => $reflect->name ];

        if (is_subclass_of($obj, AbstractJsonSerializable::getAbstractJsonSerializableClass())) {
            $skipProps = array_unique(array_merge($skipProps, $obj->jsonSleep()));
        }

        // Get all props of current class but not props private of parent class
        foreach ($reflect->getProperties() as $prop) {
            $prop->setAccessible(true);
            $propName  = $prop->getName();
            if ($prop->isStatic() || in_array($propName, $skipProps)) {
                continue;
            }
            $propValue = $prop->getValue($obj);
            $result[$propName] = self::valueToJsonData($propValue, $objParents);
        }

        return $result;
    }

    /**
     * Recursive parse values, all objects are transformed to array
     *
     * @param mixed    $value      valute to parse
     * @param string[] $objParents objs parents unique hash ids
     *
     * @return mixed
     */
    protected static function valueToJsonData($value, $objParents = [])
    {
        if (is_scalar($value) || is_null($value)) {
            return $value;
        } elseif (is_object($value)) {
            $objHash = spl_object_hash($value);
            if (in_array($objHash, $objParents)) {
                // prevent recursion
                /** @todo store recursion in serialized json and restore it */
                return null;
            }
            $objParents[] = $objHash;
            return self::objectToJsonData($value, $objParents);
        } elseif (is_array($value)) {
            $result = [];
            foreach ($value as $key => $arrayVal) {
                $result[$key] = self::valueToJsonData($arrayVal, $objParents);
            }
            return $result;
        } else {
            return $value;
        }
    }

    /**
     * Return value from json decode data
     *
     * @param mixed       $value  value
     * @param object|null $newObj if is new create new object of fill the object param
     *
     * @return mixed
     */
    protected static function jsonDataToValue($value, $newObj = null)
    {
        if (is_scalar($value) || is_null($value)) {
            return $value;
        } elseif (($newClassName = self::getClassFromArray($value)) !== false) {
            if (is_object($newObj)) {
                // use the passed object as a parameter instead of creating a new one
            } elseif (class_exists($newClassName)) {
                $classReflect = new ReflectionClass($newClassName);
                $newObj = $classReflect->newInstanceWithoutConstructor();
            } else {
                $newObj = new \StdClass();
            }

            if ($newObj instanceof \stdClass) {
                foreach ($value as $arrayProp => $arrayValue) {
                    if ($arrayProp == self::CLASS_KEY_FOR_JSON_SERIALIZE) {
                        continue;
                    }
                    $newObj->{$arrayProp} = self::jsonDataToValue($arrayValue);
                }
            } else {
                $reflect = new ReflectionObject($newObj);
                foreach ($reflect->getProperties() as $prop) {
                    $prop->setAccessible(true);
                    $propName = $prop->getName();
                    if (!isset($value[$propName]) || $prop->isStatic()) {
                        continue;
                    }
                    $prop->setValue($newObj, self::jsonDataToValue($value[$propName]));
                }

                if (is_subclass_of($newObj, AbstractJsonSerializable::getAbstractJsonSerializableClass())) {
                    $method = $reflect->getMethod('jsonWakeup');
                    $method->setAccessible(true);
                    $method->invoke($newObj);
                }
            }

            return $newObj;
        } elseif (is_array($value)) {
            $result = [];
            foreach ($value as $key => $arrayVal) {
                $result[$key] = self::jsonDataToValue($arrayVal);
            }
            return $result;
        } else {
            return null;
        }
    }

    /**
     * Return class name from array values
     *
     * @param array $array array data
     *
     * @return bool|string  false if prop not found
     */
    protected static function getClassFromArray($array)
    {
        if (isset($array[self::CLASS_KEY_FOR_JSON_SERIALIZE])) {
            return $array[self::CLASS_KEY_FOR_JSON_SERIALIZE];
        } else {
            return false;
        }
    }
}
