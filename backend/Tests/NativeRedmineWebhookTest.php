<?php

namespace Modules\RedmineIntegration\Tests;

use App\Models\Task;
use App\Models\TimeInterval;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\RedmineIntegration\Entities\RedmineInstance;
use Modules\RedmineIntegration\Entities\RedmineTask;
use Modules\RedmineIntegration\Entities\RedmineTimeInterval;
use Modules\RedmineIntegration\Services\RedmineTaskService;
use Modules\RedmineIntegration\Services\RedmineTimeService;
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
        $body = json_encode(['type' => 'issue.deleted', 'data' => ['issue' => ['id' => 123]]], JSON_THROW_ON_ERROR);

        $this->call(
            'POST',
            "/api/integration/redmine/{$instance->id}/webhook",
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $body
        )->assertStatus(403);
        $this->signedRequest($instance, $body, 'sha256=' . str_repeat('0', 64))->assertStatus(403);
        $this->signedRequest($instance, $body)->assertStatus(204);
    }

    public function test_empty_secret_is_rejected_even_with_a_matching_signature(): void
    {
        $instance = $this->instance('');
        $body = json_encode(['type' => 'issue.deleted', 'data' => ['issue' => ['id' => 1]]], JSON_THROW_ON_ERROR);

        $this->signedRequest($instance, $body, 'sha256=' . hash_hmac('sha256', $body, ''))
            ->assertStatus(403);
    }

    public function test_configured_flag_does_not_decrypt_broken_ciphertext(): void
    {
        $instance = $this->instance();
        $instance->setRawAttributes(array_merge($instance->getAttributes(), [
            'webhook_secret' => 'invalid-encrypted-value',
        ]));

        $this->assertTrue($instance->webhook_secret_configured);
        $this->assertTrue($instance->toArray()['webhook_secret_configured']);
        $this->assertArrayNotHasKey('webhook_secret', $instance->toArray());
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

        $body = json_encode(['type' => 'issue.deleted', 'data' => ['issue' => ['id' => 123]]], JSON_THROW_ON_ERROR);
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

    public function test_issue_deletion_cleans_links_and_upload_queue_but_preserves_intervals(): void
    {
        $instance = $this->instance();
        $task = Task::factory()->create();
        $otherTask = Task::factory()->create();
        RedmineTask::create([
            'redmine_instance_id' => $instance->id,
            'redmine_issue_id' => 123,
            'task_id' => $task->id,
        ]);

        $interval = TimeInterval::factory()->create([
            'task_id' => $task->id,
            'user_id' => $task->assigned_by,
        ]);
        RedmineTimeInterval::create([
            'redmine_instance_id' => $instance->id,
            'time_interval_id' => $interval->id,
        ]);

        DB::table('tasks_relations')->insert([
            ['parent_id' => $otherTask->id, 'child_id' => $task->id, 'label' => 'blocks', 'weight' => 1],
            ['parent_id' => $task->id, 'child_id' => $otherTask->id, 'label' => 'blocks', 'weight' => 1],
        ]);
        DB::table('cron_task_workers')->insert([
            'user_id' => $task->assigned_by,
            'task_id' => $task->id,
            'duration' => 300,
        ]);

        $body = json_encode(['type' => 'issue.deleted', 'data' => ['issue' => ['id' => 123]]], JSON_THROW_ON_ERROR);
        $this->signedRequest($instance, $body)->assertStatus(204);
        $this->signedRequest($instance, $body)->assertStatus(204);

        $this->assertNotNull(Task::withTrashed()->findOrFail($task->id)->deleted_at);
        $this->assertDatabaseHas('time_intervals', ['id' => $interval->id, 'deleted_at' => null]);
        $this->assertDatabaseHas('redmine_tasks', ['redmine_instance_id' => $instance->id, 'task_id' => $task->id]);
        $this->assertDatabaseMissing('redmine_time_intervals', ['time_interval_id' => $interval->id]);
        $this->assertDatabaseMissing('cron_task_workers', ['task_id' => $task->id]);
        $this->assertSame(0, DB::table('tasks_relations')
            ->where('parent_id', $task->id)->orWhere('child_id', $task->id)->count());
    }

    public function test_time_synchronization_retires_uploads_queued_after_task_deletion(): void
    {
        $instance = $this->instance();
        $task = Task::factory()->create();
        RedmineTask::create([
            'redmine_instance_id' => $instance->id,
            'redmine_issue_id' => 123,
            'task_id' => $task->id,
        ]);
        app(RedmineTaskService::class)->deleteTaskFromWebhook($instance, 123);

        $interval = TimeInterval::factory()->create([
            'task_id' => $task->id,
            'user_id' => $task->assigned_by,
        ]);
        RedmineTimeInterval::create([
            'redmine_instance_id' => $instance->id,
            'time_interval_id' => $interval->id,
        ]);

        app(RedmineTimeService::class)->synchronize($instance);

        $this->assertDatabaseMissing('redmine_time_intervals', ['time_interval_id' => $interval->id]);
        $this->assertDatabaseHas('time_intervals', ['id' => $interval->id, 'deleted_at' => null]);
    }
}
