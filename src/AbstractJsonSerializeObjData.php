<?php

/**
 * AbstractJsonSerializeObjData class
 *
 * @package Amk\JsonSerialize
 */

namespace Amk\JsonSerialize;

use Exception;
use ReflectionClass;
use ReflectionObject;
use stdClass;

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
     *
     * @return array
     */
    final protected static function objectToJsonData($obj, $flags = 0, $objParents = [])
    {
        $reflect = new ReflectionObject($obj);
        $result = [];

        if (!($flags & self::JSON_SERIALIZE_SKIP_CLASS_NAME)) {
            $result[self::CLASS_KEY_FOR_JSON_SERIALIZE] = $reflect->name;
        }

        if (method_exists($obj, '__serialize')) {
            $data = $obj->__serialize();
            if (!is_array($data)) {
                throw new Exception('__serialize method must return an array');
            }
            return array_merge($data, $result);
        } elseif (method_exists($obj, '__sleep')) {
            $includeProps = $obj->__sleep();
            if (!is_array($includeProps)) {
                throw new Exception('__sleep method must return an array');
            }
        } else {
            $includeProps = true;
        }

        // Get all props of current class but not props private of parent class and static props
        foreach ($reflect->getProperties() as $prop) {
            if ($prop->isStatic()) {
                continue;
            }
            $propName  = $prop->getName();
            if ($includeProps !==  true && !in_array($propName, $includeProps)) {
                continue;
            }
            $prop->setAccessible(true);
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
    final protected static function valueToJsonData($value, $flags = 0, $objParents = [])
    {
        switch (gettype($value)) {
            case "boolean":
            case "integer":
            case "double":
            case "string":
            case "NULL":
                return $value;
            case "array":
                $result = [];
                foreach ($value as $key => $arrayVal) {
                    $result[$key] = self::valueToJsonData($arrayVal, $flags, $objParents);
                }
                return $result;
            case "object":
                $objHash = spl_object_hash($value);
                if (in_array($objHash, $objParents)) {
                    // prevent infinite recursion loop
                    return null;
                }
                $objParents[] = $objHash;
                return self::objectToJsonData($value, $flags, $objParents);
            case "resource":
            case "resource (closed)":
            case "unknown type":
            default:
                return null;
        }
    }

    /**
     * Return value from json decoded data
     *
     * @param mixed               $value json decoded data
     * @param ?JsonUnserializeMap $map   unserialize map
     *
     * @return mixed
     */
    final protected static function jsonDataToValue($value, $map = null)
    {
        if ($map !== null) {
            $current = $map->getCurrent();

            if ($map->isMapped()) {
                $mappedVal = $map->getMappedValue($value, $isReference);
                if ($isReference) {
                    return $mappedVal;
                }
                switch (gettype($mappedVal)) {
                    case 'array':
                        $result = [];
                        foreach ($mappedVal as $key => $arrayVal) {
                            $map->setCurrent($key, $current);
                            $result[$key] = self::jsonDataToValue($arrayVal, $map);
                        };
                        return $result;
                    case 'object':
                        if (!is_array($value)) {
                            $value = [];
                        }
                        return self::fillObjFromValue($value, $mappedVal, $map);
                    default:
                        return $mappedVal;
                }
            }
        }

        switch (gettype($value)) {
            case 'array':
                if (($newClassName = self::getClassFromArray($value)) === false) {
                    $result = [];
                    foreach ($value as $key => $arrayVal) {
                        if ($map !== null) {
                            $map->setCurrent($key, $current);
                        }
                        $result[$key] = self::jsonDataToValue($arrayVal, $map);
                    }
                } else {
                    if (class_exists($newClassName)) {
                        $classReflect = new ReflectionClass($newClassName);
                        $newObj = $classReflect->newInstanceWithoutConstructor();
                    } else {
                        $newObj = new \StdClass();
                    }
                    $result = self::fillObjFromValue($value, $newObj, $map);
                }
                return $result;
            case 'boolean':
            case 'integer':
            case 'double':
            case 'string':
            case "NULL":
                return $value;
            default:
                return null;
        }
    }

    /**
     * Fill passed object from array values
     *
     * @param array               $value value from json data
     * @param object              $obj   object to fill with json data
     * @param ?JsonUnserializeMap $map   unserialize map
     *
     * @return object
     */
    final protected static function fillObjFromValue($value, $obj, $map = null)
    {
        if ($map !== null) {
            $current = $map->getCurrent();
            $map->addReferenceObjOfCurrent($obj);
        }

        if ($obj instanceof \stdClass) {
            foreach ($value as $arrayProp => $arrayValue) {
                if ($arrayProp == self::CLASS_KEY_FOR_JSON_SERIALIZE) {
                    continue;
                }
                if ($map !== null) {
                    $map->setCurrent($arrayProp, $current);
                }
                $obj->{$arrayProp} = self::jsonDataToValue($arrayValue, $map);
            }
        } else {
            if (method_exists($obj, '__unserialize')) {
                $obj->__unserialize($value);
            } else {
                $reflect = new ReflectionObject($obj);
                foreach ($reflect->getProperties() as $prop) {
                    $prop->setAccessible(true);
                    $propName = $prop->getName();
                    if ($map !== null) {
                        $map->setCurrent($propName, $current);
                        if (!array_key_exists($propName, $value) && $map->isMapped()) {
                            $value[$propName] = null;
                        }
                    }
                    if (!array_key_exists($propName, $value) || $prop->isStatic()) {
                        continue;
                    }
                    $prop->setValue($obj, self::jsonDataToValue($value[$propName], $map));
                }

                if (method_exists($obj, '__wakeup')) {
                    $obj->__wakeup();
                }
            }
        }
        return $obj;
    }

    /**
     * Return class name from array values
     *
     * @param array $array array data
     *
     * @return bool|string  false if prop not found
     */
    final protected static function getClassFromArray($array)
    {
        return (isset($array[self::CLASS_KEY_FOR_JSON_SERIALIZE]) ? $array[self::CLASS_KEY_FOR_JSON_SERIALIZE] : false);
    }

    /**
     * Perform sanity checks on data that shall be encoded to JSON.
     *
     * @param mixed $data  Variable (usually an array or object) to encode as JSON.
     * @param int   $depth Maximum depth to walk through $data. Must be greater than 0.
     *
     * @return mixed The sanitized data that shall be encoded to JSON.
     *
     * @throws Exception If depth limit is reached.
     */
    public static function sanitizeData($data, $depth)
    {
        if ($depth < 0) {
            throw new Exception('Reached depth limit');
        }

        if (is_array($data)) {
            $output = array();
            foreach ($data as $id => $el) {
                // Don't forget to sanitize the ID!
                if (is_string($id)) {
                    $clean_id = self::convertString($id);
                } else {
                    $clean_id = $id;
                }

                // Check the element type, so that we're only recursing if we really have to.
                if (is_array($el) || is_object($el)) {
                    $output[ $clean_id ] = self::sanitizeData($el, $depth - 1);
                } elseif (is_string($el)) {
                    $output[ $clean_id ] = self::convertString($el);
                } else {
                    $output[ $clean_id ] = $el;
                }
            }
        } elseif (is_object($data)) {
            $output = new stdClass();
            foreach ($data as $id => $el) {
                if (is_string($id)) {
                    $clean_id = self::convertString($id);
                } else {
                    $clean_id = $id;
                }

                if (is_array($el) || is_object($el)) {
                    $output->$clean_id = self::sanitizeData($el, $depth - 1);
                } elseif (is_string($el)) {
                    $output->$clean_id = self::convertString($el);
                } else {
                    $output->$clean_id = $el;
                }
            }
        } elseif (is_string($data)) {
            return self::convertString($data);
        } else {
            return $data;
        }

        return $output;
    }

    /**
     * Convert a string to UTF-8, so that it can be safely encoded to JSON.
     *
     * @param string $string The string which is to be converted.
     *
     * @return string The checked string.
     */
    protected static function convertString($string)
    {
        static $use_mb = null;
        if (is_null($use_mb)) {
            $use_mb = function_exists('mb_convert_encoding');
        }

        if ($use_mb) {
            $encoding = mb_detect_encoding($string, mb_detect_order(), true);
            if ($encoding) {
                return mb_convert_encoding($string, 'UTF-8', $encoding);
            } else {
                return mb_convert_encoding($string, 'UTF-8', 'UTF-8');
            }
        } else {
            return self::checkInvalidUtf8($string, true);
        }
    }

    /**
     * Checks for invalid UTF8 in a string.
     *
     * @param string $string The text which is to be checked.
     * @param bool   $strip  Optional. Whether to attempt to strip out invalid UTF8. Default false.
     *
     * @return string The checked text.
     */
    protected static function checkInvalidUtf8($string, $strip = false)
    {
        $string = (string) $string;
        if (0 === strlen($string)) {
            return '';
        }

        // Check for support for utf8 in the installed PCRE library once and store the result in a static.
        static $utf8_pcre = null;
        if (!isset($utf8_pcre)) {
            $utf8_pcre = @preg_match('/^./u', 'a');
        }

        // We can't demand utf8 in the PCRE installation, so just return the string in those cases.
        if (!$utf8_pcre) {
            return $string;
        }

        if (1 === @preg_match('/^./us', $string)) {
            return $string;
        }

        // Attempt to strip the bad chars if requested (not recommended).
        if ($strip && function_exists('iconv')) {
            return iconv('utf-8', 'utf-8', $string);
        }

        return '';
    }
}
