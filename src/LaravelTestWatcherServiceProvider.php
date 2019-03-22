<?php

namespace WackyStudio\LaravelTestWatcher;

use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;
use Symfony\Component\Finder\Finder;
use WackyStudio\LaravelTestWatcher\Console\TestWatcherCommand;
use WackyStudio\LaravelTestWatcher\Contracts\AnnotatedTestsFinderContract;
use WackyStudio\LaravelTestWatcher\Factories\LaravelTestWatcherFactory;
use WackyStudio\LaravelTestWatcher\Finders\TestsAnnotatedWithWatchFinder;

class LaravelTestWatcherServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'laravel-test-watcher');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel-test-watcher');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('laravel-test-watcher.php'),
            ], 'config');
            // Publishing the views.
            /*$this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/laravel-test-watcher'),
            ], 'views');*/
            // Publishing assets.
            /*$this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/laravel-test-watcher'),
            ], 'assets');*/
            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/laravel-test-watcher'),
            ], 'lang');*/
            // Registering package commands.
            $this->commands([
                TestWatcherCommand::class,
            ]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'laravel-test-watcher');

        // Register the main class to use with the facade
        $this->app->singleton('laravel-test-watcher', function () {
            return ( new LaravelTestWatcherFactory )->make();
        });

        $this->app->bind(AnnotatedTestsFinderContract::class, function () {
            return new TestsAnnotatedWithWatchFinder;
        });

        $this->app->bind(LoopInterface::class, function () {
            return Factory::create();
        });
    }
}
