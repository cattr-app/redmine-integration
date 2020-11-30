<?php

namespace Modules\RedmineIntegration\Services;

use Exception;
use Modules\RedmineIntegration\Entities\RedmineInstance;
use Modules\RedmineIntegration\Entities\RedmineStatus;
use Redmine\Client;

class RedmineStatusService
{
    protected function updateStatus(int $redmineInstanceId, int $redmineStatusId, string $name, bool $isClosed)
    {
        RedmineStatus::updateOrCreate([
            'redmine_instance_id' => $redmineInstanceId,
            'redmine_status_id'   => $redmineStatusId,
        ], [
            'name'      => $name,
            'is_closed' => $isClosed,
        ]);
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
