<?php

namespace LaravelTolgee;

use LaravelTolgee\Commands\DeleteAllKeysCommand;
use LaravelTolgee\Commands\ImportKeysCommand;
use LaravelTolgee\Commands\SyncTranslationsCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelTolgeeServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-tolgee')
            ->hasConfigFile('tolgee')
            ->hasCommands([
                ImportKeysCommand::class,
                DeleteAllKeysCommand::class,
                SyncTranslationsCommand::class
            ]);
    }

}
