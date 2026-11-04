<?php

namespace Modules\RedmineIntegration\Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\RedmineIntegration\Entities\RedmineInstance;
use Tests\Facades\UserFactory;
use Tests\TestCase;

class RedmineInstanceValidationTest extends TestCase
{
    use DatabaseTransactions;

    private const URI = '/api/integration/redmine/instances';

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(UserFactory::refresh()->asAdmin()->withTokens()->create());
    }

    public function test_duplicate_instance_url_returns_cattr_validation_error(): void
    {
        $url = 'https://redmine.example.org/' . uniqid('', true);

        RedmineInstance::create([
            'url' => $url,
            'api_key' => 'test-key',
            'sync_type' => 'cron',
            'sync_new_projects' => false,
            'project_sync_interval' => 0,
            'task_sync_interval' => 0,
            'priority_sync_interval' => 0,
            'status_sync_interval' => 0,
        ]);

        $response = $this->postJson(self::URI, [
            'url' => $url,
            'sync_type' => 'cron',
            'sync_new_projects' => false,
            'project_sync_interval' => 0,
            'task_sync_interval' => 0,
            'priority_sync_interval' => 0,
            'status_sync_interval' => 0,
            'auth_method' => 'api_key',
            'auth_api_key' => 'test-key',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed')
            ->assertJsonStructure(['error' => ['message', 'fields' => ['url']]])
            ->assertJsonMissingPath('errors');

        $this->assertSame(['url'], array_keys($response->json('error.fields')));
        $this->assertNotEmpty($response->json('error.fields.url'));
        $this->assertSame(1, RedmineInstance::where('url', $url)->count());
    }
}
