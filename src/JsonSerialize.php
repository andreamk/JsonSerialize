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
    const CLASS_KEY_FOR_JSON_SERIALIZE = 'CL_-=_-=';
    const JSON_SERIALIZE_SKIP_CLASS_NAME = 1073741824; // 30 bit mask

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
            json_encode(self::objectToJsonData($obj, $flags, [], $skipProps), $flags, $depth) :
            json_encode(self::objectToJsonData($obj, $flags, [], $skipProps), $flags);
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

        $result = self::jsonDataToValue(json_decode($json, true, $depth, $flags), $obj);
        if ($result !== $obj) {
            throw new Exception('invalid obj param');
        }
        return $result;
    }
}
