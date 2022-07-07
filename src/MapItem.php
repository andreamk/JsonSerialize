<?php

/**
 * Map item class
 *
 * @package Amk\JsonSerialize
 */

namespace Amk\JsonSerialize;

/**
 * Map item element
 */
class MapItem
{
    /** @var ?string */
    public $type = null;
    /** @var MapItem */
    public $childs = [];
}