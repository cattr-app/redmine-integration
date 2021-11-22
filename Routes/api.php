<?php

use Illuminate\Routing\Router;
use Modules\RedmineIntegration\Http\Controllers\RedmineInstanceController;
use Modules\RedmineIntegration\Http\Controllers\RedminePriorityController;
use Modules\RedmineIntegration\Http\Controllers\RedmineProjectController;
use Modules\RedmineIntegration\Http\Controllers\RedmineStatusController;
use Modules\RedmineIntegration\Http\Controllers\RedmineUserController;
use Modules\RedmineIntegration\Http\Controllers\RedmineWebhookController;

Route::group(['middleware' => 'auth:api'], static function (Router $router) {
    // Redmine instances
    $router->get('/instances', [RedmineInstanceController::class, 'list'])
           ->name('redmine-instances.list');
    $router->get('/instances/{id}', [RedmineInstanceController::class, 'show'])
           ->name('redmine-instances.show');
    $router->post('/instances', [RedmineInstanceController::class, 'create'])
           ->name('redmine-instances.create');
    $router->put('/instances/{id}', [RedmineInstanceController::class, 'update'])
           ->name('redmine-instances.update');
    $router->delete('/instances/{id}', [RedmineInstanceController::class, 'delete'])
           ->name('redmine-instances.delete');

    // Redmine priorities
    $router->get('/instances/{instanceId}/priorities', [RedminePriorityController::class, 'list'])
           ->name('redmine-priorities.list');
    $router->put('/instances/{instanceId}/priorities', [RedminePriorityController::class, 'bulkUpdate'])
           ->name('redmine-priorities.bulk-update');
    $router->get('/instances/{instanceId}/priorities/{id}', [RedminePriorityController::class, 'show'])
           ->name('redmine-priorities.show');
    $router->put('/instances/{instanceId}/priorities/{id}', [RedminePriorityController::class, 'update'])
           ->name('redmine-priorities.update');

    // Redmine statuses
    $router->get('/instances/{instanceId}/statuses', [RedmineStatusController::class, 'list'])
           ->name('redmine-statuses.list');
    $router->put('/instances/{instanceId}/statuses', [RedmineStatusController::class, 'bulkUpdate'])
           ->name('redmine-statuses.bulk-update');
    $router->get('/instances/{instanceId}/statuses/{id}', [RedmineStatusController::class, 'show'])
           ->name('redmine-statuses.show');
    $router->put('/instances/{instanceId}/statuses/{id}', [RedmineStatusController::class, 'update'])
           ->name('redmine-statuses.update');

    // Redmine projects
    $router->get('/instances/{instanceId}/projects', [RedmineProjectController::class, 'list'])
           ->name('redmine-projects.list');
    $router->get('/instances/{instanceId}/projects/{id}', [RedmineProjectController::class, 'show'])
           ->name('redmine-projects.show');
    $router->put('/instances/{instanceId}/projects/{id}', [RedmineProjectController::class, 'update'])
           ->name('redmine-projects.update');

    // Redmine users
    $router->post('/instances/{instanceId}/users', [RedmineUserController::class, 'create'])
           ->name('redmine-users.create');
    $router->post('/instances/{instanceId}/check-auth', [RedmineUserController::class, 'checkAuth'])
           ->name('redmine-users.check-auth');
});

Route::any('/{instance}/webhook', [RedmineWebhookController::class, 'webhook'])->name('redmine-webhook');
