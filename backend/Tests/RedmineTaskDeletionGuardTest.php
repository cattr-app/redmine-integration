<?php

namespace Modules\RedmineIntegration\Tests;

use App\Models\Task;
use Modules\RedmineIntegration\Entities\RedmineInstance;
use Modules\RedmineIntegration\Entities\RedmineTask;
use Tests\Facades\TaskFactory;
use Tests\Facades\UserFactory;
use Tests\TestCase;

class RedmineTaskDeletionGuardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(UserFactory::refresh()->asAdmin()->withTokens()->create());
    }

    public function test_redmine_linked_task_cannot_be_deleted(): void
    {
        $task = TaskFactory::create();
        $instance = RedmineInstance::create([
            'url' => 'https://redmine.example.org',
            'api_key' => 'test-key',
            'sync_type' => 'cron',
            'sync_new_projects' => false,
            'project_sync_interval' => 0,
            'task_sync_interval' => 0,
            'priority_sync_interval' => 0,
            'status_sync_interval' => 0,
        ]);
        RedmineTask::create([
            'redmine_instance_id' => $instance->id,
            'redmine_issue_id' => 123,
            'task_id' => $task->id,
        ]);

        $this->postJson('tasks/remove', ['id' => $task->id])->assertForbidden();

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'deleted_at' => null]);
        $this->assertDatabaseHas('redmine_tasks', [
            'redmine_instance_id' => $instance->id,
            'task_id' => $task->id,
        ]);
    }

    public function test_task_without_redmine_link_can_still_be_deleted(): void
    {
        $task = TaskFactory::create();

        $this->postJson('tasks/remove', ['id' => $task->id])->assertNoContent();

        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }
}
