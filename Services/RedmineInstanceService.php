<?php

namespace Modules\RedmineIntegration\Services;

use Exception;
use Modules\RedmineIntegration\Entities\RedmineInstance;
use Redmine\Client;

class RedmineInstanceService
{
    protected RedminePriorityService $redminePriorityService;
    protected RedmineStatusService $redmineStatusService;
    protected RedmineProjectService $redmineProjectService;

    public function __construct(
        RedminePriorityService $redminePriorityService,
        RedmineStatusService $redmineStatusService,
        RedmineProjectService $redmineProjectService
    ) {
        $this->redminePriorityService = $redminePriorityService;
        $this->redmineStatusService = $redmineStatusService;
        $this->redmineProjectService = $redmineProjectService;
    }

    public function create(array $data): RedmineInstance
    {
        if ($data['auth_method'] === 'basic') {
            $client = new Client($data['url'], $data['auth_login'], $data['auth_password']);
        } elseif ($data['auth_method'] === 'api_key') {
            $client = new Client($data['url'], $data['auth_api_key']);
        } else {
            throw new Exception('Unknown auth_method');
        }

        $redmineResponse = $client->user->getCurrentUser();
        if (!isset($redmineResponse)) {
            throw new Exception('Invalid credentials');
        }

        unset($data['auth_method']);
        unset($data['auth_login']);
        unset($data['auth_password']);
        unset($data['auth_api_key']);

        $data['api_key'] = $redmineResponse['user']['api_key'];

        $instance = RedmineInstance::create($data);
        $this->redminePriorityService->synchronize($instance);
        $this->redmineStatusService->synchronize($instance);
        $this->redmineProjectService->synchronize($instance, false);

        return $instance;
    }
}
