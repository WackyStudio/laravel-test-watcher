<?php

namespace WackyStudio\LaravelTestWatcher\Finders;

use Illuminate\Support\Collection;
use WackyStudio\LaravelTestWatcher\Contracts\AnnotatedTestsFinderContract;
use WackyStudio\LaravelTestWatcher\Contracts\TestFileContract;
use WackyStudio\LaravelTestWatcher\TestFiles\InvalidTestFile;
use WackyStudio\LaravelTestWatcher\TestFiles\TestFile;

class TestsAnnotatedWithWatchFinder implements AnnotatedTestsFinderContract
{

    /**
     * @param string $filePath
     * @param string $fileContents
     *
     * @return TestFileContract
     */
    public function findAnnotatedTests(string $filePath, string $fileContents = '')
    {
        if($fileContents === ''){
            try{
                $fileContents = file_get_contents($filePath);
            }catch (\Exception $exception){
                return new InvalidTestFile($filePath);
            }

        }

        $tokens = $this->convertContentsToTokenCollection($fileContents);
        $tokens = $this->filterWhiteSpaceAndMapTokensToNames($tokens);
        $namespace = $this->findNameSpace($tokens);
        $className = $this->findClassName($tokens);
        $testMethods = $this->findTestsAnnotatedWithWatch($tokens);

        return new TestFile($filePath, $className, $testMethods, $namespace);
    }

    /**
     * @param string $fileContents
     *
     * @return Collection
     */
    public function convertContentsToTokenCollection(string $fileContents)
    {
        return new Collection(token_get_all($fileContents));
    }

    /**
     * @param Collection $collection
     *
     * @return Collection
     */
    public function filterWhiteSpaceAndMapTokensToNames(Collection $collection)
    {
        return $collection->filter(function ($item) {
            if (is_array($item)) {
                return trim($item[1]) !== '';
            } else {
                return false;
            }
        })->map(function ($item) {
            return $item[1];
        })->values();
    }

    public function findNameSpace(Collection $tokens)
    {
        $namespace = [];
        foreach ($tokens as $key => $token) {
            if (strpos($token, 'namespace') !== false) {
                $currentKey = 1;
                while($tokens[$key + $currentKey] !== 'use' && $tokens[$key + $currentKey] !== 'class'){
                    array_push($namespace, $tokens[$key + $currentKey]);
                    $currentKey++;
                }

                return implode('',$namespace);
            }
        }

        return '';
    }

    /**
     * @param Collection $tokens
     *
     * @return string
     */
    public function findClassName(Collection $tokens)
    {
        foreach ($tokens as $key => $token) {
            if (strpos($token, 'class') !== false) {
                return $tokens[$key + 1];
            }
        }

        return '';
    }

    /**
     * @param Collection $tokens
     *
     * @return array
     */
    public function findTestsAnnotatedWithWatch(Collection $tokens)
    {
        $testMethods = [];
        foreach ($tokens as $key => $token) {
            if (strpos($token, '@watch') !== false) {
                if ($tokens[$key + 2] !== 'function') {
                    continue;
                }
                $testMethod = $tokens[$key + 3];
                array_push($testMethods, $testMethod);
            }
        }

        return $testMethods;
    }


}