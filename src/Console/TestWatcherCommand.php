<?php

namespace WackyStudio\LaravelTestWatcher\Console;

use Illuminate\Console\Command;
use WackyStudio\LaravelTestWatcher\Facades\LaravelTestWatcher;

class TestWatcherCommand extends Command
{
    protected $signature = 'tests:watch';
    protected $description = 'Watch tests and source code for changes and run tests automatically';

    public function handle()
    {
        $this->info('Starting test watcher...');
        LaravelTestWatcher::watch();
    }
}
