<?php

namespace WackyStudio\LaravelTestWatcher\Console;

use Dotenv\Dotenv;
use Illuminate\Console\Command;
use WackyStudio\LaravelTestWatcher\Facades\LaravelTestWatcher;

class TestWatcherCommand extends Command
{
    protected $signature = 'tests:watch';
    protected $description = 'Watch tests and source code for changes and run tests automatically';

    public function handle()
    {
        $this->info('Starting test watcher...');
        $this->changeEnvironment();
        LaravelTestWatcher::watch();
    }

    private function changeEnvironment()
    {
        if (file_exists(base_path('.env.testing'))) {
            $dotenv = Dotenv::create(base_path(), '.env.testing');
            $dotenv->overload();
        }
    }
}
