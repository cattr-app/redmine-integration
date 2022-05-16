<?php

namespace Modules\RedmineIntegration\Console;

use Illuminate\Console\Command;
use Modules\RedmineIntegration\Entities\RedmineInstance;
use Modules\RedmineIntegration\Services\RedminePriorityService;

class SynchronizePriorities extends Command
{
    protected $name = 'redmine:priorities';
    protected $description = 'Synchronize priorities from redmine';

    public function handle(RedminePriorityService $redmineService): void
    {
        $this->withProgressBar(
            RedmineInstance::all(),
            static fn($instance) => rescue(static fn() => $redmineService->synchronize($instance))
        );
    }
}
