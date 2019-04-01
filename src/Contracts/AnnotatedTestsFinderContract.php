<?php

namespace WackyStudio\LaravelTestWatcher\Contracts;

interface AnnotatedTestsFinderContract
{
    /**
     * @param string $filePath
     * @param string $fileContents
     *
     * @return TestFileContract
     */
    public function findAnnotatedTests(string $filePath, string $fileContents = '');
}
