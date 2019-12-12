<?php

namespace WackyStudio\LaravelTestWatcher\TestFiles;

use Illuminate\Support\Collection;
use WackyStudio\LaravelTestWatcher\Contracts\AnnotatedTestsFinderContract;
use WackyStudio\LaravelTestWatcher\Contracts\TestFileContract;

class FilesToTestRepository
{
    /**
     * @var AnnotatedTestsFinderContract
     */
    private $testFinder;

    private $collection;
    private $oldCollection;

    public function __construct(AnnotatedTestsFinderContract $testFinder)
    {
        $this->testFinder = $testFinder;
        $this->collection = new TestFilesCollection;
    }

    /**
     * @param $files
     */
    public function update(array $files)
    {
        $this->oldCollection = clone $this->collection;

        foreach ($files as $file) {
            $testFile = $this->testFinder->findAnnotatedTests($file);
            if ($testFile->hasAnyTests()) {
                $this->collection->updateOrAdd($testFile);
            } else {
                $this->collection->removeIfExist($testFile);
            }
        }
    }

    /**
     * @return Collection
     */
    public function getFilesToTest()
    {
        return $this->collection->getCollection();
    }

    /**
     * @return array
     */
    public function getChanges()
    {
        $changes = $this->collection->compareToOldCollection($this->oldCollection);

        return (new Collection($changes))->map(function ($item) {
            return (new Collection($item))->map(function ($file) {
                if ($file instanceof TestFileContract) {
                    return [
                        'file' => $file->getNamespace().'\\'.$file->getClassName(),
                        'methods' => $file->getMethodsToWatch(),
                    ];
                }

                if (is_array($file) && isset($file['new']) && isset($file['old'])) {
                    return [
                        'file' => $file['new']->getNamespace().'\\'.$file['new']->getClassName(),
                        'methods' => $file['new']->getMethodsToWatch(),
                        'droppedMethods' => array_values(array_diff($file['old']->getMethodsToWatch(), $file['new']->getMethodsToWatch())),
                    ];
                }

                return $file;
            });
        })->toArray();
    }
}
