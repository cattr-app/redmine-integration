<?php

namespace Modules\RedmineIntegration\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\RedmineIntegration\Entities\RedmineInstance;
use Modules\RedmineIntegration\Exceptions\ClientFactoryException;
use Modules\RedmineIntegration\Services\RedmineGroupService;

class SynchronizeGroups extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'redmine:groups';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize groups from redmine.';

    protected RedmineGroupService $redmineGroupService;

    /**
     * Create a new command instance.
     */
    public function __construct(RedmineGroupService $redmineGroupService)
    {
        parent::__construct();

        $this->redmineGroupService = $redmineGroupService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        try {
            foreach (RedmineInstance::all() as $instance) {
                $this->redmineGroupService->synchronizeAll($instance);
            }
        } catch (ClientFactoryException $e) {
            Log::info($e->getMessage());
        }
    }
}
