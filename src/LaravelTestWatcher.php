<?php

namespace WackyStudio\LaravelTestWatcher;

use League\CLImate\CLImate;
use React\EventLoop\LoopInterface;
use Symfony\Component\Finder\Finder;
use WackyStudio\LaravelTestWatcher\Contracts\CommandLineInterfaceContract;
use WackyStudio\LaravelTestWatcher\Contracts\PHPUnitRunnerContract;
use Yosymfony\ResourceWatcher\ResourceWatcher;
use WackyStudio\LaravelTestWatcher\TestFiles\FilesToTestRepository;
use WackyStudio\LaravelTestWatcher\CommandLineInterface\CommandLineInterface;

class LaravelTestWatcher
{

    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var Finder
     */
    private $testFiles;

    /**
     * @var ResourceWatcher
     */
    private $directoriesWatcher;

    /**
     * @var FilesToTestRepository
     */
    private $filesToTest;

    /**
     * @var PHPUnitRunner
     */
    private $phpunitRunner;

    /**
     * @var CommandLineInterface
     */
    private $cli;

    public function __construct(LoopInterface $loop, Finder $testFiles, ResourceWatcher $directoriesWatcher)
    {
        $this->loop = $loop;
        $this->testFiles = $testFiles;
        $this->directoriesWatcher = $directoriesWatcher;
        $this->filesToTest = app(FilesToTestRepository::class);
        $this->cli = app(CommandLineInterfaceContract::class);
        $this->phpunitRunner = app(PHPUnitRunnerContract::class);
    }

    public function prepare()
    {
        $files = [];
        foreach ($this->testFiles as $file) {
            array_push($files, $file->getRealPath());
        }
        $this->filesToTest->update($files);
        $this->cli->render();
    }

    public function watch()
    {
        $this->prepare();
        $this->loop->addPeriodicTimer(1 / 4, function () {
            if ($this->phpunitRunner->isRunning()) {
                return;
            }
            $result = $this->directoriesWatcher->findChanges();
            if ($result->hasChanges()) {
                if (count($result->getDeletedFiles()) > 0) {
                    $this->filesToTest->update($result->getDeletedFiles());
                    $this->directoriesWatcher->rebuild();
                }
                if (count($result->getNewFiles()) > 0) {
                    $this->filesToTest->update($result->getNewFiles());
                    $this->directoriesWatcher->rebuild();
                }
                if (count($result->getUpdatedFiles()) > 0) {
                    $this->filesToTest->update($result->getUpdatedFiles());
                    $this->phpunitRunner->run();
                    $this->cli->render();
                }
            }
        });
        $this->loop->run();
    }

}
