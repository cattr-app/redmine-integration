<?php

Route::middleware('auth:api')->group(static function () {
    // Redmine instances
    Route::get('/instances', 'RedmineInstanceController@list')->name('redmine-instances.list');
    Route::get('/instances/{id}', 'RedmineInstanceController@show')->name('redmine-instances.show');
    Route::post('/instances', 'RedmineInstanceController@create')->name('redmine-instances.create');
    Route::put('/instances/{id}', 'RedmineInstanceController@update')->name('redmine-instances.update');
    Route::delete('/instances/{id}', 'RedmineInstanceController@delete')->name('redmine-instances.delete');

    // Redmine priorities
    Route::get('/instances/{instanceId}/priorities', 'RedminePriorityController@list')->name('redmine-priorities.list');
    Route::put('/instances/{instanceId}/priorities', 'RedminePriorityController@bulkUpdate')->name('redmine-priorities.bulk-update');
    Route::get('/instances/{instanceId}/priorities/{id}', 'RedminePriorityController@show')->name('redmine-priorities.show');
    Route::put('/instances/{instanceId}/priorities/{id}', 'RedminePriorityController@update')->name('redmine-priorities.update');

    // Redmine statuses
    Route::get('/instances/{instanceId}/statuses', 'RedmineStatusController@list')->name('redmine-statuses.list');
    Route::get('/instances/{instanceId}/statuses/{id}', 'RedmineStatusController@show')->name('redmine-statuses.show');
    Route::put('/instances/{instanceId}/statuses/{id}', 'RedmineStatusController@update')->name('redmine-statuses.update');

    // Redmine projects
    Route::get('/instances/{instanceId}/projects', 'RedmineProjectController@list')->name('redmine-projects.list');
    Route::get('/instances/{instanceId}/projects/{id}', 'RedmineProjectController@show')->name('redmine-projects.show');
    Route::put('/instances/{instanceId}/projects/{id}', 'RedmineProjectController@update')->name('redmine-projects.update');

    // Task routes
    Route::post('/tasks/synchronize', 'TaskRedmineController@synchronize')->name('task.synchronize');

    // Project routes
    Route::post('/projects/synchronize', 'ProjectRedmineController@synchronize')
        ->name('projects.synchronize');

    // Time Entry routes
    Route::put('/time-entries', 'TimeEntryRedmineController@create')->name('time-entries.put');

    // Redmine Settings routes
    Route::get('/settings', 'SettingController@index')->name('settings.index');
    Route::patch('/settings', 'SettingController@update')->name('settings.update');

    // User settings routes
    Route::get('/user-settings', 'UserSettingController@index')->name('user-settings.index');
    Route::patch('/user-settings', 'UserSettingController@update')->name('user-settings.update');
});

Route::group([
    'middleware' => 'redmineintegration.signature',
    'as' => 'plugin.',
    'prefix' => 'plugin'
], static function () {
    Route::post('update', 'TaskUpdateController@handleUpdate')->name('update');
});
