<?php

namespace Modules\RedmineIntegration\Services;

use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
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
        bool $createInternalProjects,
        string $syncType
    ): void {
        DB::transaction(function () use ($redmineInstanceId, $redmineProjectId, $name, $createInternalProjects, $syncType): void {
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
            if (RedmineProjectLinkPolicy::shouldAutoLink(
                $mapping->project_id,
                $mapping->manual_unlinked,
                $syncType,
                $createInternalProjects
            )) {
                $mapping->project_id = $this->createRedmineSourcedProject($name, $name)->id;
            }
            $mapping->save();
        });
    }

    /** Create a Cattr project owned by the Redmine integration. */
    private function createRedmineSourcedProject(string $name, string $description): Project
    {
        return Project::create([
            'name' => $name,
            'description' => $description,
            'source' => 'redmine',
        ]);
    }

    /**
     * Create a Redmine-sourced Cattr project for an unlinked mapping and link
     * it in the same transaction. This is an explicit administrative link, so
     * it clears a previous manual unlink.
     *
     * @throws ValidationException when the mapping is already linked
     */
    public function createLinkedProject(
        int $redmineInstanceId,
        int $id,
        ?string $name = null,
        ?string $description = null
    ): RedmineProject {
        return DB::transaction(function () use ($redmineInstanceId, $id, $name, $description): RedmineProject {
            // Lock in the same order as synchronization to avoid deadlocks.
            RedmineInstance::whereKey($redmineInstanceId)->lockForUpdate()->firstOrFail();
            $mapping = RedmineProject::where([
                'redmine_instance_id' => $redmineInstanceId,
                'id' => $id,
            ])->lockForUpdate()->firstOrFail();

            if ($mapping->project_id !== null) {
                throw ValidationException::withMessages([
                    'project_id' => 'The Redmine project is already linked to a Cattr project.',
                ]);
            }

            $name = $name ?? $mapping->name;
            $mapping->project_id = $this->createRedmineSourcedProject($name, $description ?? $name)->id;
            $mapping->manual_unlinked = false;
            $mapping->save();

            return $mapping;
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
                $createInternalProjects,
                $redmineInstance->sync_type
            );
        }
    }
}
