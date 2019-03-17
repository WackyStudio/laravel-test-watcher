<?php

namespace WackyStudio\LaravelTestWatcher\Tests;

use Orchestra\Testbench\TestCase;
use WackyStudio\LaravelTestWatcher\LaravelTestWatcherServiceProvider;

class LaravelTestWatcherTest extends TestCase
{
    protected function getApplicationProviders($app)
    {
        return [
            LaravelTestWatcherServiceProvider::class,
        ];
    }

    /** @test */
    public function it_()
    {

    }
}
