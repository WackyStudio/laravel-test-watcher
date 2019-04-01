<?php

namespace WackyStudio\LaravelTestWatcher\Tests;

use Orchestra\Testbench\TestCase;
use Illuminate\Support\Facades\Artisan;
use WackyStudio\LaravelTestWatcher\LaravelTestWatcher;
use WackyStudio\LaravelTestWatcher\LaravelTestWatcherServiceProvider;
use WackyStudio\LaravelTestWatcher\Facades\LaravelTestWatcher as LaravelTestWatcherFacade;

class LaravelTestWatcherTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [LaravelTestWatcherServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'LaravelTestWatcher' => LaravelTestWatcherFacade::class,
        ];
    }

    protected function getBasePath()
    {
        return __DIR__.'/helpers';
    }

    /**
     * @test
     */
    public function it_can_be_started_through_an_artisan_command()
    {
        $this->withoutMockingConsoleOutput();
        LaravelTestWatcherFacade::shouldReceive('watch')->once()->andReturnNull();

        $this->artisan('tests:watch');
        $output = Artisan::output();

        $this->assertEquals('Starting test watcher...'.PHP_EOL, $output);
    }
}
