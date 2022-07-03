<?php

/**
 * JsonSerialize class
 *
 * @package Amk\JsonSerialize
 */

namespace Amk\JsonSerialize;

use Exception;

/**
 * This class serializes and deserializes a variable in json keeping the class type and saving also private objects
 */
class JsonSerialize extends AbstractJsonSerializeObjData
{
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
            json_encode(self::valueToJsonData($value, $flags), $flags, $depth) :
            json_encode(self::valueToJsonData($value, $flags), $flags);
    }

    /**
     * Returns value ready to be serialized, for objects returns a value key array, other data types are unchanged
     *
     * @param mixed   $value value to serialize
     * @param integer $flags JsonSerialize flags
     *
     * @return mixed Returns a JSON encoded string on success or false on failure.
     */
    public static function dataToSerialize($value, $flags = 0)
    {
        return self::valueToJsonData($value, $flags);
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
    public static function unserializeToObj($json, $obj, $depth = 512, $flags = 0)
    {
        if (!is_object($obj)) {
            throw new Exception('invalid obj param');
        }
        $value = json_decode($json, true, $depth, $flags);
        if (!is_array($value)) {
            throw new Exception('json value isn\'t an array');
        }
        return self::fillObjFromValue($value, $obj);
    }
}
