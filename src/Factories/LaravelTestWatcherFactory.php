<?php

namespace WackyStudio\LaravelTestWatcher\Factories;

use Illuminate\Support\Collection;
use React\EventLoop\LoopInterface;
use Symfony\Component\Finder\Finder;
use Illuminate\Support\Facades\Config;
use Yosymfony\ResourceWatcher\ResourceWatcher;
use Yosymfony\ResourceWatcher\Crc32ContentHash;
use Yosymfony\ResourceWatcher\ResourceCacheMemory;
use WackyStudio\LaravelTestWatcher\LaravelTestWatcher;
use WackyStudio\LaravelTestWatcher\Contracts\AnnotatedTestsFinderContract;

class LaravelTestWatcherFactory
{
    /**
     * @return LaravelTestWatcher
     */
    public static function create()
    {
        return (new static())->make();
    }

    /**
     * Creates a new LaravelTestWatcher instance.
     *
     * @return LaravelTestWatcher
     */
    public function make()
    {
        $loop = app(LoopInterface::class);
        $testFiles = $this->makeFinderForTestFiles();
        $directoriesWatcher = $this->makeDirectoriesWatcher();
        $annotatedTestFinder = app(AnnotatedTestsFinderContract::class);

        return new LaravelTestWatcher($loop, $testFiles, $directoriesWatcher, $annotatedTestFinder);
    }

    /**
     * @return Finder
     */
    public function makeFinderForTestFiles()
    {
        $testFiles = $this->makeFinder([base_path('tests')])
                          ->name('*.php');

        return $testFiles;
    }

    /**
     * @param Finder $finder
     *
     * @return ResourceWatcher
     */
    public function makeDirectoriesWatcher()
    {
        $finder = $this->makeFinder($this->getDirectoriesToWatch());
        $watcher = new ResourceWatcher(new ResourceCacheMemory(), $finder, new Crc32ContentHash());

        return $watcher;
    }

    /**
     * @return Finder
     */
    public function makeFinder(array $directories)
    {
        $finder = new Finder();
        $finder->files()
               ->in($directories);

        return $finder;
    }

    /**
     * Maps directories given in config file to base path.
     *
     * @return array
     */
    public function getDirectoriesToWatch()
    {
        $directories = Collection::make(Config::get('laravel-test-watcher.watch_directories'));

        return $directories->map(function ($directory) {
            return base_path($directory);
        })->toArray();
    }
}
