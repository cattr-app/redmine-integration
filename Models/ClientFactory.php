<?php

namespace Modules\RedmineIntegration\Models;

use Modules\RedmineIntegration\Exceptions\ClientFactoryException;
use Modules\RedmineIntegration\Entities\Repositories\UserRepository;
use Modules\RedmineIntegration\Services\SettingsService;
use Redmine\Client;

class ClientFactory
{
    protected SettingsService $settings;
    protected UserRepository $userRepository;

    public function __construct(SettingsService $settings, UserRepository $userRepository)
    {
        $this->settings = $settings;
        $this->userRepository = $userRepository;
    }

    /**
     * @return Client
     * @throws ClientFactoryException
     */
    public function createCompanyClient(): Client
    {
        $url = $this->settings->getUrl();
        if (empty($url)) {
            throw new ClientFactoryException(
                'Redmine URL is not set in the company settings. Please contact your administrator',
                404
            );
        }

        $key = $this->settings->getApiKey();
        if (empty($key)) {
            throw new ClientFactoryException('Empty API key', 404);
        }

        return new Client($url, $key);
    }

    /**
     * @param int $userID
     * @param string|null $key
     * @return Client
     * @throws ClientFactoryException
     */
    public function createUserClient(int $userID, string $key = null): Client
    {
        $url = $this->settings->getUrl();
        if (empty($url)) {
            throw new ClientFactoryException('Empty URL', 404);
        }

        $key = $key ?? $this->userRepository->getUserRedmineApiKey($userID);
        if (empty($key)) {
            throw new ClientFactoryException('Empty API key', 404);
        }

        return new Client($url, $key);
    }
}
