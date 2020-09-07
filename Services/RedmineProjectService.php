<?php

namespace Modules\RedmineIntegration\Services;

use Exception;
use Modules\RedmineIntegration\Entities\RedmineInstance;
use Modules\RedmineIntegration\Entities\RedmineProject;
use Redmine\Client;

class RedmineProjectService
{
    protected function createRedmineProject(int $redmineInstanceId, int $redmineProjectId, string $name)
    {
        $existingRedmineProject = RedmineProject::where([
            'redmine_instance_id' => $redmineInstanceId,
            'redmine_project_id' => $redmineProjectId,
        ])->first();

        if (isset($existingRedmineProject)) {
            $existingRedmineProject->name = $name;
            $existingRedmineProject->save();
        } else {
            RedmineProject::create([
                'redmine_project_id' => $redmineProjectId,
                'name' => $name,
                'redmine_instance_id' => $redmineInstanceId,
                'project_id' => null,
            ]);
        }
    }

    public function synchronize(RedmineInstance $redmineInstance)
    {
        $client = new Client($redmineInstance->url, $redmineInstance->api_key);
        $response = $client->project->all();
        if (!isset($response['projects'])) {
            throw new Exception("Can't synchronize projects for " . $redmineInstance->url);
        }

        foreach ($response['projects'] as $project) {
            $this->createRedmineProject($redmineInstance->id, $project['id'], $project['name']);
        }
    }
}
