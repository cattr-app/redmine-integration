<?php

namespace Modules\RedmineIntegration\Services;

use App\Contracts\Settings;

/**
 * Class SettingsService
 * @package Modules\RedmineIntegration\Services
 */
class SettingsService
{
    /**
     *
     */
    protected const MODULE_NAME = 'redmine';

    /**
     * @var Settings
     */
    protected Settings $settings;

    /**
     * SettingsService constructor.
     * @param Settings $settings
     */
    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return $this->settings->all(self::MODULE_NAME);
    }

    /**
     * @param string $key
     * @param null $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return $this->settings->get(self::MODULE_NAME, $key, $default);
    }

    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    public function set($key, $value = null)
    {
        return $this->settings->get(self::MODULE_NAME, $key, $value);
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->get('enabled', false);
    }

    /**
     * @return array
     */
    public function getPriorities(): array
    {
        return $this->get('priorities', []);
    }

    /**
     * @param $value
     * @return array
     */
    public function setPriorities($value): array
    {
        return $this->set('priorities', $value);
    }

    /**
     * @return mixed
     */
    public function getOnlineTimeout(): int
    {
        return $this->get('online_timeout', 0);
    }

    /**
     * @param int $value
     * @return int
     */
    public function setOnlineTimeout(int $value): int
    {
        return $this->set('online_timeout', $value);
    }

    /**
     * @return string
     */
    public function getApiKey(): string
    {
        return $this->get('url');
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->get('url', null);
    }

    /**
     * @return bool
     */
    public function isSyncEnabled(): bool
    {
        return $this->get('sync', false);
    }
}
