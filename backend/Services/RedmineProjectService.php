<?php

namespace Modules\RedmineIntegration\Services;

use App\Models\Project;
use Exception;
use Modules\RedmineIntegration\Entities\RedmineInstance;
use Modules\RedmineIntegration\Entities\RedmineProject;
use Redmine\Client\Client;
use RuntimeException;

class RedmineProjectService
{
    protected function createRedmineProject(
        int $redmineInstanceId,
        int $redmineProjectId,
        string $name,
        bool $createInternalProjects
    ) {
        $existingRedmineProject = RedmineProject::where([
            'redmine_instance_id' => $redmineInstanceId,
            'redmine_project_id' => $redmineProjectId,
        ])->first();

        if (isset($existingRedmineProject)) {
            // Do not replace mappings to deleted or missing Cattr projects.
            if ($createInternalProjects && $existingRedmineProject->project_id === null) {
                $project = Project::create([
                    'name' => $name,
                    'description' => $name,
                    'source' => 'redmine',
                ]);

                $existingRedmineProject->project_id = $project->id;
            }

            $existingRedmineProject->name = $name;
            $existingRedmineProject->save();
        } else {
            if ($createInternalProjects) {
                $project = Project::create([
                    'name' => $name,
                    'description' => $name,
                    'source' => 'redmine',
                ]);
            } else {
                $project = null;
            }

            RedmineProject::create([
                'redmine_project_id' => $redmineProjectId,
                'name' => $name,
                'redmine_instance_id' => $redmineInstanceId,
                'project_id' => isset($project) ? $project->id : null,
            ]);
        }
    }

    public function synchronize(RedmineInstance $redmineInstance, bool $createInternalProjects = true)
    {
        $client = app(Client::class, ['url' => $redmineInstance->url, 'apikeyOrUsername' => $redmineInstance->api_key]);

        // Explicit callers may disable creation (e.g. initial instance registration).
        // Otherwise, honor the instance setting regardless of sync mode.
        $createInternalProjects = $createInternalProjects && (bool) $redmineInstance->sync_new_projects;

        foreach (RedminePaginator::items(
            static fn (int $offset, int $limit): array => $client->getApi('project')->all([
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
