<?php

namespace Modules\RedmineIntegration\Providers;

use CatEvent;
use Filter;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Config;
use Modules\RedmineIntegration\Console\GenerateSignature;
use Modules\RedmineIntegration\Console\SynchronizeGroups;
use Modules\RedmineIntegration\Console\SynchronizePriorities;
use Modules\RedmineIntegration\Console\SynchronizeProjects;
use Modules\RedmineIntegration\Console\SynchronizeStatuses;
use Modules\RedmineIntegration\Console\SynchronizeTasks;
use Modules\RedmineIntegration\Console\SynchronizeTime;
use Modules\RedmineIntegration\Http\Middleware\ValidateSignature;
use Modules\RedmineIntegration\Subscribers\EventObserver;
use Modules\RedmineIntegration\Subscribers\FilterObserver;
use Redmine\Client\Client;
use Redmine\Client\NativeCurlClient;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * @var string $moduleName
     */
    protected string $moduleName = 'CattrRedmineIntegration';

    /**
     * @var string $moduleNameLower
     */
    protected string $moduleNameLower = 'redmineintegration';

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'backend/Database/Migrations'));
        $this->registerCommands();

        app('router')->aliasMiddleware(
            'redmineintegration.signature',
            ValidateSignature::class
        );

        parent::boot();
    }

    /**
     * Register translations.
     */
    public function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/' . $this->moduleNameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
        } else {
            $this->loadTranslationsFrom(module_path($this->moduleName, 'backend/Resources/lang'), $this->moduleNameLower);
        }
    }


    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->bind(Client::class, NativeCurlClient::class);
    }

    public static function registerEvents (): void
    {
        CatEvent::subscribe(EventObserver::class);
        Filter::subscribe(FilterObserver::class);
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $this->publishes([
            module_path($this->moduleName, 'backend/Config/config.php') => config_path($this->moduleNameLower . '.php'),
        ], 'config');
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'backend/Config/config.php'),
            $this->moduleNameLower
        );
    }

    protected function registerCommands(): void
    {
        //Register synchronize redmine tasks command
        $this->commands([
            SynchronizeTasks::class,
            SynchronizeStatuses::class,
            SynchronizePriorities::class,
            SynchronizeProjects::class,
            SynchronizeTime::class,
            SynchronizeGroups::class,
            GenerateSignature::class,
        ]);
    }
}
