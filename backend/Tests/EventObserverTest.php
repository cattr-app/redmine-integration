<?php

namespace Modules\RedmineIntegration\Tests;

use App\Models\Task;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Modules\RedmineIntegration\Entities\RedmineInstance;
use Modules\RedmineIntegration\Entities\RedminePriority;
use Modules\RedmineIntegration\Entities\RedmineProject;
use Modules\RedmineIntegration\Entities\RedmineTask;
use Modules\RedmineIntegration\Entities\RedmineUser;
use Modules\RedmineIntegration\Services\RedmineTaskService;
use Tests\Facades\TaskFactory;
use Tests\Facades\UserFactory;
use Tests\TestCase;

class EventObserverTest extends TestCase
{
    use DatabaseTransactions;

    public function test_editing_webhook_linked_task_passes_task_model_to_redmine_service(): void
    {
        $admin = UserFactory::refresh()->asAdmin()->withTokens()->create();
        $task = TaskFactory::create();
        $assignedUser = UserFactory::create();
        $task->users()->sync([$assignedUser->id]);

        $instance = RedmineInstance::create([
            'url' => 'https://redmine.example.org',
            'api_key' => 'test-key',
            'sync_type' => 'webhook',
            'webhook_secret' => 'abcdefghijklmnop',
            'sync_new_projects' => false,
            'project_sync_interval' => 5,
            'task_sync_interval' => 5,
            'priority_sync_interval' => 5,
            'status_sync_interval' => 5,
        ]);

        $redmineTask = RedmineTask::create([
            'redmine_instance_id' => $instance->id,
            'redmine_issue_id' => 123,
            'task_id' => $task->id,
        ]);

        RedmineProject::create([
            'redmine_instance_id' => $instance->id,
            'redmine_project_id' => 456,
            'project_id' => $task->project_id,
            'name' => 'Test project',
        ]);

        RedminePriority::create([
            'redmine_instance_id' => $instance->id,
            'redmine_priority_id' => 7,
            'priority_id' => $task->priority_id,
            'name' => 'Normal',
        ]);

        RedmineUser::create([
            'redmine_instance_id' => $instance->id,
            'redmine_user_id' => 789,
            'user_id' => $assignedUser->id,
            'api_key' => 'test-key',
        ]);

        $this->mock(RedmineTaskService::class)
            ->shouldReceive('updateRedmineTask')
            ->once()
            ->with(
                Mockery::on(static fn ($mapping) => $mapping instanceof RedmineTask
                    && $mapping->is($redmineTask)),
                456,
                [789],
                7,
                Mockery::on(static fn ($editedTask) => $editedTask instanceof Task
                    && $editedTask->is($task)
                    && $editedTask->description === 'Updated from Cattr')
            );

        $this->actingAs($admin)->postJson('/api/tasks/edit', [
            'id' => $task->id,
            'project_id' => $task->project_id,
            'priority_id' => $task->priority_id,
            'description' => 'Updated from Cattr',
            'users' => [$assignedUser->id],
        ])->assertOk()->assertJsonPath('data.id', $task->id);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'description' => 'Updated from Cattr',
        ]);
    }
}
