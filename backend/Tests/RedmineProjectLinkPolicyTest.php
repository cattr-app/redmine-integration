<?php

namespace Modules\RedmineIntegration\Tests;

use Modules\RedmineIntegration\Services\RedmineProjectLinkPolicy;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class RedmineProjectLinkPolicyTest extends TestCase
{
    #[DataProvider('linkCases')]
    public function testAutomaticLinkPolicy(
        ?int $projectId,
        ?bool $manualUnlinked,
        string $syncType,
        bool $enabled,
        bool $expected
    ): void {
        $this->assertSame(
            $expected,
            RedmineProjectLinkPolicy::shouldAutoLink($projectId, $manualUnlinked, $syncType, $enabled)
        );
    }

    public static function linkCases(): array
    {
        return [
            'legacy cron' => [null, null, 'cron', true, true],
            'legacy webhook stays protected' => [null, null, 'webhook', true, false],
            'new cron' => [null, false, 'cron', true, true],
            'new webhook' => [null, false, 'webhook', true, true],
            'explicitly unlinked cron' => [null, true, 'cron', true, false],
            'explicitly unlinked webhook' => [null, true, 'webhook', true, false],
            'linked project' => [42, false, 'cron', true, false],
            'legacy cron without creation' => [null, null, 'cron', false, false],
            'webhook without creation' => [null, false, 'webhook', false, false],
            'legacy switched to cron' => [null, null, 'cron', true, true],
        ];
    }
}
