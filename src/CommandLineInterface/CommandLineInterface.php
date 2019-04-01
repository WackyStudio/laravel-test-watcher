<?php

namespace WackyStudio\LaravelTestWatcher\CommandLineInterface;

use League\CLImate\CLImate;
use React\EventLoop\LoopInterface;
use WackyStudio\LaravelTestWatcher\TestFiles\TestFile;
use WackyStudio\LaravelTestWatcher\TestFiles\FilesToTestRepository;

class CommandLineInterface
{
    /**
     * @var CLImate
     */
    protected $climate;
    /**
     * @var FilesToTestRepository
     */
    private $filesToTest;
    /**
     * @var LoopInterface
     */
    private $loop;

    public function __construct(FilesToTestRepository $filesToTest, LoopInterface $loop)
    {
        $this->climate = new CLImate();
        $this->filesToTest = $filesToTest;
        $this->loop = $loop;
    }

    public function render()
    {
        $this->loop->addPeriodicTimer(1 / 2, function () {
            $this->climate->clear();
            $this->headerContent();
            $this->emptyLine();
            $this->testsContent();
            $this->failedTestsContent();
        });
    }

    public function emptyLine()
    {
        $this->climate->out('');
    }

    public function headerContent()
    {
        $this->emptyLine();
        $this->emptyLine();
        $this->climate->out(implode(PHP_EOL, [
            '<bold>Laravel Test Watcher</bold>',
            'By Wacky Studio',
            '',
            '____________________',
        ]));
    }

    public function testsContent()
    {
        $rowsNeeded = $this->filesToTest->getFilesToTest()->map(function (TestFile $file) {
            return count($file->getMethodsToWatch());
        })->max();

        $tests = $this->filesToTest->getFilesToTest()->map(function (TestFile $file) use ($rowsNeeded) {
            $passed = collect($file->getPassedTests());
            $failed = collect($file->getFailedTests());

            return collect([
                "<underline><bold><white>{$file->getNamespace()}\\</white><yellow>{$file->getClassName()}</yellow></bold></underline>",
                PHP_EOL,
            ])->merge(collect($file->getMethodsToWatch())->map(function ($item) use ($passed, $failed) {
                if ($passed->contains($item)) {
                    return "<green>{$item}</green>";
                } elseif ($failed->contains(function ($failed) use ($item) {
                    return $failed['method'] === $item;
                })) {
                    return "<red>{$item}</red>";
                } else {
                    return $item;
                }
            }))->pad($rowsNeeded + 2, '<black></black>');
        });

        $this->climate->columns($tests->transpose()->toArray());
    }

    public function failedTestsContent()
    {
        $failedOutput = $this->filesToTest->getFilesToTest()->flatMap(function (TestFile $file) {
            return collect($file->getFailedTests())->map(function ($item) {
                return $item['content'];
            });
        });

        if ($failedOutput->count() > 0) {
            $this->emptyLine();
            $this->emptyLine();
            $this->climate->out("<bold>{$failedOutput->count()}</bold> test(s) are failing:");

            $failedOutput->each(function ($content) {
                $this->emptyLine();

                collect(explode(PHP_EOL, $content))->filter(function ($item) {
                    return trim($item) !== '';
                })->values()
                  ->only(4, 5, 6)
                  ->values()
                  ->each(function ($item, $key) {
                      if ($key === 0) {
                          $this->climate->bold(str_replace('1) ', '', $item));
                      } elseif ($key === 1) {
                          $this->climate->backgroundRed()->white($item);
                      } else {
                          $this->climate->out($item);
                      }
                  });
            });
        }
    }
}
