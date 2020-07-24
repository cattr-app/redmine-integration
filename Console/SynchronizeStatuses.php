<?php

namespace Modules\RedmineIntegration\Console;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\RedmineIntegration\Entities\RedmineInstance;
use Modules\RedmineIntegration\Exceptions\ClientFactoryException;
use Modules\RedmineIntegration\Services\RedmineStatusService;
use Modules\RedmineIntegration\Services\StatusService;

class SynchronizeStatuses extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'redmine:statuses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize statuses from redmine.';

    protected RedmineStatusService $redmineStatusService;

    /**
     * Create a new command instance.
     *
     * @param StatusService $status
     */
    public function __construct(RedmineStatusService $redmineStatusService)
    {
        parent::__construct();

        $this->redmineStatusService = $redmineStatusService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        try {
            foreach (RedmineInstance::all() as $instance) {
                $this->redmineStatusService->synchronize($instance);
            }
        } catch (ClientFactoryException $e) {
            Log::info($e->getMessage());
        }
    }
}
