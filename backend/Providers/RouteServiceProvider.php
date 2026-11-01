<?php

namespace Modules\RedmineIntegration\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware('api')
            ->as('integration.redmine.')
            ->prefix('api/integration/redmine')
            ->group(__DIR__ . '/../Routes/api.php');
    }
}
