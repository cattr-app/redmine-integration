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
        $this->withProgressBar(
            RedmineInstance::all(),
            static fn($instance) => rescue(static fn() => $redmineService->synchronize($instance))
        );
    }
}
