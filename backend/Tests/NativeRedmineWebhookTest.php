<?php

namespace Modules\RedmineIntegration\Tests;

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\RedmineIntegration\Entities\RedmineInstance;
use Modules\RedmineIntegration\Entities\RedmineTask;
use Modules\RedmineIntegration\Services\RedmineTaskService;
use Tests\TestCase;

class NativeRedmineWebhookTest extends TestCase
{
    use RefreshDatabase;

    private function instance(string $secret = 'abcdefghijklmnop'): RedmineInstance
    {
        return RedmineInstance::create([
            'url' => 'https://redmine.example.org',
            'api_key' => 'test-key',
            'sync_type' => 'webhook',
            'webhook_secret' => $secret,
            'sync_new_projects' => false,
            'project_sync_interval' => 5,
            'task_sync_interval' => 5,
            'priority_sync_interval' => 5,
            'status_sync_interval' => 5,
        ]);
    }

    private function signedRequest(RedmineInstance $instance, string $body, ?string $signature = null)
    {
        $signature ??= 'sha256=' . hash_hmac('sha256', $body, $instance->webhook_secret);

        return $this->call(
            'POST',
            "/api/integration/redmine/{$instance->id}/webhook",
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_X_REDMINE_SIGNATURE_256' => $signature],
            $body
        );
    }

    public function test_signature_required_and_validated(): void
    {
        $instance = $this->instance();
        $body = json_encode(['type' => 'issue.deleted', 'data' => ['issue' => ['id' => 123]]]);

        $this->signedRequest($instance, $body, 'sha256=' . str_repeat('0', 64))->assertStatus(403);
        $this->signedRequest($instance, $body)->assertStatus(204);
    }

    public function test_issue_deleted_soft_deletes_linked_task_and_is_idempotent(): void
    {
        $instance = $this->instance();
        $task = Task::factory()->create();
        RedmineTask::create([
            'redmine_instance_id' => $instance->id,
            'redmine_issue_id' => 123,
            'task_id' => $task->id,
        ]);

        $body = json_encode(['type' => 'issue.deleted', 'data' => ['issue' => ['id' => 123]]]);
        $this->signedRequest($instance, $body)->assertStatus(204);
        $this->signedRequest($instance, $body)->assertStatus(204);

        $this->assertNotNull(Task::withTrashed()->findOrFail($task->id)->deleted_at);
        $this->assertDatabaseHas('redmine_tasks', [
            'redmine_instance_id' => $instance->id,
            'redmine_issue_id' => 123,
            'task_id' => $task->id,
        ]);
    }

    public function test_deleted_task_is_not_recreated_by_a_late_update(): void
    {
        $instance = $this->instance();
        $task = Task::factory()->create();
        RedmineTask::create([
            'redmine_instance_id' => $instance->id,
            'redmine_issue_id' => 123,
            'task_id' => $task->id,
        ]);
        app(RedmineTaskService::class)->deleteTaskFromWebhook($instance, 123);

        app(RedmineTaskService::class)->createTaskFromWebhook($instance, 123, [
            'project' => ['id' => 1, 'name' => 'Example'],
            'status' => ['id' => 1],
            'priority' => ['id' => 1],
            'subject' => 'Late update',
        ]);

        $this->assertSame(1, RedmineTask::where('redmine_instance_id', $instance->id)->count());
        $this->assertNotNull(Task::withTrashed()->findOrFail($task->id)->deleted_at);
    }
}
