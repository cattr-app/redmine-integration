<?php

namespace Modules\RedmineIntegration\Services;

use App\Models\Status;
use Modules\RedmineIntegration\Entities\RedmineInstance;
use Modules\RedmineIntegration\Entities\RedmineStatus;
use Redmine\Client\Client;
use RuntimeException;

class RedmineStatusService
{
    protected function updateStatus(int $redmineInstanceId, int $redmineStatusId, string $name, bool $isClosed): void
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

    public function synchronize(RedmineInstance $redmineInstance): void
    {
        $client = app(Client::class, ['url' => $redmineInstance->url, 'apikeyOrUsername' => $redmineInstance->api_key]);
        $response = $client->getApi('issue_status')->all();
        if (!is_array($response) || !isset($response['issue_statuses'])) {
            throw new RuntimeException('Invalid credentials or Redmine not available. Please contact your administrator');
        }

        foreach ($response['issue_statuses'] as $status) {
            $this->updateStatus($redmineInstance->id, $status['id'], $status['name'], $status['is_closed']);
        }
    }
}
