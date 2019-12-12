<?php

namespace WackyStudio\LaravelTestWatcher;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use WackyStudio\LaravelTestWatcher\CommandLineInterface\CommandLineInterface;
use WackyStudio\LaravelTestWatcher\Contracts\CommandLineInterfaceContract;
use WackyStudio\LaravelTestWatcher\Contracts\PHPUnitRunnerContract;
use WackyStudio\LaravelTestWatcher\TestFiles\FilesToTestRepository;
use WackyStudio\LaravelTestWatcher\TestFiles\TestFile;

class PHPUnitRunner implements PHPUnitRunnerContract
{
    private $isRunningTests = false;

    /**
     * @var FilesToTestRepository
     */
    private $filesToTestRepository;

    /**
     * @var CommandLineInterface
     */
    private $cli;

    /**
     * PHPUnitRunner constructor.
     *
     * @param FilesToTestRepository $filesToTestRepository
     * @param CommandLineInterfaceContract $cli
     */
    public function __construct(FilesToTestRepository $filesToTestRepository, CommandLineInterfaceContract $cli)
    {
        $this->filesToTestRepository = $filesToTestRepository;
        $this->cli = $cli;
    }

    public function run()
    {
        if ($this->filesToTestRepository->getFilesToTest()->count() === 0) {
            return;
        }

        $this->isRunningTests = true;

        $this->filesToTestRepository->getFilesToTest()->each(function (TestFile $test) {
            $test->resetStatuses();

            foreach ($test->getMethodsToWatch() as $key=>$method) {
                $process = new Process([base_path().'/vendor/bin/phpunit', '--filter', $method, $test->getFilePath()], base_path());

                try {
                    $process->mustRun();
                    $test->addPassedTest($method);
                } catch (ProcessFailedException $exception) {
                    $test->addFailedTest($method, $exception->getProcess()->getOutput());
                }

                $this->cli->render();

                if ($key == array_keys($test->getMethodsToWatch())[count($test->getMethodsToWatch()) - 1]) {
                    $this->isRunningTests = false;
                }
            }
        });
    }

    public function isRunning()
    {
        return $this->isRunningTests;
    }
}
