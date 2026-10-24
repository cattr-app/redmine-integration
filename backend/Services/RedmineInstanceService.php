<?php

namespace Modules\RedmineIntegration\Services;

use Exception;
use Modules\RedmineIntegration\Entities\RedmineInstance;
use Redmine\Client\Client;
use RuntimeException;
use Illuminate\Validation\ValidationException;

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
            $client = app(Client::class, ['url' => $data['url'], 'apikeyOrUsername' => $data['auth_login'], 'password' => $data['auth_password']]);
        } elseif ($data['auth_method'] === 'api_key') {
            $client = app(Client::class, ['url' => $data['url'], 'apikeyOrUsername' => $data['auth_api_key']]);
        } else {
            throw new RuntimeException('Unknown auth_method');
        }

        $redmineResponse = $client->getApi('user')->getCurrentUser();
        if (!is_array($redmineResponse)) {
            throw new RuntimeException('Invalid credentials or Redmine not available. Please contact your administrator');
        }

        unset($data['auth_method'], $data['auth_login'], $data['auth_password'], $data['auth_api_key']);

        $data['api_key'] = $redmineResponse['user']['api_key'];

        $this->validateWebhookSecret($data['sync_type'], $data['webhook_secret'] ?? null);

        $instance = RedmineInstance::create($data);
        $this->redminePriorityService->synchronize($instance);
        $this->redmineStatusService->synchronize($instance);
        $this->redmineProjectService->synchronize($instance, false);

        return $instance;
    }

    public function update(int $id, array $data): RedmineInstance
    {
        if (isset($data['auth_method'])) {
            if ($data['auth_method'] === 'basic') {
                $client = app(Client::class, ['url' => $data['url'], 'apikeyOrUsername' => $data['auth_login'], 'password' => $data['auth_password']]);
            } elseif ($data['auth_method'] === 'api_key') {
                $client = app(Client::class, ['url' => $data['url'], 'apikeyOrUsername' => $data['auth_api_key']]);
            } else {
                throw new RuntimeException('Unknown auth_method');
            }

            $redmineResponse = $client->getApi('user')->getCurrentUser();
            if (!is_array($redmineResponse)) {
                throw new RuntimeException('Invalid credentials or Redmine not available. Please contact your administrator');
            }

            $data['api_key'] = $redmineResponse['user']['api_key'];
        }

        unset($data['auth_method'], $data['auth_login'], $data['auth_password'], $data['auth_api_key']);

        /** @var RedmineInstance $instance */
        $instance = RedmineInstance::find($id);
        $clearSecret = (bool) ($data['clear_webhook_secret'] ?? false);
        unset($data['clear_webhook_secret']);
        if ($clearSecret && !empty($data['webhook_secret'])) {
            throw ValidationException::withMessages(['webhook_secret' => 'Cannot replace and clear the secret together.']);
        }
        $secret = $clearSecret ? null : ($data['webhook_secret'] ?? $instance->webhook_secret);
        $syncType = $data['sync_type'] ?? $instance->sync_type;

        // Instances created for the legacy webhook plugin already run in webhook mode without a
        // per-instance secret and authenticate with the shared REQUEST_SIGNATURE token. They must
        // stay editable, so the native secret is only validated once one is introduced (or already
        // existed). Switching any other instance to webhook mode still requires a native secret.
        $hadNativeSecret = $instance->webhook_secret !== null && $instance->webhook_secret !== '';
        $keepsLegacyMode = $instance->sync_type === 'webhook'
            && !$hadNativeSecret
            && ($secret === null || $secret === '');
        if (!$keepsLegacyMode) {
            $this->validateWebhookSecret($syncType, $secret);
        }
        if ($clearSecret) {
            $data['webhook_secret'] = null;
        } elseif (empty($data['webhook_secret'])) {
            unset($data['webhook_secret']);
        }
        $instance->fill($data);
        $instance->save();

        return $instance;
    }
    private function validateWebhookSecret(string $syncType, ?string $secret): void
    {
        if ($syncType === 'webhook' && ($secret === null || strlen($secret) < 16)) {
            throw ValidationException::withMessages([
                'webhook_secret' => 'A webhook secret of at least 16 characters is required for webhook synchronization.',
            ]);
        }
    }
}
