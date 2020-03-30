<?php

namespace WackyStudio\LaravelTestWatcher\CommandLineInterface;

use Illuminate\Support\Collection;
use League\CLImate\CLImate;
use WackyStudio\LaravelTestWatcher\Contracts\CommandLineInterfaceContract;
use WackyStudio\LaravelTestWatcher\TestFiles\FilesToTestRepository;
use WackyStudio\LaravelTestWatcher\TestFiles\TestFile;

class CommandLineInterface implements CommandLineInterfaceContract
{
    /**
     * @var CLImate
     */
    protected $climate;
    /**
     * @var FilesToTestRepository
     */
    private $filesToTest;

    public function __construct(FilesToTestRepository $filesToTest, Climate $climate)
    {
        $this->climate = $climate;
        $this->filesToTest = $filesToTest;
    }

    public function render()
    {
        $this->climate->clear();
        $this->headerContent();
        $this->emptyLine();
        $this->testsContent();
        $this->failedTestsContent();
    }

    public function emptyLine()
    {
        $this->climate->out("\n");
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
        if ($this->filesToTest->getFilesToTest()
                              ->count() == 0) {
            $this->climate->out('<yellow><bold>No test cases to watch</bold></yellow>');

            return;
        }
        $rowsNeeded = $this->filesToTest->getFilesToTest()
                                        ->map(function (TestFile $file) {
                                            return count($file->getMethodsToWatch());
                                        })
                                        ->max();

        $tests = $this->filesToTest
            ->getFilesToTest()
            ->map(function (TestFile $file) use ($rowsNeeded) {
                $passed = collect($file->getPassedTests());
                $failed = collect($file->getFailedTests());

                return collect([
                    "<underline><bold><white>{$file->getNamespace()}\\</white><yellow>{$file->getClassName()}</yellow></bold></underline>",
                    "\n",
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
        $failedOutput = $this->filesToTest->getFilesToTest()
                              ->flatMap(function (TestFile $file) {
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

                $collection = $this->removeLineBreaksAndEmptyLines($content);
                $collection = $this->removeUnnecessaryPHPUnitOutput($collection);

                $collection->each(function ($item, $key) {
                    if ($key === 0) {
                        $this->climate->backgroundWhite()->black()->bold(' '.str_replace('1) ', '', $item).' ');
                    } elseif ($key === 1) {
                        $this->climate->backgroundRed()
                                      ->white(' '.$item.' ');
                    } else {
                        $this->climate->bold($item);
                    }
                });

                $this->emptyLine();
            });
        }
    }

    /**
     * @param $content
     *
     * @return \Illuminate\Support\Collection
     */
    private function removeLineBreaksAndEmptyLines($content)
    {
        return collect(explode("\n", $content))->filter(function ($item) {
            return trim($item) !== '';
        })->values();
    }

    /**
     * @param Collection $collection
     *
     * @return Collection
     */
    private function removeUnnecessaryPHPUnitOutput(Collection $collection)
    {
        $collection = $collection->slice(4)
                   ->values();
        $collection->pop();
        $collection->pop();

        return $collection;
    }
}
