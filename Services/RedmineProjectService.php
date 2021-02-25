<?php

namespace Modules\RedmineIntegration\Services;

use App\Models\Project;
use Exception;
use Modules\RedmineIntegration\Entities\RedmineInstance;
use Modules\RedmineIntegration\Entities\RedmineProject;
use Redmine\Client;

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
            if ($createInternalProjects && !isset($existingRedmineProject->project)) {
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
        $client = new Client($redmineInstance->url, $redmineInstance->api_key);
        $response = $client->project->all(['limit' => 1]);
        if (empty($response['total_count'])) {
            return;
        }

        $count = $response['total_count'];
        $response = $client->project->all(['limit' => $count]);
        if (!is_array($response) || !isset($response['projects'])) {
            throw new Exception('Invalid credentials or Redmine not available. Please contact your administrator');
        }

        foreach ($response['projects'] as $project) {
            $createInternalProjects = $createInternalProjects && $redmineInstance->sync_type === 'cron';

            $this->createRedmineProject(
                $redmineInstance->id,
                $project['id'],
                $project['name'],
                $createInternalProjects
            );
        }
    }
}
