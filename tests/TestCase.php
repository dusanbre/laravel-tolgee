<?php

namespace LaravelTolgee\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use LaravelTolgee\LaravelTolgeeServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Dusan Antonijevic\\LaravelTolgee\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelTolgeeServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        /*
        $migration = include __DIR__.'/../database/migrations/create_laravel-tolgee_table.php.stub';
        $migration->up();
        */
    }
}
