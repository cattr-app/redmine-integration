<?php

namespace Modules\RedmineIntegration\Console;

use Illuminate\Console\Command;
use Modules\RedmineIntegration\Entities\RedmineInstance;
use Modules\RedmineIntegration\Services\RedmineGroupService;

class SynchronizeGroups extends Command
{
    protected $name = 'redmine:groups';
    protected $description = 'Synchronize groups from redmine';

    public function handle(RedmineGroupService $redmineService): void
    {
        $this->withProgressBar(
            RedmineInstance::all(),
            static fn($instance) => rescue(static fn() => $redmineService->synchronizeAll($instance))
        );
    }
}
