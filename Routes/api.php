<?php

use Illuminate\Routing\Router;

Route::group(['middleware' => 'auth:api'], static function (Router $router) {
    // Redmine instances
    $router->get('/instances', 'RedmineInstanceController@list')->name('redmine-instances.list');
    $router->get('/instances/{id}', 'RedmineInstanceController@show')->name('redmine-instances.show');
    $router->post('/instances', 'RedmineInstanceController@create')->name('redmine-instances.create');
    $router->put('/instances/{id}', 'RedmineInstanceController@update')->name('redmine-instances.update');
    $router->delete('/instances/{id}', 'RedmineInstanceController@delete')->name('redmine-instances.delete');

    // Redmine priorities
    $router->get('/instances/{instanceId}/priorities', 'RedminePriorityController@list')->name('redmine-priorities.list');
    $router->put('/instances/{instanceId}/priorities', 'RedminePriorityController@bulkUpdate')->name('redmine-priorities.bulk-update');
    $router->get('/instances/{instanceId}/priorities/{id}', 'RedminePriorityController@show')->name('redmine-priorities.show');
    $router->put('/instances/{instanceId}/priorities/{id}', 'RedminePriorityController@update')->name('redmine-priorities.update');

    // Redmine statuses
    $router->get('/instances/{instanceId}/statuses', 'RedmineStatusController@list')->name('redmine-statuses.list');
    $router->get('/instances/{instanceId}/statuses/{id}', 'RedmineStatusController@show')->name('redmine-statuses.show');
    $router->put('/instances/{instanceId}/statuses/{id}', 'RedmineStatusController@update')->name('redmine-statuses.update');

    // Redmine projects
    $router->get('/instances/{instanceId}/projects', 'RedmineProjectController@list')->name('redmine-projects.list');
    $router->get('/instances/{instanceId}/projects/{id}', 'RedmineProjectController@show')->name('redmine-projects.show');
    $router->put('/instances/{instanceId}/projects/{id}', 'RedmineProjectController@update')->name('redmine-projects.update');

    // Redmine users
    $router->post('/instances/{instanceId}/users', 'RedmineUserController@create')->name('redmine-users.create');
});

Route::any('/webhook', 'RedmineWebhookController@webhook')->name('redmine-webhook');
