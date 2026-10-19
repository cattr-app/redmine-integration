<?php

namespace Modules\RedmineIntegration\Services;

use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Modules\RedmineIntegration\Entities\RedmineInstance;
use Modules\RedmineIntegration\Entities\RedmineProject;
use Redmine\Client\Client;

class RedmineProjectService
{
    /**
     * Create or refresh a project mapping without overwriting manual unlinks
     * or links to deleted projects. Lock existing records during linking.
     */
    protected function createRedmineProject(
        int $redmineInstanceId,
        int $redmineProjectId,
        string $name,
        bool $createInternalProjects
    ): void {
        DB::transaction(function () use ($redmineInstanceId, $redmineProjectId, $name, $createInternalProjects): void {
            // Lock in the same order as webhook synchronization to avoid deadlocks.
            RedmineInstance::whereKey($redmineInstanceId)->lockForUpdate()->firstOrFail();
            $mapping = RedmineProject::where([
                'redmine_instance_id' => $redmineInstanceId,
                'redmine_project_id' => $redmineProjectId,
            ])->lockForUpdate()->first();

            if (!$mapping) {
                $mapping = new RedmineProject([
                    'redmine_instance_id' => $redmineInstanceId,
                    'redmine_project_id' => $redmineProjectId,
                    'manual_unlinked' => false,
                ]);
            }

            $mapping->name = $name;
            if ($createInternalProjects && $mapping->project_id === null && $mapping->manual_unlinked === false) {
                $project = Project::create([
                    'name' => $name,
                    'description' => $name,
                    'source' => 'redmine',
                ]);
                $mapping->project_id = $project->id;
            }
            $mapping->save();
        });
    }

    /** Synchronize all project pages and honor cron's automatic import behavior. */
    public function synchronize(RedmineInstance $redmineInstance, bool $createInternalProjects = true): void
    {
        $client = app(Client::class, ['url' => $redmineInstance->url, 'apikeyOrUsername' => $redmineInstance->api_key]);
        $createInternalProjects = $createInternalProjects
            && ($redmineInstance->sync_type === 'cron' || (bool) $redmineInstance->sync_new_projects);

        foreach (RedminePaginator::items(
            static fn (int $offset, int $limit) => $client->getApi('project')->all([
                'offset' => $offset,
                'limit' => $limit,
            ]),
            'projects'
        ) as $project) {
            $this->createRedmineProject(
                $redmineInstance->id,
                $project['id'],
                $project['name'],
                $createInternalProjects
            );
        }
    }
}
