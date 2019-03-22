<?php

namespace WackyStudio\LaravelTestWatcher\TestFiles;

use Illuminate\Support\Collection;
use WackyStudio\LaravelTestWatcher\Contracts\TestFileContract;

class TestFilesCollection
{

    /**
     * @var Collection
     */
    private $collection;

    public function __construct()
    {
        $this->collection = new Collection([]);
    }

    /**
     * @param TestFileContract $file
     *
     * @return $this
     */
    public function add(TestFileContract $file)
    {
        $this->collection->add($file);
        return $this;
    }

    /**
     * @param TestFileContract $file
     *
     * @return $this
     */
    public function update(TestFileContract $file)
    {
        $this->collection = $this->collection->filter(function(TestFileContract $item) use($file){
            return $item->getFilePath() !== $file->getFilePath();
        })->add($file)->values();
        return $this;
    }

    /**
     * @param TestFileContract $file
     *
     * @return $this
     */
    public function updateOrAdd(TestFileContract $file)
    {
        $this->update($file);
        return $this;
    }

    /**
     * @param TestFileContract $file
     *
     * @return $this
     */
    public function removeIfExist(TestFileContract $file)
    {
        $this->collection = $this->collection->filter(function(TestFileContract $item) use($file){
            return $item->getFilePath() !== $file->getFilePath();
        })->values();
        return $this;
    }

    public function has(TestFileContract $file)
    {
        return $this->collection->contains(function (TestFileContract $item) use($file){
            return $item->getFilePath() === $file->getFilePath();
        });
    }

    /**
     * @param string $filePath
     *
     * @return TestFileContract|null
     */
    public function getByFilePath(string $filePath)
    {
        return $this->collection->filter(function (TestFileContract $item) use($filePath) {
            return $item->getFilePath() === $filePath;
        })->first();
    }

    /**
     * @return Collection
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * @param TestFilesCollection $oldTestFilesCollection
     *
     * @return array
     */
    public function compareToOldCollection(TestFilesCollection $oldTestFilesCollection)
    {
        $oldCollection = $oldTestFilesCollection->getCollection();
        $new = $this->collection->filter(function(TestFileContract $file) use($oldCollection){
           return !$oldCollection->contains(function(TestFileContract $item) use($file){
               return $file->getFilePath() === $item->getFilePath();
           });
        })->values();


        $updated = $this->collection->map(function(TestFileContract $file) use($oldCollection){

            $oldMatch = $oldCollection->filter(function(TestFileContract $item) use($file){
                return $file->getFilePath() === $item->getFilePath();
            })->first();

            if($oldMatch !== null && count(array_diff($oldMatch->getMethodsToWatch(), $file->getMethodsToWatch())))
            {
                return ['old' => $oldMatch, 'new' => $file];
            }
            return null;
        })->filter(function($item){
            return $item !== null;
        })->values();

        $removed = $oldCollection->filter(function(TestFileContract $file){
            return !$this->collection->contains(function(TestFileContract $item) use($file){
                return $file->getFilePath() === $item->getFilePath();
            });
        })->values();

        return [
            'added' => $new->toArray(),
            'updated' => $updated->toArray(),
            'removed' => $removed->toArray(),
        ];
    }
}