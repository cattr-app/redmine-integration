<?php

namespace Modules\RedmineIntegration\Console;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\RedmineIntegration\Entities\RedmineInstance;
use Modules\RedmineIntegration\Exceptions\ClientFactoryException;
use Modules\RedmineIntegration\Services\RedmineStatusService;

class SynchronizeStatuses extends Command
{
    protected $name = 'redmine:statuses';
    protected $description = 'Synchronize statuses from redmine';

    public function handle(RedmineStatusService $redmineService): void
    {
        $this->withProgressBar(
            RedmineInstance::all(),
            static fn($instance) => rescue(static fn() => $redmineService->synchronize($instance))
        );
    }
}
