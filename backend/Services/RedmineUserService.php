<?php

namespace Modules\RedmineIntegration\Services;

use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Modules\RedmineIntegration\Entities\RedmineInstance;
use Modules\RedmineIntegration\Entities\RedmineUser;
use Modules\RedmineIntegration\Notifications\Unauthorized;
use Redmine\Client\Client;
use RuntimeException;

class RedmineUserService
{
    public function create(RedmineInstance $instance, array $data): Model|RedmineUser
    {
        if ($data['auth_method'] === 'basic') {
            $client = app(Client::class, ['url' => $instance->url, 'apikeyOrUsername' => $data['auth_login'], 'password' => $data['auth_password']]);
        } elseif ($data['auth_method'] === 'api_key') {
            $client = app(Client::class, ['url' => $instance->url, 'apikeyOrUsername' => $data['auth_api_key']]);
        } else {
            throw new RuntimeException('Unknown auth_method');
        }

        $redmineResponse = $client->getApi('user')->getCurrentUser();
        if (!isset($redmineResponse) || !is_array($redmineResponse)) {
            throw new RuntimeException('Invalid credentials or Redmine not available. Please contact your administrator');
        }

        return RedmineUser::updateOrCreate([
            'redmine_instance_id' => $data['redmine_instance_id'],
            'redmine_user_id' => $redmineResponse['user']['id'],
        ], [
            'api_key' => $redmineResponse['user']['api_key'],
            'user_id' => Auth::id(),
        ]);
    }

    public function checkAuth(RedmineInstance $instance, User $user): bool
    {
        $relation = RedmineUser::whereRedmineInstanceId($instance->id)
            ->where('user_id', '=', $user->id)
            ->first();
        if (!isset($relation)) {
            return false;
        }

        $client = app(Client::class, ['url' => $instance->url, 'apikeyOrUsername' => $relation->api_key]);
        $redmineResponse = $client->getApi('user')->getCurrentUser();
        if (isset($redmineResponse) && is_array($redmineResponse)) {
            return true;
        }

        $user->notify(new Unauthorized($instance));

        return false;
    }
}
