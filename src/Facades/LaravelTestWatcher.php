<?php

namespace WackyStudio\LaravelTestWatcher\Facades;

use Illuminate\Support\Facades\Facade;

class LaravelTestWatcher extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laravel-test-watcher';
    }
}
