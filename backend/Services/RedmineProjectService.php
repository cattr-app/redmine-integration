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
        $response = $client->getApi('project')->all(['limit' => 1]);
        if (empty($response['total_count'])) {
            return;
        }

        $count = $response['total_count'];
        $response = $client->getApi('project')->all(['limit' => $count]);
        if (!is_array($response) || !isset($response['projects'])) {
            throw new RuntimeException('Invalid credentials or Redmine not available. Please contact your administrator');
        }

        // Explicit callers may disable creation (e.g. initial instance registration).
        // Otherwise, honor the instance setting regardless of sync mode.
        $createInternalProjects = $createInternalProjects && (bool) $redmineInstance->sync_new_projects;

        foreach ($response['projects'] as $project) {

            $this->createRedmineProject(
                $redmineInstance->id,
                $project['id'],
                $project['name'],
                $createInternalProjects
            );
        }
    }
}
