<?php

namespace Modules\RedmineIntegration\Console;

use Illuminate\Console\Command;
use Modules\RedmineIntegration\Entities\RedmineInstance;
use Modules\RedmineIntegration\Services\RedmineTimeService;

class SynchronizeTime extends Command
{
    protected $name = 'redmine:time';
    protected $description = 'Synchronize time to redmine';

    public function handle(RedmineTimeService $redmineService): void
    {
        $this->withProgressBar(
            RedmineInstance::all(),
            static fn($instance) => rescue(static fn() => $redmineService->synchronize($instance))
        );
    }
}
