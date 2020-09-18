<?php

namespace Modules\RedmineIntegration\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\RedmineIntegration\Entities\RedmineInstance;
use Modules\RedmineIntegration\Exceptions\ClientFactoryException;
use Modules\RedmineIntegration\Services\RedmineTaskService;

/**
 * Class SynchronizeTasks
 */
class SynchronizeTasks extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'redmine:tasks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize tasks from redmine.';

    protected RedmineTaskService $redmineTaskService;

    /**
     * Create a new command instance.
     *
     * SynchronizeTasks constructor.
     */
    public function __construct(RedmineTaskService $redmineTaskService)
    {
        parent::__construct();

        $this->redmineTaskService = $redmineTaskService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        try {
            foreach (RedmineInstance::all() as $instance) {
                $this->redmineTaskService->synchronize($instance);
            }
        } catch (ClientFactoryException $e) {
            Log::info($e->getMessage());
        }
    }
}
