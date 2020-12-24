<?php

namespace Modules\RedmineIntegration\Providers;

use App\EventFilter\EventServiceProvider as ServiceProvider;
use Config;
use Modules\RedmineIntegration\Console\GenerateSignature;
use Modules\RedmineIntegration\Console\SynchronizeGroups;
use Modules\RedmineIntegration\Console\SynchronizePriorities;
use Modules\RedmineIntegration\Console\SynchronizeProjects;
use Modules\RedmineIntegration\Console\SynchronizeStatuses;
use Modules\RedmineIntegration\Console\SynchronizeTasks;
use Modules\RedmineIntegration\Console\SynchronizeTime;
use Modules\RedmineIntegration\Http\Middleware\ValidateSignature;

class RedmineIntegrationServiceProvider extends ServiceProvider
{
    /**
     * @var string $moduleName
     */
    protected string $moduleName = 'RedmineIntegration';

    /**
     * @var string $moduleNameLower
     */
    protected string $moduleNameLower = 'redmineintegration';

    /**
     * @var array
     */
    protected $listen = [
        'item.create.task' => [
            'Modules\RedmineIntegration\Listeners\IntegrationObserver@taskCreation',
        ],
        'item.edit.task' => [
            'Modules\RedmineIntegration\Listeners\IntegrationObserver@taskEdition',
        ],
        'item.remove.task' => [
            'Modules\RedmineIntegration\Listeners\IntegrationObserver@taskDeletion',
        ],
        'answer.success.item.list.result.task' => [
            'Modules\RedmineIntegration\Listeners\IntegrationObserver@taskList',
        ],
        'answer.success.item.show.user' => [
            'Modules\RedmineIntegration\Listeners\IntegrationObserver@userShow',
        ],
        'answer.success.item.edit.user' => [
            'Modules\RedmineIntegration\Listeners\IntegrationObserver@userAfterEdition',
        ],
        'answer.success.item.create.user' => [
            'Modules\RedmineIntegration\Listeners\IntegrationObserver@userAfterEdition',
        ],
        'role.actions.list' => [
            'Modules\RedmineIntegration\Listeners\IntegrationObserver@rulesHook',
        ],
        'answer.success.item.create.timeinterval' => [
            'Modules\RedmineIntegration\Listeners\IntegrationObserver@timeIntervalCreation',
        ],
    ];

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
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
            $this->loadTranslationsFrom(module_path($this->moduleName, 'Resources/lang'), $this->moduleNameLower);
        }
    }


    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->register(ScheduleServiceProvider::class);
        $this->app->register(RouteServiceProvider::class);
        $this->app->register(BroadcastServiceProvider::class);
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $this->publishes([
            module_path($this->moduleName, 'Config/config.php') => config_path($this->moduleNameLower . '.php'),
        ], 'config');
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/config.php'),
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

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (Config::get('view.paths') as $path) {
            if (is_dir($path . '/modules/' . $this->moduleNameLower)) {
                $paths[] = $path . '/modules/' . $this->moduleNameLower;
            }
        }
        return $paths;
    }
}
