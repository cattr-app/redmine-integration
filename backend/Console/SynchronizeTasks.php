<?php

namespace Modules\RedmineIntegration\Console;

use Illuminate\Console\Command;
use Modules\RedmineIntegration\Entities\RedmineInstance;
use Modules\RedmineIntegration\Services\RedmineTaskService;

class SynchronizeTasks extends Command
{
    protected $name = 'redmine:tasks';
    protected $description = 'Synchronize tasks from redmine';

    public function handle(RedmineTaskService $redmineService): void
    {
        $failed = false;
        $this->withProgressBar(RedmineInstance::all(), function (RedmineInstance $instance) use ($redmineService, &$failed): void {
            try {
                $redmineService->synchronize($instance);
            } catch (\Throwable $exception) {
                $failed = true;
                report($exception);
                $this->newLine();
                $this->error("Redmine instance {$instance->id}: {$exception->getMessage()}");
            }
        });

        if ($failed) {
            $this->newLine();
            throw new \RuntimeException('One or more Redmine instances failed to synchronize.');
        }
    }
}
