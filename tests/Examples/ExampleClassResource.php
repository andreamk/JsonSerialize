<?php

/**
 * Example class
 *
 * @package Amk\JsonSerialize
 */

namespace Amk\JsonSerialize\Tests\Examples;

use Exception;

/**
 * Example class with open resource on __construct and __wakeup functions
 */
class ExampleClassResource extends \Amk\JsonSerialize\AbstractJsonSerializable
{
    /** @var string */
    protected $path = '';
    /** @var false|resource */
    protected $handle = false;

    /**
     * Class costructor
     *
     * @param string $path file path
     */
    public function __construct($path)
    {
        $this->path = $path;
        $this->openFile();
    }

    /**
     * Class destructor
     */
    public function __destruct()
    {
        if (is_resource($this->handle)) {
            fclose($this->handle);
        }
    }

    /**
     * Open file
     *
     * @return void
     */
    protected function openFile()
    {
        if (($this->handle = fopen($this->path, 'a+')) == false) {
            throw new Exception('can\'t open file ' . $this->path);
        }
    }

    /**
     * Get file content
     *
     * @return string
     */
    public function getContent()
    {
        if (($filesize = (int) filesize($this->path)) == 0) {
            return '';
        }
        if (!$this->handle) {
            return '';
        }
        fseek($this->handle, 0);

        if (($result = fread($this->handle, $filesize)) === false) {
            return '';
        }
        return $result;
    }

    /**
     * Write file content
     *
     * @param string $content content to write
     *
     * @return bool
     */
    public function writeContent($content)
    {
        if (!$this->handle) {
            return false;
        }

        fseek($this->handle, 0);
        if (fwrite($this->handle, $content) === false) {
            throw new Exception('Cannot write to file ' . $this->path);
        }
        return true;
    }

    /**
     * It can clean up the object and is supposed to return an array with the
     * names of all variables of that object that should be serialized.
     *
     * @return string[]
     */
    public function __sleep()
    {
        return array('path');
    }

    /**
     * This function can reconstruct any resources that the object may have.
     *
     * @return void
     */
    public function __wakeup()
    {
        $this->openFile();
    }

    /**
     * Return class name
     *
     * @return class-string<object>
     */
    public static function getClassName()
    {
        return __CLASS__;
    }
}
