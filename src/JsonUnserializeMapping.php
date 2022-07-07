<?php

/**
 * JsonUnserializeMapping class
 *
 * @package Amk\JsonSerialize
 */

namespace Amk\JsonSerialize;

use Exception;
use ReflectionClass;

/**
 * Unserialize mapping
 *
 * Accepted types
 *
 * bool
 * boolean
 * float
 * int
 * integer
 * string
 * array
 * object
 *
 * Special types
 * cl:ClassName     Istance of class
 * rf:PropReference Referenct of other prop
 *
 * If type start with ? is nullable
 */
class JsonUnserializeMapping
{
    /** @var MapItem */
    private $map = null;
    /** @var string */
    private $currentProp = '';
    /** @var bool */
    private $isCurrentMapped = false;
    /** @var mixed */
    private $currentType = null;
    /** @var object[] */
    private $objReferences = [];

    /**
     * Class constructor
     *
     * @param array<string, string> $map values map
     */
    public function __construct($map = [])
    {
        if (!is_array($map)) {
            throw new Exception('map must be an array');
        }
        $this->map = new MapItem();

        foreach ($map as $prop => $type) {
            $this->addMapItem($prop, $type);
        }
    }

    /**
     * Reset current prop
     *
     * @return void
     */
    public function resetCurrent()
    {
        $this->currentProp = '';
        $this->isCurrentMapped = false;
        $this->currentType = null;
        $this->objReferences = [];
    }

    /**
     * Add reference object
     *
     * @param object $obj object
     *
     * @return void
     */
    public function addReferenceObjOfCurrent($obj)
    {
        $this->objReferences[$this->currentProp] = $obj;
    }

    /**
     * Return current prop
     *
     * @return string
     */
    public function getCurrent()
    {
        return $this->currentProp;
    }

    /**
     * Return true if current propr is mapped
     *
     * @return bool
     */
    public function isMapped()
    {
        return $this->isCurrentMapped;
    }

    /**
     * Set current prop
     *
     * @param string $prop   prop name
     * @param string $parent prop parent
     *
     * @return bool return true if current prop is mapped
     */
    public function setCurrent($prop, $parent = '')
    {
        $this->currentProp = (strlen($parent) ? $parent . '/' : '') . $prop;
        $this->isCurrentMapped = false;
        $this->currentType = null;

        if (strlen($this->currentProp) == 0) {
            if ($this->map->type !== null) {
                $this->isCurrentMapped = true;
                $this->currentType = $this->map->type;
            }
        } else {
            $pArray = explode('/', $this->currentProp);
            $cLevel = $this->map;
            for ($i = 0; $i < count($pArray); $i++) {
                $cProp = $pArray[$i];
                if (isset($cLevel->childs[$cProp])) {
                    $cLevel = $cLevel->childs[$cProp];
                    continue;
                }

                if (isset($cLevel->childs['*'])) {
                    // wildcard
                    $cLevel = $cLevel->childs['*'];
                    continue;
                }

                break;
            }
            if ($i == count($pArray) && $cLevel->type !== null) {
                $this->isCurrentMapped = true;
                $this->currentType = $cLevel->type;
            }
        }
        return $this->isCurrentMapped;
    }

    /**
     * Return mapped value
     *
     * @param mixed $value       input value
     * @param bool  $isReference Set to true if is reference object
     *
     * @return mixed
     */
    public function getMappedValue($value, &$isReference = false)
    {
        if (!$this->isCurrentMapped) {
            return $value;
        }

        $type = $this->currentType;
        if ($type[0] === '?') {
            if ($value === null) {
                return null;
            }
            $type = substr($type, 1);
        }

        switch (substr($type, 0, 3)) {
            case 'cl:':
                $newClassName = substr($type, 3);
                if (class_exists($newClassName)) {
                    $classReflect = new ReflectionClass($newClassName);
                    $newObj = $classReflect->newInstanceWithoutConstructor();
                } else {
                    $newObj = new \StdClass();
                }
                return $newObj;
            case 'rf:':
                $reference = substr($type, 3);
                $isReference = true;
                if (isset($this->objReferences[$reference])) {
                    return $this->objReferences[$reference];
                } else {
                    return null;
                }
        }

        switch ($type) {
            case 'bool':
            case 'boolean':
                return (bool) $value;
            case 'float':
                return (float) $value;
            case 'int':
            case 'integer':
                return (int) $value;
            case 'string':
                return (string) $value;
            case 'array':
                return (array) $value;
            case 'object':
                return (object) $value;
            case 'null':
                return null;
            default:
                break;
        }

        /** @todo create flag not strict to return false */
        throw new Exception('Invalid mapping');
    }

    /**
     * Add map element
     *
     * @param string $prop prop itendifier, if exists overite it
     * @param string $type type to map
     *
     * @return void
     */
    public function addMapItem($prop, $type)
    {
        $cLevel = $this->map;
        if (strlen($prop) > 0) {
            $pArray = explode('/', $prop);
            $cLevel = $this->map;
            foreach ($pArray as $cProp) {
                if (!isset($cLevel->childs[$cProp])) {
                    $cLevel->childs[$cProp] = new MapItem();
                }
                $cLevel = $cLevel->childs[$cProp];
            }
        }
        $cLevel->type = $type;
    }

    /**
     * Add map element
     *
     * @param string $prop prop itendifier
     *
     * @return void
     */
    public function removeMapItem($prop)
    {
        if (isset($this->map[$prop])) {
            unset($this->map[$prop]);
        }
    }

    /**
     * Remove all map items
     *
     * @return void
     */
    public function resetMap()
    {
        $this->map = [];
    }
}