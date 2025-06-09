<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Suppress deprecation warnings from third-party packages
        error_reporting(E_ALL & ~E_DEPRECATED);
    }
}
