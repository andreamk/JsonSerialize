<?php

/**
 *
 * @package JsonSerializable
 */

namespace Amk\JsonSerialize;

abstract class AbstractJsonSerializable extends JsonSerialize implements \JsonSerializable
{
    /**
     * prepared json serialized object
     *
     * @return mixed
     */
    final public function jsonSerialize()
    {
        return self::objectToJsonData($this, [], $this->jsonSleep());
    }

    /**
     * This method is similar to the magic __sleep method but instead of returning
     * the list of properties to include it returns the list of properties to exclude
     *
     * @link https://www.php.net/manual/en/language.oop5.magic.php#object.sleep
     *
     * @return string[]
     */
    protected function jsonSleep()
    {
        return [];
    }

    /**
     * This method is similar to the magic __wakeup method and it
     * is called after the json object has been read
     *
     * @link https://www.php.net/manual/en/language.oop5.magic.php#object.wakeup
     *
     * @return void
     */
    protected function jsonWakeup()
    {
    }

    /**
     * This function is returns the name of the class as the special constant ::class introduced in PHP 5.5
     *
     * @return string
     */
    final public static function getAbstractJsonSerializableClass()
    {
        return __CLASS__;
    }
}
