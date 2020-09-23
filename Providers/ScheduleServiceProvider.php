<?php

namespace Modules\RedmineIntegration\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Modules\RedmineIntegration\Entities\RedmineInstance;
use Modules\RedmineIntegration\Services\RedminePriorityService;
use Modules\RedmineIntegration\Services\RedmineProjectService;
use Modules\RedmineIntegration\Services\RedmineStatusService;
use Modules\RedmineIntegration\Services\RedmineTaskService;
use Modules\RedmineIntegration\Services\RedmineTimeService;

class ScheduleServiceProvider extends ServiceProvider
{
    const TIME_SYNC_INTERVAL = 5;

    public function boot(): void
    {
        $this->app->booted(static function () {
            /** @var Schedule $schedule */
            $schedule = app(Schedule::class);
            $schedule->call(static function () {
                $minutes = (int) floor(time() / 60);
                $cronInstances = RedmineInstance::where(['sync_type' => 'cron'])->get();
                foreach ($cronInstances as $instance) {
                    if ($instance->project_sync_interval > 0 && $minutes % $instance->project_sync_interval === 0) {
                        /** @var RedmineProjectService $service */
                        $service = app(RedmineProjectService::class);
                        $service->synchronize($instance);
                    }

                    if ($instance->task_sync_interval > 0 && $minutes % $instance->task_sync_interval === 0) {
                        /** @var RedmineTaskService $service */
                        $service = app(RedmineTaskService::class);
                        $service->synchronize($instance);
                    }
                }

                $allInstances = RedmineInstance::all();
                foreach ($allInstances as $instance) {
                    /** @var RedmineInstance $instance */
                    if ($instance->priority_sync_interval > 0 && $minutes % $instance->priority_sync_interval === 0) {
                        /** @var RedminePriorityService $service */
                        $service = app(RedminePriorityService::class);
                        $service->synchronize($instance);
                    }

                    if ($instance->status_sync_interval > 0 && $minutes % $instance->status_sync_interval === 0) {
                        /** @var RedmineStatusService $service */
                        $service = app(RedmineStatusService::class);
                        $service->synchronize($instance);
                    }

                    if ($minutes % static::TIME_SYNC_INTERVAL === 0) {
                        /** @var RedmineTimeService $service */
                        $service = app(RedmineTimeService::class);
                        $service->synchronize($instance);
                    }
                }
            })->everyMinute();
        });
    }
}
