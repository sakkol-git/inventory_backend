<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Ensure the API guard is the default for Spatie permission checks
        app('config')->set('auth.defaults.guard', 'api');
    }
}
