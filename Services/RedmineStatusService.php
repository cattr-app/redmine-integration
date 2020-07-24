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
        if (!isset($response['issue_statuses'])) {
            throw new Exception("Can't synchronize statuses for " . $redmineInstance->url);
        }

        foreach ($response['issue_statuses'] as $status) {
            $this->updateStatus($redmineInstance->id, $status['id'], $status['name'], $status['is_closed']);
        }
    }
}
