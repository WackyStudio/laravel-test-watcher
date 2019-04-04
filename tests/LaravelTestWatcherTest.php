<?php

namespace WackyStudio\LaravelTestWatcher\Tests;

use Orchestra\Testbench\TestCase;
use Illuminate\Support\Facades\Artisan;
use WackyStudio\LaravelTestWatcher\LaravelTestWatcher;
use WackyStudio\LaravelTestWatcher\TestFiles\FilesToTestRepository;
use WackyStudio\LaravelTestWatcher\LaravelTestWatcherServiceProvider;
use WackyStudio\LaravelTestWatcher\Factories\LaravelTestWatcherFactory;
use WackyStudio\LaravelTestWatcher\Contracts\CommandLineInterfaceContract;
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

    /** @test */
    public function it_prepares_by_running_through_filesystem_and_update_files_to_test_repository()
    {
        $testFileRepoMock = \Mockery::mock(FilesToTestRepository::class);
        $testFileRepoMock->shouldReceive('update')->with([
            __DIR__.'/helpers/tests/TestOne.php',
            __DIR__.'/helpers/tests/TestTwo.php',
        ])->andReturnNull();
        app()->instance(FilesToTestRepository::class, $testFileRepoMock);
        $cliMock = \Mockery::mock(CommandLineInterfaceContract::class);
        $cliMock->shouldReceive('render')->andReturnNull();
        app()->instance(CommandLineInterfaceContract::class, $cliMock);
        $watcher = LaravelTestWatcherFactory::create();

        $watcher->prepare();
    }
}
