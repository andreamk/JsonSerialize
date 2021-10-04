<?php

/**
 * AbstractJsonSerializeObjData class
 *
 * @package Amk\JsonSerialize
 */

namespace Amk\JsonSerialize;

use ReflectionClass;
use ReflectionObject;

/**
 * This calsse contains the logic that converts objects into values ready to be encoded in json
 */
abstract class AbstractJsonSerializeObjData
{
    const CLASS_KEY_FOR_JSON_SERIALIZE = 'CL_-=_-=';
    const JSON_SERIALIZE_SKIP_CLASS_NAME = 1073741824; // 30 bit mask

    /**
     * Convert object to array with private and protected proprieties.
     * Private parent class proprieties aren't considered.
     *
     * @param object   $obj        obejct to serialize
     * @param int      $flags      flags bitmask
     * @param string[] $objParents objs parents unique objects hash list
     * @param string[] $skipProps  properties to skip
     *
     * @return array
     */
    protected static function objectToJsonData($obj, $flags = 0, $objParents = [], $skipProps = [])
    {
        $reflect = new ReflectionObject($obj);
        if (!($flags & self::JSON_SERIALIZE_SKIP_CLASS_NAME)) {
            $result  = [ self::CLASS_KEY_FOR_JSON_SERIALIZE => $reflect->name ];
        }
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
            $result[$propName] = self::valueToJsonData($propValue, $flags, $objParents);
        }

        return $result;
    }

    /**
     * Recursive parse values, all objects are transformed to array
     *
     * @param mixed    $value      valute to parse
     * @param int      $flags      flags bitmask
     * @param string[] $objParents objs parents unique hash ids
     *
     * @return mixed
     */
    protected static function valueToJsonData($value, $flags = 0, $objParents = [])
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
            return self::objectToJsonData($value, $flags, $objParents);
        } elseif (is_array($value)) {
            $result = [];
            foreach ($value as $key => $arrayVal) {
                $result[$key] = self::valueToJsonData($arrayVal, $flags, $objParents);
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
     * @param object|null $newObj if is null create new object of fill the passed object by param
     *
     * @return mixed
     */
    protected static function jsonDataToValue($value, $newObj = null)
    {
        if (is_scalar($value) || is_null($value)) {
            return $value;
        } elseif (is_object($newObj)) {
            return self::fillObjFromValue($value, $newObj);
        } elseif (($newClassName = self::getClassFromArray($value)) !== false) {
            if (class_exists($newClassName)) {
                $classReflect = new ReflectionClass($newClassName);
                $newObj = $classReflect->newInstanceWithoutConstructor();
            } else {
                $newObj = new \StdClass();
            }
            return self::fillObjFromValue($value, $newObj);
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
     * Fill passed object from array values
     *
     * @param array  $value  value from json data
     * @param object $newObj object to fill with json data
     *
     * @return object
     */
    protected static function fillObjFromValue($value, $newObj)
    {
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
