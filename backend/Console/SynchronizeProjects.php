<?php

namespace Modules\RedmineIntegration\Console;

use Illuminate\Console\Command;
use Modules\RedmineIntegration\Entities\RedmineInstance;
use Modules\RedmineIntegration\Services\RedmineProjectService;

class SynchronizeProjects extends Command
{
    protected $name = 'redmine:projects';
    protected $description = 'Synchronize projects from redmine';

    public function handle(RedmineProjectService $redmineService): void
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
