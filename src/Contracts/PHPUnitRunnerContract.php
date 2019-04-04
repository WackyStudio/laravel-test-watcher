<?php

namespace WackyStudio\LaravelTestWatcher\Contracts;

interface PHPUnitRunnerContract
{

    /**
     * @return void
     */
    public function run();

    /**
     * @return bool
     */
    public function isRunning();
}