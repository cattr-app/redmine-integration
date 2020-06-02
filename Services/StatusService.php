<?php

namespace Modules\RedmineIntegration\Services;

use Illuminate\Support\Arr;
use Modules\RedmineIntegration\Exceptions\ClientFactoryException;
use Modules\RedmineIntegration\Models\ClientFactory;

class StatusService
{
    protected const REDMINE_STATUSES = 'statuses';
    protected const REDMINE_ACTIVE_STATUS = 'active_status';
    protected const REDMINE_INACTIVE_STATUS = 'inactive_status';
    protected const REDMINE_ACTIVATE_ON_STATUSES = 'activate_on_statuses';
    protected const REDMINE_DEACTIVATE_ON_STATUSES = 'deactivate_on_statuses';

    protected ClientFactory $clientFactory;
    protected SettingsService $settings;

    public function __construct(ClientFactory $clientFactory, SettingsService $settings)
    {
        $this->clientFactory = $clientFactory;
        $this->settings = $settings;
    }

    public function existsByID(int $id): bool
    {
        $statuses = $this->getAll();
        foreach ($statuses as $status) {
            if ($status['id'] === $id) {
                return true;
            }
        }

        return false;
    }

    public function getAll(): array
    {
        return $this->settings->get(static::REDMINE_STATUSES, []);
    }

    public function add(int $id, string $name, bool $isActive, bool $isClosed): void
    {
        $statuses = $this->getAll();
        $statuses[] = [
            'id' => $id,
            'name' => $name,
            'is_active' => $isActive,
            'is_closed' => $isClosed,
        ];

        $this->setAll($statuses);
    }

    public function setAll(array $value): void
    {
        $this->settings->set(static::REDMINE_STATUSES, $value);
    }

    public function getActiveStatusID(): int
    {
        return $this->settings->get(static::REDMINE_ACTIVE_STATUS, 0);
    }

    public function setActiveStatusID(int $value): int
    {
        return $this->settings->set(static::REDMINE_ACTIVE_STATUS, $value);
    }

    public function getInactiveStatusID(): int
    {
        return $this->settings->get(static::REDMINE_INACTIVE_STATUS, 0);
    }

    public function setInactiveStatusID(int $value): int
    {
        return $this->settings->set(static::REDMINE_INACTIVE_STATUS, $value);
    }

    public function getActivateOnStatuses(): array
    {
        return $this->settings->get(static::REDMINE_ACTIVATE_ON_STATUSES, []);
    }

    public function setActivateOnStatuses(array $value): array
    {
        return $this->settings->set(static::REDMINE_ACTIVATE_ON_STATUSES, $value);
    }

    public function getDeactivateOnStatuses(): array
    {
        return $this->settings->get(static::REDMINE_DEACTIVATE_ON_STATUSES, []);
    }

    public function setDeactivateOnStatuses(array $value): array
    {
        return $this->settings->set(static::REDMINE_DEACTIVATE_ON_STATUSES, $value);
    }

    /**
     * @throws ClientFactoryException
     */
    public function synchronize(): void
    {
        $client = $this->clientFactory->createCompanyClient();
        $redmineStatuses = $client->issue_status->all()['issue_statuses'];
        $savedStatuses = $this->getAll();

        // Merge statuses info from the redmine with the active state of stored statuses
        $statuses = array_map(static function (array $redmineStatus) use ($savedStatuses) {
            // Try find saved status with the same ID
            $savedStatus = Arr::first($savedStatuses, static function ($savedStatus) use ($redmineStatus) {
                return $savedStatus['id'] === $redmineStatus['id'];
            });

            // Set status is active, if saved status is exist and active,
            // or if status from the Redmine is not closed
            $redmineStatus['is_active'] = isset($savedStatus)
                ? $savedStatus['is_active']
                : !isset($redmineStatus['is_closed']);

            return $redmineStatus;
        }, $redmineStatuses);

        $this->setAll($statuses);
    }
}
