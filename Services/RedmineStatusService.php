<?php

namespace Modules\RedmineIntegration\Services;

use App\Models\Status;
use Exception;
use Modules\RedmineIntegration\Entities\RedmineInstance;
use Modules\RedmineIntegration\Entities\RedmineStatus;
use Redmine\Client;

class RedmineStatusService
{
    protected function updateStatus(int $redmineInstanceId, int $redmineStatusId, string $name, bool $isClosed)
    {
        $existingRedmineStatus = RedmineStatus::where([
            'redmine_instance_id' => $redmineInstanceId,
            'redmine_status_id'   => $redmineStatusId,
        ])->first();

        if (isset($existingRedmineStatus)) {
            $existingRedmineStatus->name = $name;
            $existingRedmineStatus->is_closed = $isClosed;
            $existingRedmineStatus->save();
        } else {
            $internalStatusId = Status::min('id');

            RedmineStatus::create([
                'redmine_instance_id' => $redmineInstanceId,
                'redmine_status_id'   => $redmineStatusId,
                'name'                => $name,
                'is_closed'           => $isClosed,
                'status_id'           => $internalStatusId,
            ]);
        }
    }

    public function synchronize(RedmineInstance $redmineInstance)
    {
        $client = new Client($redmineInstance->url, $redmineInstance->api_key);
        $response = $client->issue_status->all();
        if (!is_array($response) || !isset($response['issue_statuses'])) {
            throw new Exception('Invalid credentials or Redmine not available. Please contact your administrator');
        }

        foreach ($response['issue_statuses'] as $status) {
            $this->updateStatus($redmineInstance->id, $status['id'], $status['name'], $status['is_closed']);
        }
    }
}
