<?php

namespace Modules\RedmineIntegration\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\RedmineIntegration\Entities\RedmineInstance;
use Modules\RedmineIntegration\Exceptions\ClientFactoryException;
use Modules\RedmineIntegration\Services\RedmineTimeService;

/**
 * Class SynchronizeTime
 */
class SynchronizeTime extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'redmine:time';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize time to redmine.';

    protected RedmineTimeService $redmineTimeService;

    /**
     * Create a new command instance.
     *
     * SynchronizeTasks constructor.
     */
    public function __construct(RedmineTimeService $redmineTimeService)
    {
        parent::__construct();

        $this->redmineTimeService = $redmineTimeService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        try {
            foreach (RedmineInstance::all() as $instance) {
                $this->redmineTimeService->synchronize($instance);
            }
        } catch (ClientFactoryException $e) {
            Log::info($e->getMessage());
        }
    }
}
