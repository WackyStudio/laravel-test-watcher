<?php

namespace WackyStudio\LaravelTestWatcher\Tests\Finders;

use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;
use WackyStudio\LaravelTestWatcher\Contracts\TestFileContract;
use WackyStudio\LaravelTestWatcher\Finders\TestsAnnotatedWithWatchFinder;
use WackyStudio\LaravelTestWatcher\TestFiles\InvalidTestFile;

class WatchAnnotatedTestsFinderTest extends TestCase
{
    /** @test */
    public function it_converts_file_contents_to_a_collection_of_tokens()
    {
        $finder = new TestsAnnotatedWithWatchFinder;
        $collection = $finder->convertContentsToTokenCollection(file_get_contents(__DIR__.'/../helpers/tests/TestOne.php'));
        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertEquals("<?php\n", $collection->first()[1]);
    }

    /** @test */
    public function it_filters_whites_space_and_maps_to_token_contents()
    {
        $finder = new TestsAnnotatedWithWatchFinder;
        $collection = $finder->convertContentsToTokenCollection(file_get_contents(__DIR__.'/../helpers/tests/TestOne.php'));
        $filteredAndMappedCollection = $finder->filterWhiteSpaceAndMapTokensToNames($collection);
        $this->assertInstanceOf(Collection::class, $filteredAndMappedCollection);
        $this->assertEquals("<?php\n", $filteredAndMappedCollection->first());
    }

    /** @test */
    public function it_finds_namespace_from_tokens_collection()
    {
        $finder = new TestsAnnotatedWithWatchFinder;
        $collection = $finder->convertContentsToTokenCollection(file_get_contents(__DIR__.'/../helpers/tests/TestOne.php'));
        $filteredAndMappedCollection = $finder->filterWhiteSpaceAndMapTokensToNames($collection);
        $namespace = $finder->findNameSpace($filteredAndMappedCollection);
        $this->assertEquals('WackyStudio\LaravelTestWatcher\Tests\helpers\tests', $namespace);
    }

    /** @test */
    public function it_finds_class_name_from_tokens_collection()
    {
        $finder = new TestsAnnotatedWithWatchFinder;
        $collection = $finder->convertContentsToTokenCollection(file_get_contents(__DIR__.'/../helpers/tests/TestOne.php'));
        $filteredAndMappedCollection = $finder->filterWhiteSpaceAndMapTokensToNames($collection);
        $className = $finder->findClassName($filteredAndMappedCollection);
        $this->assertEquals('TestOne', $className);
    }

    /** @test */
    public function it_finds_tests_annotated_with_a_watch_annotation()
    {
        $finder = new TestsAnnotatedWithWatchFinder;
        $collection = $finder->convertContentsToTokenCollection(file_get_contents(__DIR__.'/../helpers/tests/TestOne.php'));
        $filteredAndMappedCollection = $finder->filterWhiteSpaceAndMapTokensToNames($collection);
        $testMethod = $finder->findTestsAnnotatedWithWatch($filteredAndMappedCollection);
        $this->assertTrue(collect($testMethod)->contains('it_serves_as_a_fake_test_for_a_real_test'));
    }

    /** @test */
    public function it_finds_class_name_and_annotated_test_methods_and_returns_a_test_file_instance()
    {
        $finder = new TestsAnnotatedWithWatchFinder;
        $testFile = $finder->findAnnotatedTests(__DIR__.'/../helpers/tests/TestOne.php', file_get_contents(__DIR__.'/../helpers/tests/TestOne.php'));
        $this->assertInstanceOf(TestFileContract::class, $testFile);
        $this->assertEquals('TestOne', $testFile->getClassName());
        $this->assertTrue(collect($testFile->getMethodsToWatch())->contains('it_serves_as_a_fake_test_for_a_real_test'));
    }

    /** @test */
    public function if_not_provided_it_can_get_file_contents_itself()
    {
        $finder = new TestsAnnotatedWithWatchFinder;
        $testFile = $finder->findAnnotatedTests(__DIR__.'/../helpers/tests/TestOne.php');
        $this->assertInstanceOf(TestFileContract::class, $testFile);
        $this->assertEquals('TestOne', $testFile->getClassName());
        $this->assertTrue(collect($testFile->getMethodsToWatch())->contains('it_serves_as_a_fake_test_for_a_real_test'));
    }

    /** @test */
    public function it_returns_a_null_test_file_if_a_file_is_not_found()
    {
        $finder = new TestsAnnotatedWithWatchFinder;
        $testFile = $finder->findAnnotatedTests(__DIR__.'/../helpers/tests/ThisTestIsNotThere.php');
        $this->assertInstanceOf(TestFileContract::class, $testFile);
        $this->assertInstanceOf(InvalidTestFile::class, $testFile);
        $this->assertEquals('invalid', $testFile->getClassName());
        $this->assertFalse($testFile->hasAnyTests());
        $this->assertEquals(__DIR__.'/../helpers/tests/ThisTestIsNotThere.php', $testFile->getFilePath());
    }
}
