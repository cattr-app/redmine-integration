<?php

namespace Modules\RedmineIntegration\Tests;

use App\Models\Task;
use App\Models\TimeInterval;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use App\Models\Priority;
use App\Models\Status;
use Modules\RedmineIntegration\Entities\RedmineInstance;
use Modules\RedmineIntegration\Entities\RedminePriority;
use Modules\RedmineIntegration\Entities\RedmineProject;
use Modules\RedmineIntegration\Entities\RedmineStatus;
use Modules\RedmineIntegration\Entities\RedmineTask;
use Modules\RedmineIntegration\Entities\RedmineUser;
use Modules\RedmineIntegration\Entities\RedmineTimeInterval;
use Modules\RedmineIntegration\Services\RedmineTaskService;
use Modules\RedmineIntegration\Services\RedmineTimeService;
use Tests\TestCase;

class NativeRedmineWebhookTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        \Tests\Facades\UserFactory::refresh()->asAdmin()->create();
    }

    private function makeInstance(string $secret = 'abcdefghijklmnop'): RedmineInstance
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


    private function legacyRequest(RedmineInstance $instance, string $body, ?string $signature = 'legacy-test-token')
    {
        $headers = ['CONTENT_TYPE' => 'application/json'];
        if ($signature !== null) {
            $headers['HTTP_X_REDMINE_SIGNATURE'] = $signature;
        }

        return $this->call(
            'POST',
            "/api/integration/redmine/{$instance->id}/webhook",
            [],
            [],
            [],
            $headers,
            $body
        );
    }

    public function test_legacy_plugin_payload_is_accepted_for_existing_instance(): void
    {
        config()->set('redmineintegration.request.signature', 'legacy-test-token');
        $instance = $this->makeInstance('');
        $body = json_encode(['payload' => ['issue' => [
            'id' => 123,
            'project' => ['id' => 4, 'name' => 'Legacy project'],
            'status' => ['id' => 1],
            'priority' => ['id' => 2],
            'subject' => 'Legacy issue',
            'assignee' => ['id' => 10],
        ]]], JSON_THROW_ON_ERROR);

        // No mapped project exists, so a valid legacy update is accepted without creating a task.
        $this->legacyRequest($instance, $body)->assertStatus(204);
    }

    public function test_legacy_plugin_payload_creates_task_in_mapped_project_and_maps_assignee(): void
    {
        config()->set('redmineintegration.request.signature', 'legacy-test-token');
        $instance = $this->makeInstance('');
        $project = \Tests\Facades\ProjectFactory::create();
        $assignee = \Tests\Facades\UserFactory::create();

        RedmineProject::create([
            'redmine_instance_id' => $instance->id,
            'redmine_project_id' => 4,
            'name' => 'Legacy project',
            'project_id' => $project->id,
            'manual_unlinked' => false,
        ]);
        $status = new RedmineStatus([
            'redmine_instance_id' => $instance->id,
            'redmine_status_id' => 1,
            'name' => 'New',
            'is_closed' => false,
        ]);
        $status->status_id = Status::query()->firstOrFail()->id;
        $status->save();
        $priority = new RedminePriority([
            'redmine_instance_id' => $instance->id,
            'redmine_priority_id' => 2,
            'name' => 'Normal',
        ]);
        $priority->priority_id = Priority::query()->firstOrFail()->id;
        $priority->save();
        RedmineUser::create([
            'redmine_instance_id' => $instance->id,
            'redmine_user_id' => 10,
            'user_id' => $assignee->id,
            'api_key' => 'assignee-key',
        ]);

        $body = json_encode(['payload' => ['issue' => [
            'id' => 123,
            'project' => ['id' => 4, 'name' => 'Legacy project'],
            'status' => ['id' => 1],
            'priority' => ['id' => 2],
            'subject' => 'Legacy issue',
            'description' => 'Created by the legacy plugin',
            // The historical plugin sends "assignee" instead of "assigned_to".
            'assignee' => ['id' => 10],
        ]]], JSON_THROW_ON_ERROR);

        $this->legacyRequest($instance, $body)->assertStatus(204);

        $redmineTask = RedmineTask::where(['redmine_instance_id' => $instance->id, 'redmine_issue_id' => 123])->firstOrFail();
        $task = Task::withoutGlobalScopes()->findOrFail($redmineTask->task_id);
        $this->assertSame('Legacy issue', $task->task_name);
        $this->assertSame($project->id, (int) $task->project_id);
        $this->assertSame($status->status_id, (int) $task->status_id);
        $this->assertSame($priority->priority_id, (int) $task->priority_id);
        $this->assertDatabaseHas('tasks_users', ['task_id' => $task->id, 'user_id' => $assignee->id]);
        $this->assertDatabaseHas('projects_users', ['project_id' => $project->id, 'user_id' => $assignee->id]);
    }

    public function test_legacy_signature_rejects_missing_invalid_and_default_keys(): void
    {
        $instance = $this->makeInstance('');
        $body = json_encode(['payload' => ['issue' => ['id' => 123]]], JSON_THROW_ON_ERROR);
        config()->set('redmineintegration.request.signature', 'legacy-test-token');

        $this->legacyRequest($instance, $body, null)->assertStatus(403);
        $this->legacyRequest($instance, $body, 'invalid')->assertStatus(403);
        config()->set('redmineintegration.request.signature', 'DEFAULT');
        $this->legacyRequest($instance, $body, 'DEFAULT')->assertStatus(403);
    }

    public function test_configured_native_secret_disables_legacy_authentication(): void
    {
        config()->set('redmineintegration.request.signature', 'legacy-test-token');
        $instance = $this->makeInstance();
        $body = json_encode(['payload' => ['issue' => ['id' => 123]]], JSON_THROW_ON_ERROR);

        $this->legacyRequest($instance, $body)->assertStatus(403);
        // A malformed native signature must not fall back to an otherwise valid legacy header.
        $this->call('POST', "/api/integration/redmine/{$instance->id}/webhook", [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_REDMINE_SIGNATURE_256' => 'sha256=invalid',
            'HTTP_X_REDMINE_SIGNATURE' => 'legacy-test-token',
        ], $body)->assertStatus(403);
    }

    public function test_legacy_payload_must_contain_issue_data(): void
    {
        config()->set('redmineintegration.request.signature', 'legacy-test-token');
        $instance = $this->makeInstance('');
        $this->legacyRequest($instance, '{"payload":{}}')->assertStatus(422);
    }

    public function test_signature_required_and_validated(): void
    {
        $instance = $this->makeInstance();
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
        $instance = $this->makeInstance('');
        $body = json_encode(['type' => 'issue.deleted', 'data' => ['issue' => ['id' => 1]]], JSON_THROW_ON_ERROR);

        $this->signedRequest($instance, $body, 'sha256=' . hash_hmac('sha256', $body, ''))
            ->assertStatus(403);
    }

    public function test_configured_flag_does_not_decrypt_broken_ciphertext(): void
    {
        $instance = $this->makeInstance();
        $instance->setRawAttributes(array_merge($instance->getAttributes(), [
            'webhook_secret' => 'invalid-encrypted-value',
        ]));

        $this->assertTrue($instance->webhook_secret_configured);
        $this->assertTrue($instance->toArray()['webhook_secret_configured']);
        $this->assertArrayNotHasKey('webhook_secret', $instance->toArray());
    }

    public function test_issue_deleted_soft_deletes_linked_task_and_is_idempotent(): void
    {
        $instance = $this->makeInstance();
        $task = \Tests\Facades\TaskFactory::create();
        RedmineTask::create([
            'redmine_instance_id' => $instance->id,
            'redmine_issue_id' => 123,
            'task_id' => $task->id,
        ]);

        $body = json_encode(['type' => 'issue.deleted', 'data' => ['issue' => ['id' => 123]]], JSON_THROW_ON_ERROR);
        $this->signedRequest($instance, $body)->assertStatus(204);
        $this->signedRequest($instance, $body)->assertStatus(204);

        $this->assertNotNull(Task::withoutGlobalScopes()->withTrashed()->findOrFail($task->id)->deleted_at);
        $this->assertDatabaseHas('redmine_tasks', [
            'redmine_instance_id' => $instance->id,
            'redmine_issue_id' => 123,
            'task_id' => $task->id,
        ]);
    }

    public function test_deleted_task_is_not_recreated_by_a_late_update(): void
    {
        $instance = $this->makeInstance();
        $task = \Tests\Facades\TaskFactory::create();
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
        $this->assertNotNull(Task::withoutGlobalScopes()->withTrashed()->findOrFail($task->id)->deleted_at);
    }

    public function test_issue_deletion_cleans_links_and_upload_queue_but_preserves_intervals(): void
    {
        $instance = $this->makeInstance();
        $task = \Tests\Facades\TaskFactory::create();
        $otherTask = \Tests\Facades\TaskFactory::create();
        RedmineTask::create([
            'redmine_instance_id' => $instance->id,
            'redmine_issue_id' => 123,
            'task_id' => $task->id,
        ]);

        $user = \Tests\Facades\UserFactory::create();
        $interval = TimeInterval::factory()->create([
            'task_id' => $task->id,
            'user_id' => $user->id,
        ]);
        RedmineTimeInterval::create([
            'redmine_instance_id' => $instance->id,
            'time_interval_id' => $interval->id,
        ]);

        DB::table('tasks_relations')->insert([
            ['parent_id' => $otherTask->id, 'child_id' => $task->id, 'label' => 'blocks', 'weight' => 1],
            ['parent_id' => $task->id, 'child_id' => $otherTask->id, 'label' => 'blocks', 'weight' => 1],
        ]);
        DB::table('cron_task_workers')->updateOrInsert(
            ['user_id' => $user->id, 'task_id' => $task->id],
            ['duration' => 300],
        );

        $body = json_encode(['type' => 'issue.deleted', 'data' => ['issue' => ['id' => 123]]], JSON_THROW_ON_ERROR);
        $this->signedRequest($instance, $body)->assertStatus(204);
        $this->signedRequest($instance, $body)->assertStatus(204);

        $this->assertNotNull(Task::withoutGlobalScopes()->withTrashed()->findOrFail($task->id)->deleted_at);
        $this->assertDatabaseHas('time_intervals', ['id' => $interval->id, 'deleted_at' => null]);
        $this->assertDatabaseHas('redmine_tasks', ['redmine_instance_id' => $instance->id, 'task_id' => $task->id]);
        $this->assertDatabaseMissing('redmine_time_intervals', ['time_interval_id' => $interval->id]);
        $this->assertDatabaseMissing('cron_task_workers', ['task_id' => $task->id]);
        $this->assertSame(0, DB::table('tasks_relations')
            ->where('parent_id', $task->id)->orWhere('child_id', $task->id)->count());
    }

    public function test_time_synchronization_retires_uploads_queued_after_task_deletion(): void
    {
        $instance = $this->makeInstance();
        $task = \Tests\Facades\TaskFactory::create();
        RedmineTask::create([
            'redmine_instance_id' => $instance->id,
            'redmine_issue_id' => 123,
            'task_id' => $task->id,
        ]);
        app(RedmineTaskService::class)->deleteTaskFromWebhook($instance, 123);

        $user = \Tests\Facades\UserFactory::create();
        $interval = TimeInterval::factory()->create([
            'task_id' => $task->id,
            'user_id' => $user->id,
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
