<?php

namespace WackyStudio\LaravelTestWatcher\TestFiles;

use WackyStudio\LaravelTestWatcher\Contracts\TestFileContract;

class TestFile implements TestFileContract
{
    protected $className;
    protected $testMethods = [];
    protected $filePath;
    protected $namespace;

    protected $passedTestMethods = [];
    protected $failedTestMethods = [];

    public function __construct(string $filePath, string $className, array $testMethods, string $namespace)
    {
        $this->filePath = $filePath;
        $this->className = $className;
        $this->testMethods = $testMethods;
        $this->namespace = $namespace;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @return array
     */
    public function getMethodsToWatch()
    {
        return $this->testMethods;
    }

    /**
     * @return bool
     */
    public function hasAnyTests()
    {
        return count($this->getMethodsToWatch()) > 0;
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
        return $this->namespace;
    }

    /**
     * @param $passedTest
     */
    public function addPassedTest($passedTest)
    {
        array_push($this->passedTestMethods, $passedTest);
    }

    /**
     * @return array
     */
    public function getPassedTests()
    {
        return $this->passedTestMethods;
    }

    /**
     * @param $method
     * @param $content
     */
    public function addFailedTest($method, $content)
    {
        array_push($this->failedTestMethods, [
            'method' => $method,
            'content' => $content,
        ]);
    }

    /**
     * @return array
     */
    public function getFailedTests()
    {
        return $this->failedTestMethods;
    }

    public function resetStatuses()
    {
        $this->passedTestMethods = [];
        $this->failedTestMethods = [];
    }
}