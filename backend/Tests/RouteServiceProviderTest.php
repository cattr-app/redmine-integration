<?php

namespace Modules\RedmineIntegration\Tests;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as FoundationRouteServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\RedmineIntegration\Providers\RouteServiceProvider;
use Tests\TestCase;

class RouteServiceProviderTest extends TestCase
{
    public function test_module_provider_does_not_reload_core_routes(): void
    {
        $this->assertSame(ServiceProvider::class, get_parent_class(RouteServiceProvider::class));
        $this->assertFalse(is_subclass_of(RouteServiceProvider::class, FoundationRouteServiceProvider::class));
    }

    public function test_redmine_routes_keep_their_paths_names_and_middleware(): void
    {
        $instances = Route::getRoutes()->getByName('integration.redmine.redmine-instances.list');
        $this->assertNotNull($instances);
        $this->assertSame('api/integration/redmine/instances', $instances->uri());
        $this->assertContains('GET', $instances->methods());
        $this->assertContains('api', $instances->gatherMiddleware());
        $this->assertContains('auth:sanctum', $instances->gatherMiddleware());

        $webhook = Route::getRoutes()->getByName('integration.redmine.redmine-webhook');
        $this->assertNotNull($webhook);
        $this->assertSame('api/integration/redmine/{instance}/webhook', $webhook->uri());
        $this->assertContains('POST', $webhook->methods());
        $this->assertContains('api', $webhook->gatherMiddleware());
        $this->assertContains('redmineintegration.signature', $webhook->gatherMiddleware());
    }
}
