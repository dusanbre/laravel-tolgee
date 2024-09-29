<?php

namespace LaravelTolgee\Tests;

use LaravelTolgee\LaravelTolgeeServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
    }

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelTolgeeServiceProvider::class,
        ];
    }
}
