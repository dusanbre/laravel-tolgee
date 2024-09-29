<?php

namespace Workbench\App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class WorkbenchServiceProvider extends ServiceProvider
{
    public function register()
    {
    }

    public function boot()
    {
        Route::get('/', 'welcome');
    }
}
