<?php

namespace Modules\RedmineIntegration\Services;

use Exception;
use Modules\RedmineIntegration\Entities\RedmineGroup;
use Modules\RedmineIntegration\Entities\RedmineInstance;
use Modules\RedmineIntegration\Entities\RedmineUser;
use Redmine\Client\Client;

class RedmineGroupService
{
    public function synchronizeAll(RedmineInstance $redmineInstance)
    {
        $client = app(Client::class, ['url' => $redmineInstance->url, 'apikeyOrUsername' => $redmineInstance->api_key]);
        $response = $client->getApi('group')->all();
        if (!is_array($response)) {
            throw new Exception('Invalid credentials or Redmine not available. Please contact your administrator');
        }

        foreach ($response['groups'] as $group) {
            $this->synchronize($redmineInstance, $group['id']);
        }
    }

    public function synchronize(RedmineInstance $redmineInstance, int $groupId)
    {
        $client = app(Client::class, ['url' => $redmineInstance->url, 'apikeyOrUsername' => $redmineInstance->api_key]);
        $response = $client->getApi('group')->show($groupId, ['include' => 'users']);
        if (!is_array($response)) {
            throw new Exception('Invalid credentials or Redmine not available. Please contact your administrator');
        }

        $redmineUserIds = [];
        foreach ($response['group']['users'] as $user) {
            $redmineUserIds[] = $user['id'];
        }

        $userIds = RedmineUser::whereIn('redmine_user_id', $redmineUserIds)->pluck('id');
        $group = RedmineGroup::updateOrCreate([
            'redmine_group_id' => $response['group']['id'],
            'redmine_instance_id' => $redmineInstance->id,
        ], []);

        $group->users()->sync($userIds);
    }
}
