<?php

namespace WackyStudio\LaravelTestWatcher\Contracts;

interface TestFileContract
{

    /**
     * @return bool
     */
    public function hasAnyTests();

    /**
     * @return string
     */
    public function getNamespace();

    /**
     * @return string
     */
    public function getClassName();

    /**
     * @return array
     */
    public function getMethodsToWatch();

    /**
     * @return string
     */
    public function getFilePath();

}