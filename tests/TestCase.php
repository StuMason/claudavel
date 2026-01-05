<?php

namespace Stumason\Claudavel\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Stumason\Claudavel\ClaudavelServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            ClaudavelServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Set up any environment configuration needed for tests
    }
}
