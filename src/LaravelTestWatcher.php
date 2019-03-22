<?php

namespace WackyStudio\LaravelTestWatcher;

use React\EventLoop\LoopInterface;
use Symfony\Component\Finder\Finder;
use WackyStudio\LaravelTestWatcher\CommandLineInterface\CommandLineInterface;
use WackyStudio\LaravelTestWatcher\TestFiles\FilesToTestRepository;
use Yosymfony\ResourceWatcher\ResourceWatcher;

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
        $this->cli = new CommandLineInterface($this->filesToTest, $this->loop);
        $this->phpunitRunner = new PHPUnitRunner($this->filesToTest, $this->loop, $this->cli);
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
                }
            }
        });

        $this->loop->run();
    }
}
