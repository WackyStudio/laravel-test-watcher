<?php

namespace WackyStudio\LaravelTestWatcher\Tests\TestFiles;

use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;
use WackyStudio\LaravelTestWatcher\TestFiles\TestFile;
use WackyStudio\LaravelTestWatcher\TestFiles\TestFilesCollection;

class TestFilesCollectionTest extends TestCase
{
    /** @test */
    public function it_creates_a_new_collection_as_container()
    {
        $collection = new TestFilesCollection;
        $this->assertInstanceOf(Collection::class, $collection->getCollection());
    }

    /** @test */
    public function it_can_add_a_test_file_to_collection()
    {
        $collection = new TestFilesCollection;
        $testfile = new TestFile('/path', 'Testing', [], 'namespace');
        $collection->add($testfile);
        $this->assertTrue($collection->getCollection()->contains($testfile));
    }

    /** @test */
    public function it_can_update_an_existing_test_file_in_collection()
    {
        $collection = new TestFilesCollection;
        $testfile = new TestFile('/path', 'Testing', [], 'namespace');
        $collection->add($testfile);
        $this->assertTrue($collection->getCollection()->contains($testfile));

        $testfileUpdated = new TestFile('/path', 'Testing', [
            'testMethodOne',
            'testMethodTwo',
        ], 'namespace');
        $collection->update($testfileUpdated);

        $this->assertTrue($collection->getCollection()->contains($testfileUpdated));
        $this->assertFalse($collection->getCollection()->contains($testfile));
    }

    /** @test */
    public function it_can_update_an_exisiting_files_if_it_exists_or_it_will_add_it()
    {
        $collection = new TestFilesCollection;
        $testfile = new TestFile('/path/one', 'Testing', [], 'namespace');
        $collection->updateOrAdd($testfile);
        $this->assertTrue($collection->getCollection()->contains($testfile));

        $testfileUpdated = new TestFile('/path/one', 'Testing', [
            'testMethodOne',
            'testMethodTwo',
        ], 'namespace');
        $collection->updateOrAdd($testfileUpdated);

        $this->assertTrue($collection->getCollection()->contains($testfileUpdated));
        $this->assertFalse($collection->getCollection()->contains($testfile));

        $newTestfile = new TestFile('/path/three', 'TestingAgain', [], 'namespace');
        $collection->updateOrAdd($newTestfile);

        $this->assertTrue($collection->getCollection()->contains($testfileUpdated));
        $this->assertTrue($collection->getCollection()->contains($newTestfile));
    }

    /** @test */
    public function it_will_remove_a_test_file_if_it_exists()
    {
        $collection = new TestFilesCollection;

        $testfile = new TestFile('/path/one', 'Testing', [], 'namespace');
        $testFileTwo = new TestFile('/path/two', 'TestingTwo', [], 'namespace');
        $testFileThree = new TestFile('/path/three', 'TestingThree', [], 'namespace');

        $collection->updateOrAdd($testfile);
        $collection->updateOrAdd($testFileTwo);
        $collection->updateOrAdd($testFileThree);

        $this->assertTrue($collection->getCollection()->contains($testfile));
        $this->assertTrue($collection->getCollection()->contains($testFileTwo));
        $this->assertTrue($collection->getCollection()->contains($testFileThree));

        $collection->removeIfExist($testfile);
        $this->assertFalse($collection->getCollection()->contains($testfile));
    }

    /** @test */
    public function it_can_tell_if_it_has_a_test_file_in_collection_already()
    {
        $collection = new TestFilesCollection;

        $testfile = new TestFile('/path/one', 'Testing', [], 'namespace');
        $testFileTwo = new TestFile('/path/two', 'TestingTwo', [], 'namespace');
        $testFileThree = new TestFile('/path/three', 'TestingThree', [], 'namespace');

        $collection->updateOrAdd($testfile);
        $collection->updateOrAdd($testFileTwo);
        $collection->updateOrAdd($testFileThree);

        $this->assertTrue($collection->getCollection()->contains($testfile));
        $this->assertTrue($collection->getCollection()->contains($testFileTwo));
        $this->assertTrue($collection->getCollection()->contains($testFileThree));

        $this->assertTrue($collection->has($testfile));
    }

    /** @test */
    public function it_can_get_a_test_file_by_filepath_if_it_exists()
    {
        $collection = new TestFilesCollection;

        $testfile = new TestFile('/path/one', 'Testing', [], 'namespace');
        $collection->updateOrAdd($testfile);
        $this->assertTrue($collection->getCollection()->contains($testfile));

        $firstTestFile = $collection->getByFilePath('/path/one');
        $this->assertEquals($testfile, $firstTestFile);

        $notExistingFile = $collection->getByFilePath('/does/not/exist');
        $this->assertNull($notExistingFile);
    }

    /** @test */
    public function it_can_compare_itself_to_another_collection()
    {
        $oldCollection = new TestFilesCollection;

        $testfile = new TestFile('/path/one', 'Testing', [
            'test_method',
        ], 'namespace');
        $testFileTwo = new TestFile('/path/two', 'TestingTwo', [
            'test_method',
        ], 'namespace');

        $oldCollection->updateOrAdd($testfile);
        $oldCollection->updateOrAdd($testFileTwo);

        $newCollection = new TestFilesCollection;
        $newTestfile = new TestFile('/path/one', 'Testing', [
            'new_test_method',
        ], 'namespace');
        $testFileThree = new TestFile('/path/three', 'TestingThree', [], 'namespace');
        $newCollection->updateOrAdd($newTestfile);
        $newCollection->updateOrAdd($testFileThree);

        $result = $newCollection->compareToOldCollection($oldCollection);
        $this->assertEquals([
            'added' => [
                $testFileThree,
            ],
            'updated' => [
                [
                    'old' => $testfile,
                    'new' => $newTestfile,
                ],
            ],
            'removed' => [
                $testFileTwo,
            ],
        ], $result);
    }
}
