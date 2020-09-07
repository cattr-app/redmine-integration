<?php

namespace Modules\RedmineIntegration\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Modules\RedmineIntegration\Entities\RedmineInstance;
use Modules\RedmineIntegration\Services\RedminePriorityService;
use Modules\RedmineIntegration\Services\RedmineProjectService;
use Modules\RedmineIntegration\Services\RedmineStatusService;

class ScheduleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->booted(static function () {
            /** @var Schedule $schedule */
            $schedule = app(Schedule::class);
            $schedule->call(static function () {
                $minutes = (int) floor(time() / 60);
                $instances = RedmineInstance::where(['sync_type' => 'cron'])->get();
                foreach ($instances as $instance) {
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

                    if ($instance->project_sync_interval > 0 && $minutes % $instance->project_sync_interval === 0) {
                        /** @var RedmineProjectService $service */
                        $service = app(RedmineProjectService::class);
                        $service->synchronize($instance);
                    }

                    if ($instance->task_sync_interval > 0 && $minutes % $instance->task_sync_interval === 0) {
                        // TODO
                    }
                }
            })->everyMinute();
        });
    }
}
