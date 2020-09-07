<?php

namespace Modules\RedmineIntegration\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\RedmineIntegration\Entities\RedmineInstance;
use Modules\RedmineIntegration\Exceptions\ClientFactoryException;
use Modules\RedmineIntegration\Services\RedmineProjectService;

class SynchronizeProjects extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'redmine:projects';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize projects from redmine.';

    protected RedmineProjectService $redmineProjectService;

    public function __construct(RedmineProjectService $redmineProjectService)
    {
        parent::__construct();

        $this->redmineProjectService = $redmineProjectService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        try {
            foreach (RedmineInstance::all() as $instance) {
                $this->redmineProjectService->synchronize($instance);
            }
        } catch (ClientFactoryException $e) {
            Log::info($e->getMessage());
        }
    }
}
