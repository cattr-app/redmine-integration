<?php

namespace Modules\RedmineIntegration\Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Validation\ValidationException;
use Modules\RedmineIntegration\Entities\RedmineInstance;
use Modules\RedmineIntegration\Services\RedmineInstanceService;
use Tests\TestCase;

class RedmineInstanceServiceTest extends TestCase
{
    use DatabaseTransactions;

    private function makeInstance(?string $secret): RedmineInstance
    {
        return RedmineInstance::create([
            'url' => 'https://redmine.example.org/' . uniqid('', true),
            'api_key' => 'test-key',
            'sync_type' => 'webhook',
            'sync_new_projects' => false,
            'project_sync_interval' => 0,
            'task_sync_interval' => 0,
            'priority_sync_interval' => 0,
            'status_sync_interval' => 0,
            'webhook_secret' => $secret,
        ]);
    }

    public function test_legacy_webhook_instance_can_be_updated_without_a_native_secret(): void
    {
        $instance = $this->makeInstance(null);

        $updated = app(RedmineInstanceService::class)->update($instance->id, [
            'sync_type' => 'webhook',
            'task_sync_interval' => 15,
        ]);

        $this->assertSame(15, (int) $updated->task_sync_interval);
        $this->assertFalse($updated->webhook_secret_configured);
    }

    public function test_legacy_webhook_instance_rejects_a_short_native_secret(): void
    {
        $instance = $this->makeInstance(null);

        $this->expectException(ValidationException::class);

        app(RedmineInstanceService::class)->update($instance->id, [
            'sync_type' => 'webhook',
            'webhook_secret' => 'short',
        ]);
    }

    public function test_legacy_webhook_instance_can_migrate_to_a_native_secret(): void
    {
        $instance = $this->makeInstance(null);

        $updated = app(RedmineInstanceService::class)->update($instance->id, [
            'sync_type' => 'webhook',
            'webhook_secret' => 'abcdefghijklmnop',
        ]);

        $this->assertTrue($updated->webhook_secret_configured);
        $this->assertSame('abcdefghijklmnop', $updated->webhook_secret);
    }

    public function test_native_webhook_instance_cannot_clear_its_secret_while_in_webhook_mode(): void
    {
        $instance = $this->makeInstance('abcdefghijklmnop');

        $this->expectException(ValidationException::class);

        app(RedmineInstanceService::class)->update($instance->id, [
            'sync_type' => 'webhook',
            'clear_webhook_secret' => true,
        ]);
    }
}
