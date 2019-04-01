<?php

namespace WackyStudio\LaravelTestWatcher\TestFiles;

use WackyStudio\LaravelTestWatcher\Contracts\TestFileContract;

class InvalidTestFile implements TestFileContract
{
    protected $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * @return bool
     */
    public function hasAnyTests()
    {
        return false;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return 'invalid';
    }

    /**
     * @return array
     */
    public function getMethodsToWatch()
    {
        return [];
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return '';
    }
}
