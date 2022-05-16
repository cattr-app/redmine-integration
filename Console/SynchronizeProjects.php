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
        $this->withProgressBar(
            RedmineInstance::all(),
            static fn($instance) => rescue(static fn() => $redmineService->synchronize($instance))
        );
    }
}
