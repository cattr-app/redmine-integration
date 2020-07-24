<?php

namespace Modules\RedmineIntegration\Services;

use App\Models\Priority;
use Exception;
use Modules\RedmineIntegration\Entities\RedmineInstance;
use Modules\RedmineIntegration\Entities\RedminePriority;
use Redmine\Client;

class RedminePriorityService
{
    protected function updatePriority(int $redmineInstanceId, int $redmineProrityId, string $name)
    {
        $existingRedminePriority = RedminePriority::where([
            'redmine_instance_id' => $redmineInstanceId,
            'redmine_priority_id' => $redmineProrityId,
        ])->first();

        if (isset($existingRedminePriority)) {
            $existingRedminePriority->name = $name;
            $existingRedminePriority->save();
        } else {
            $internalPriority = Priority::find($redmineProrityId);
            $internalPriorityId = isset($internalPriority) ? $internalPriority->id : Priority::max('id');

            RedminePriority::create([
                'redmine_instance_id' => $redmineInstanceId,
                'redmine_priority_id' => $redmineProrityId,
                'name'                => $name,
                'priority_id'         => $internalPriorityId,
            ]);
        }
    }

    public function synchronize(RedmineInstance $redmineInstance)
    {
        $client = new Client($redmineInstance->url, $redmineInstance->api_key);
        $response = $client->issue_priority->all();
        if (!isset($response['issue_priorities'])) {
            throw new Exception("Can't synchronize priorities for " . $redmineInstance->url);
        }

        foreach ($response['issue_priorities'] as $priority) {
            $this->updatePriority($redmineInstance->id, $priority['id'], $priority['name']);
        }
    }
}
