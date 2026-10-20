<?php

namespace Modules\RedmineIntegration\Services;

final class RedmineProjectLinkPolicy
{
    /**
     * Allow legacy NULL mappings to be linked in cron mode, which always
     * imports projects. Legacy webhook mappings remain unlinked because
     * their administrative intent was not recorded before the migration.
     * Explicit manual unlinks (true) are never automatically overridden.
     */
    public static function shouldAutoLink(
        ?int $projectId,
        ?bool $manualUnlinked,
        string $syncType,
        bool $createInternalProjects
    ): bool {
        return $createInternalProjects
            && $projectId === null
            && ($manualUnlinked === false || ($manualUnlinked === null && $syncType === 'cron'));
    }
}
