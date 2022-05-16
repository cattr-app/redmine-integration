<?php

namespace Modules\RedmineIntegration\Services;

use App\Models\Priority;
use Modules\RedmineIntegration\Entities\RedmineInstance;
use Modules\RedmineIntegration\Entities\RedminePriority;
use Redmine\Client\Client;
use RuntimeException;

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

    public function synchronize(RedmineInstance $redmineInstance): void
    {
        $client = app(Client::class, ['url' => $redmineInstance->url, 'apikeyOrUsername' => $redmineInstance->api_key]);
        $response = $client->getApi('issue_priority')->all();
        if (!is_array($response) || !isset($response['issue_priorities'])) {
            throw new RuntimeException('Invalid credentials or Redmine not available. Please contact your administrator');
        }

        foreach ($response['issue_priorities'] as $priority) {
            $this->updatePriority($redmineInstance->id, $priority['id'], $priority['name']);
        }
    }
}
