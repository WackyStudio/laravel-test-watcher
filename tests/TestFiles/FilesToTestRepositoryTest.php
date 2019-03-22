<?php

namespace WackyStudio\LaravelTestWatcher\Tests\TestFiles;

use Orchestra\Testbench\TestCase;
use WackyStudio\LaravelTestWatcher\Contracts\AnnotatedTestsFinderContract;
use WackyStudio\LaravelTestWatcher\Facades\LaravelTestWatcher as LaravelTestWatcherFacade;
use WackyStudio\LaravelTestWatcher\Factories\LaravelTestWatcherFactory;
use WackyStudio\LaravelTestWatcher\LaravelTestWatcherServiceProvider;
use WackyStudio\LaravelTestWatcher\TestFiles\FilesToTestRepository;
use WackyStudio\LaravelTestWatcher\TestFiles\TestFile;

class FilesToTestRepositoryTest extends TestCase
{

    protected function getPackageProviders($app)
    {
        return [LaravelTestWatcherServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'LaravelTestWatcher' => LaravelTestWatcherFacade::class
        ];
    }

    protected function getBasePath()
    {
        return __DIR__.'/../helpers';
    }

    protected function tearDown(): void
    {
        if(file_exists(__DIR__.'/../helpers/tests/TestThree.php')){
            unlink(__DIR__.'/../helpers/tests/TestThree.php');
        }
        parent::tearDown();
    }

    /** @test */
    public function when_updating_collection_it_calls_annotated_test_watcher_to_sort_files_with_annotated_tests()
    {
        $testOneTestFile = new TestFile(
            __DIR__.'/../helpers/tests/TestOne.php',
            'TestOne',
            ['it_serves_as_a_fake_test_for_a_real_test'],
            'WackyStudio\LaravelTestWatcher\Tests\helpers\tests'
        );
        $testTwoTestFile = new TestFile(
            __DIR__.'/../helpers/tests/TestTwo.php','TestTwo',
            ['it_also_serves_as_a_fake_test_for_a_real_test'],
            'WackyStudio\LaravelTestWatcher\Tests\helpers\tests'
        );

        $this->assertTrue($testOneTestFile->hasAnyTests());
        $this->assertTrue($testTwoTestFile->hasAnyTests());

        $testFinder = \Mockery::mock(AnnotatedTestsFinderContract::class);
        $testFinder->shouldReceive('findAnnotatedTests')
                   ->with(__DIR__.'/../helpers/tests/TestOne.php')
                   ->andReturn($testOneTestFile);
        $testFinder->shouldReceive('findAnnotatedTests')
                   ->with(__DIR__.'/../helpers/tests/TestTwo.php')
                   ->andReturn($testTwoTestFile);

        $this->app->instance(AnnotatedTestsFinderContract::class, $testFinder);

        $files = [
            __DIR__.'/../helpers/tests/TestOne.php',
            __DIR__.'/../helpers/tests/TestTwo.php'
        ];

        /** @var FilesToTestRepository $repository */
        $repository = app(FilesToTestRepository::class);
        $repository->update($files);
        $filesToTest = $repository->getFilesToTest();

        $this->assertEquals($filesToTest[0], $testOneTestFile);
        $this->assertEquals($filesToTest[1], $testTwoTestFile);

    }

    /** @test */
    public function it_updates_files_to_test_collection_and_makes_sure_only_to_add_files_that_are_annotated_with_watch()
    {
        /** @var FilesToTestRepository $repository */
        $repository = app(FilesToTestRepository::class);
        $repository->update([
            __DIR__.'/../helpers/tests/TestOne.php',
            __DIR__.'/../helpers/tests/TestTwo.php',
        ]);

        $filesToTest = $repository->getFilesToTest();
        $this->assertEquals(1, $filesToTest->count());
        $this->assertEquals( __DIR__.'/../helpers/tests/TestOne.php', $filesToTest->first()->getFilePath());
    }
    
    /** @test */
    public function it_updates_files_to_test_collection_and_removes_files_that_no_longer_have_any_annotated_tests()
    {
        file_put_contents( __DIR__.'/../helpers/tests/TestThree.php', file_get_contents(__DIR__.'/../helpers/tests/stubs/testWithWatch.stub'));

        /** @var FilesToTestRepository $repository */
        $repository = app(FilesToTestRepository::class);
        $repository->update([
            __DIR__.'/../helpers/tests/TestOne.php',
            __DIR__.'/../helpers/tests/TestTwo.php',
            __DIR__.'/../helpers/tests/TestThree.php',
        ]);

        $filesToTest = $repository->getFilesToTest();
        $this->assertEquals(2, $filesToTest->count());
        $this->assertEquals( __DIR__.'/../helpers/tests/TestOne.php', $filesToTest->get(0)->getFilePath());
        $this->assertEquals( __DIR__.'/../helpers/tests/TestThree.php', $filesToTest->get(1)->getFilePath());

        file_put_contents( __DIR__.'/../helpers/tests/TestThree.php', file_get_contents(__DIR__.'/../helpers/tests/stubs/testWithoutWatch.stub'));

        $repository->update([
            __DIR__.'/../helpers/tests/TestOne.php',
            __DIR__.'/../helpers/tests/TestTwo.php',
            __DIR__.'/../helpers/tests/TestThree.php',
        ]);

        $filesToTest = $repository->getFilesToTest();
        $this->assertEquals(1, $filesToTest->count());
        $this->assertEquals( __DIR__.'/../helpers/tests/TestOne.php', $filesToTest->get(0)->getFilePath());
        $this->assertNull($filesToTest->get(1));
    }
    
    /** @test */
    public function it_compares_old_collection_to_new_collection_to_determine_which_files_are_new()
    {
        file_put_contents( __DIR__.'/../helpers/tests/TestThree.php', file_get_contents(__DIR__.'/../helpers/tests/stubs/testWithTwoWatchedMethods.stub'));

        $expectedChanges = [
            'added' => [
                [
                    'file' => 'WackyStudio\LaravelTestWatcher\Tests\helpers\tests\TestOne',
                    'methods' => [
                        'it_serves_as_a_fake_test_for_a_real_test'
                    ]
                ],
                [
                    'file' => 'WackyStudio\LaravelTestWatcher\Tests\helpers\tests\TestThree',
                    'methods' => [
                        'it_serves_as_a_fake_test_for_a_real_test',
                        'it_also_serves_as_a_fake_test'
                    ]
                ]
            ],
            'updated' => [],
            'removed' => [],
        ];

        /** @var FilesToTestRepository $repository */
        $repository = app(FilesToTestRepository::class);
        $repository->update([
            __DIR__.'/../helpers/tests/TestOne.php',
            __DIR__.'/../helpers/tests/TestThree.php',
        ]);

        $changes = $repository->getChanges();
        $this->assertEquals($expectedChanges, $changes);
    }

    /** @test */
    public function it_compares_old_collection_to_new_collection_to_determine_which_files_are_updated()
    {
        file_put_contents( __DIR__.'/../helpers/tests/TestThree.php', file_get_contents(__DIR__.'/../helpers/tests/stubs/testWithTwoWatchedMethods.stub'));

        $expectedChanges = [
            'added' => [],
            'updated' => [
                [
                    'file' => 'WackyStudio\LaravelTestWatcher\Tests\helpers\tests\TestThree',
                    'methods' => [
                        'it_serves_as_a_fake_test_for_a_real_test',
                    ],
                    'droppedMethods' => [
                        'it_also_serves_as_a_fake_test'
                    ]
                ]
            ],
            'removed' => [],
        ];

        /** @var FilesToTestRepository $repository */
        $repository = app(FilesToTestRepository::class);
        $repository->update([
            __DIR__.'/../helpers/tests/TestOne.php',
            __DIR__.'/../helpers/tests/TestThree.php',
        ]);

        file_put_contents( __DIR__.'/../helpers/tests/TestThree.php', file_get_contents(__DIR__.'/../helpers/tests/stubs/testWithOneWatchOneUnwatched.stub'));

        $repository->update([
            __DIR__.'/../helpers/tests/TestThree.php',
        ]);

        $changes = $repository->getChanges();
        $this->assertEquals($expectedChanges, $changes);
    }

    /** @test */
    public function it_compares_old_collection_to_new_collection_to_determine_which_files_are_removed()
    {
        file_put_contents( __DIR__.'/../helpers/tests/TestThree.php', file_get_contents(__DIR__.'/../helpers/tests/stubs/testWithTwoWatchedMethods.stub'));

        $expectedChanges = [
            'added' => [],
            'updated' =>[],
            'removed' =>  [
                [
                    'file' => 'WackyStudio\LaravelTestWatcher\Tests\helpers\tests\TestThree',
                    'methods' => [
                        'it_serves_as_a_fake_test_for_a_real_test',
                        'it_also_serves_as_a_fake_test'
                    ],
                ]
            ],
        ];

        /** @var FilesToTestRepository $repository */
        $repository = app(FilesToTestRepository::class);
        $repository->update([
            __DIR__.'/../helpers/tests/TestOne.php',
            __DIR__.'/../helpers/tests/TestThree.php',
        ]);

        unlink( __DIR__.'/../helpers/tests/TestThree.php');

        $repository->update([
            __DIR__.'/../helpers/tests/TestThree.php',
        ]);

        $changes = $repository->getChanges();
        $this->assertEquals($expectedChanges, $changes);
    }



}