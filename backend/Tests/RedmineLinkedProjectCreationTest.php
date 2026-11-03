<?php

namespace Modules\RedmineIntegration\Tests;

use App\Models\Project;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\RedmineIntegration\Entities\RedmineInstance;
use Modules\RedmineIntegration\Entities\RedmineProject;
use Tests\Facades\UserFactory;
use Tests\TestCase;

class RedmineLinkedProjectCreationTest extends TestCase
{
    use DatabaseTransactions;

    private function makeInstance(): RedmineInstance
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
        ]);
    }

    private function makeMapping(RedmineInstance $instance, array $attributes = []): RedmineProject
    {
        return RedmineProject::create(array_merge([
            'redmine_instance_id' => $instance->id,
            'redmine_project_id' => random_int(1000, 999999),
            'name' => 'Redmine project',
            'project_id' => null,
            'manual_unlinked' => false,
        ], $attributes));
    }

    private function uri(int $instanceId, int $id): string
    {
        return "/api/integration/redmine/instances/{$instanceId}/projects/{$id}/create-project";
    }

    private function actingAsAdmin(): void
    {
        $this->actingAs(UserFactory::refresh()->asAdmin()->withTokens()->create());
    }

    public function test_creates_redmine_sourced_project_and_links_mapping(): void
    {
        $this->actingAsAdmin();
        $instance = $this->makeInstance();
        $mapping = $this->makeMapping($instance);

        $this->postJson($this->uri($instance->id, $mapping->id), [
            'name' => 'redmine.example.org/Redmine project',
            'description' => 'Redmine project',
            'source' => 'internal',
        ])->assertOk();

        $mapping->refresh();
        $this->assertNotNull($mapping->project_id);
        $this->assertFalse($mapping->manual_unlinked);

        $project = $mapping->project;
        $this->assertNotNull($project);
        $this->assertSame('redmine', $project->getRawOriginal('source'));
        $this->assertSame('redmine.example.org/Redmine project', $project->getRawOriginal('name'));
        $this->assertSame('Redmine project', $project->description);
    }

    public function test_defaults_name_and_description_to_redmine_project_name(): void
    {
        $this->actingAsAdmin();
        $instance = $this->makeInstance();
        $mapping = $this->makeMapping($instance, ['name' => 'Default name']);

        $this->postJson($this->uri($instance->id, $mapping->id))->assertOk();

        $project = $mapping->refresh()->project;
        $this->assertSame('redmine', $project->getRawOriginal('source'));
        $this->assertSame('Default name', $project->getRawOriginal('name'));
        $this->assertSame('Default name', $project->description);
    }

    public function test_manually_unlinked_mapping_can_be_linked_to_a_new_project(): void
    {
        $this->actingAsAdmin();
        $instance = $this->makeInstance();
        $mapping = $this->makeMapping($instance, ['manual_unlinked' => true]);

        $this->postJson($this->uri($instance->id, $mapping->id))->assertOk();

        $mapping->refresh();
        $this->assertNotNull($mapping->project_id);
        $this->assertFalse($mapping->manual_unlinked);
    }

    public function test_already_linked_mapping_is_rejected_without_creating_a_project(): void
    {
        $this->actingAsAdmin();
        $instance = $this->makeInstance();
        $existing = Project::withoutGlobalScopes()->create([
            'name' => 'Existing',
            'description' => 'Existing',
        ]);
        $mapping = $this->makeMapping($instance, ['project_id' => $existing->id]);
        $projectCount = Project::withoutGlobalScopes()->count();

        $this->postJson($this->uri($instance->id, $mapping->id), ['name' => 'Duplicate'])
            ->assertUnprocessable();

        $this->assertSame($projectCount, Project::withoutGlobalScopes()->count());
        $this->assertSame($existing->id, $mapping->refresh()->project_id);
    }

    public function test_mapping_from_another_instance_is_not_found(): void
    {
        $this->actingAsAdmin();
        $instance = $this->makeInstance();
        $otherInstance = $this->makeInstance();
        $mapping = $this->makeMapping($instance);

        $this->postJson($this->uri($otherInstance->id, $mapping->id))->assertStatus(404);

        $this->assertNull($mapping->refresh()->project_id);
    }

    public function test_user_without_project_creation_permission_is_forbidden(): void
    {
        $this->actingAs(UserFactory::refresh()->asUser()->withTokens()->create());
        $instance = $this->makeInstance();
        $mapping = $this->makeMapping($instance);

        $this->postJson($this->uri($instance->id, $mapping->id))->assertForbidden();

        $this->assertNull($mapping->refresh()->project_id);
    }
}
