<?php

namespace WackyStudio\LaravelTestWatcher\Tests\Factories;

use Orchestra\Testbench\TestCase;
use WackyStudio\LaravelTestWatcher\Factories\LaravelTestWatcherFactory;
use WackyStudio\LaravelTestWatcher\LaravelTestWatcher;
use WackyStudio\LaravelTestWatcher\LaravelTestWatcherServiceProvider;
use Yosymfony\ResourceWatcher\ResourceWatcher;

class LaravelTestWatcherFactoryTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            LaravelTestWatcherServiceProvider::class,
        ];
    }

    protected function getBasePath()
    {
        return __DIR__.'/../helpers';
    }

    /**
     * @test
     * @return void
     */
    public function it_creates_an_instance_of_laravel_test_watcher()
    {
        $factory = new LaravelTestWatcherFactory;

        $this->assertInstanceOf(LaravelTestWatcher::class, $factory->make());
    }

    /**
     * @test
     */
    public function it_maps_watch_directories_given_in_config_from_base_path()
    {
        $factory = new LaravelTestWatcherFactory;
        $this->assertEquals([
            base_path('app'),
            base_path('routes'),
            base_path('tests'),
        ], $factory->getDirectoriesToWatch());
    }

    /** @test */
    public function it_makes_a_finder_instance_only_for_test_files_for_finding_tests_to_be_watched_right_away()
    {
        $factory = new LaravelTestWatcherFactory;
        $finder = $factory->makeFinderForTestFiles();

        $files = collect([]);
        foreach ($finder as $file) {
            $files->add($file->getFilename());
        }

        $this->assertTrue($files->contains('TestOne.php'));
        $this->assertTrue($files->contains('TestTwo.php'));
    }

    /** @test */
    public function it_makes_a_watcher_for_watching_files_in_directories_given_in_config()
    {
        $factory = new LaravelTestWatcherFactory;
        $watcher = $factory->makeDirectoriesWatcher();

        $this->assertInstanceOf(ResourceWatcher::class, $watcher);
    }

    /**
     * @test
     */
    public function it_can_be_instantiated_from_a_static_method()
    {
        $watcher = LaravelTestWatcherFactory::create();
        $this->assertInstanceOf(LaravelTestWatcher::class, $watcher);
    }
}
