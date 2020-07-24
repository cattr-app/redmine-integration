<?php

namespace Modules\RedmineIntegration\Console;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\RedmineIntegration\Entities\RedmineInstance;
use Modules\RedmineIntegration\Exceptions\ClientFactoryException;
use Modules\RedmineIntegration\Services\RedminePriorityService;

class SynchronizePriorities extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'redmine:priorities';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize priorities from redmine.';

    protected RedminePriorityService $redminePriorityService;

    /**
     * Create a new command instance.
     */
    public function __construct(RedminePriorityService $redminePriorityService)
    {
        parent::__construct();

        $this->redminePriorityService = $redminePriorityService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        try {
            foreach (RedmineInstance::all() as $instance) {
                $this->redminePriorityService->synchronize($instance);
            }
        } catch (ClientFactoryException $e) {
            Log::info($e->getMessage());
        }
    }
}
