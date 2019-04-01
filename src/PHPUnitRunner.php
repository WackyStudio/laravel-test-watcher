<?php

namespace WackyStudio\LaravelTestWatcher;

use React\EventLoop\LoopInterface;
use WyriHaximus\React\ProcessOutcome;
use Symfony\Component\Process\Process;
use React\ChildProcess\Process as ChildProcess;
use function WyriHaximus\React\childProcessPromise;
use WackyStudio\LaravelTestWatcher\TestFiles\TestFile;
use Symfony\Component\Process\Exception\ProcessFailedException;
use WackyStudio\LaravelTestWatcher\TestFiles\FilesToTestRepository;
use WackyStudio\LaravelTestWatcher\CommandLineInterface\CommandLineInterface;

class PHPUnitRunner
{
    private $isRunningTests = false;

    /**
     * @var FilesToTestRepository
     */
    private $filesToTestRepository;
    /**
     * @var LoopInterface
     */
    private $loop;
    /**
     * @var CommandLineInterface
     */
    private $cli;

    /**
     * PHPUnitRunner constructor.
     *
     * @param FilesToTestRepository $filesToTestRepository
     * @param LoopInterface $loop
     * @param CommandLineInterface $cli
     */
    public function __construct(FilesToTestRepository $filesToTestRepository, LoopInterface $loop, CommandLineInterface $cli)
    {
        $this->filesToTestRepository = $filesToTestRepository;
        $this->loop = $loop;
        $this->cli = $cli;
    }

    public function run()
    {
        $this->isRunningTests = true;

        $this->filesToTestRepository->getFilesToTest()->each(function (TestFile $test) {
            $test->resetStatuses();

            foreach ($test->getMethodsToWatch() as $key=>$method) {
                $process = new Process([base_path().'/vendor/bin/phpunit', '--filter', $method, $test->getFilePath()]);
                try {
                    $process->mustRun();
                    $test->addPassedTest($method);
                } catch (ProcessFailedException $exception) {
                    $test->addFailedTest($method, $exception->getProcess()->getOutput());
                }

                if ($key == array_keys($test->getMethodsToWatch())[count($test->getMethodsToWatch()) - 1]) {
                    $this->isRunningTests = false;
                }
            }
        });
    }

    public function runAsync()
    {
        $this->isRunningTests = true;

        $this->filesToTestRepository->getFilesToTest()->each(function (TestFile $test) {
            $test->resetStatuses();

            foreach ($test->getMethodsToWatch() as $key=>$method) {
                $process = new ChildProcess(base_path()."/vendor/bin/phpunit --filter {$method} {$test->getFilePath()}");

                childProcessPromise($this->loop, $process)
                    ->then(function (ProcessOutcome $result) use ($key, $test, $method) {
                        if ($result->getExitCode() === 0) {
                            $test->addPassedTest($method);
                        } else {
                            $test->addFailedTest($method, $result->getStdout());
                        }

                        if ($key == array_keys($test->getMethodsToWatch())[count($test->getMethodsToWatch()) - 1]) {
                            $this->isRunningTests = false;
                        }
                    }
                );
            }
        });
    }

    public function isRunning()
    {
        return $this->isRunningTests;
    }
}
