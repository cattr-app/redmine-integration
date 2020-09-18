<?php

namespace Modules\RedmineIntegration\Services;

use Exception;
use Illuminate\Support\Facades\Auth;
use Modules\RedmineIntegration\Entities\RedmineInstance;
use Modules\RedmineIntegration\Entities\RedmineUser;
use Redmine\Client;

class RedmineUserService
{
    public function create(RedmineInstance $instance, array $data)
    {
        if ($data['auth_method'] === 'basic') {
            $client = new Client($instance->url, $data['auth_login'], $data['auth_password']);
        } elseif ($data['auth_method'] === 'api_key') {
            $client = new Client($instance->url, $data['auth_api_key']);
        } else {
            throw new Exception('Unknown auth_method');
        }

        $redmineResponse = $client->user->getCurrentUser();
        if (!isset($redmineResponse) || $redmineResponse === false) {
            throw new Exception('Invalid credentials');
        }

        $user = RedmineUser::updateOrCreate([
            'redmine_instance_id' => $data['redmine_instance_id'],
            'redmine_user_id' => $redmineResponse['user']['id'],
        ], [
            'api_key' => $redmineResponse['user']['api_key'],
            'user_id' => Auth::id(),
        ]);

        return $user;
    }
}
