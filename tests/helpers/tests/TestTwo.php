<?php

namespace WackyStudio\LaravelTestWatcher\Tests\helpers\tests;

use PHPUnit\Framework\TestCase;

class TestTwo extends TestCase
{
    /** @test */
    public function it_also_serves_as_a_fake_test_for_a_real_test()
    {
        $this->assertTrue(true);
    }
}